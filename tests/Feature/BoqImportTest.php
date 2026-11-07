<?php

namespace Tests\Feature;

use App\Models\BoqItem;
use App\Models\Company;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use ZipArchive;

class BoqImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    /** A contractor bill laid out the way they usually are. */
    private const CONTRACTOR_BILL = [
        ['Skyline Builders — Tower A'],
        ['BILL OF QUANTITIES'],
        ['Sl. No.', 'Description of Item', 'Unit', 'Qty', 'Rate (Rs.)', 'Amount (Rs.)'],
        ['A', 'EARTHWORK'],
        ['1', 'Excavation in foundation trenches including disposal', 'Cum', '120', '350', '42000'],
        ['2', 'Earth work in excavation by mechanical means in all kinds of soil, including dressing of sides and ramming of bottoms, lift up to 1.5 m, disposal of excavated earth within 50 m as directed'],
        ['', '(a) Ordinary soil', 'Cum', '50', '200', '10000'],
        ['', '(b) Hard rock', 'Cum', '10', '900', '9000'],
        ['B', 'CONCRETE'],
        ['3', 'PCC 1:4:8 in foundation bed', 'Cum', '18.5', '₹ 5,200.00/-', '96,200.00'],
        ['4', 'Site clearance', '', 'LS', '25000', '25000'],
        ['', 'Total', '', '', '', '182200'],
    ];

    private function financeUser(?Company $company = null): User
    {
        return User::factory()
            ->forCompany($company ?? Company::factory()->create())
            ->create(['role_id' => Role::idFor(User::ROLE_ACCOUNTANT)]);
    }

    /**
     * @param  list<list<string>>  $rows
     */
    private function xlsx(array $rows, string $name = 'bill.xlsx'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'boq').'.xlsx';
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="BOQ" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');

        $strings = [];
        $sheetRows = '';
        foreach ($rows as $r => $row) {
            $cells = '';
            foreach ($row as $c => $value) {
                if ($value === '') {
                    continue;
                }
                $ref = chr(65 + $c).($r + 1);
                if (is_numeric($value)) {
                    $cells .= '<c r="'.$ref.'"><v>'.$value.'</v></c>';
                } else {
                    $strings[] = htmlspecialchars($value, ENT_XML1);
                    $cells .= '<c r="'.$ref.'" t="s"><v>'.(count($strings) - 1).'</v></c>';
                }
            }
            $sheetRows .= '<row r="'.($r + 1).'">'.$cells.'</row>';
        }

        $zip->addFromString('xl/sharedStrings.xml', '<?xml version="1.0"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .implode('', array_map(fn ($s) => '<si><t>'.$s.'</t></si>', $strings)).'</sst>');
        $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.$sheetRows.'</sheetData></worksheet>');
        $zip->close();

        return new UploadedFile($path, $name, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    private function upload(User $user, Project $project, UploadedFile $file): TestResponse
    {
        return $this->actingAs($user)->post(route('boq-items.import.store'), [
            'project_id' => $project->id,
            'file' => $file,
        ]);
    }

    private function tokenFrom(TestResponse $response): string
    {
        $this->assertMatchesRegularExpression('#/boq-items/import/([A-Za-z0-9]{32})$#', $response->headers->get('Location'));

        return basename($response->headers->get('Location'));
    }

    public function test_contractor_excel_bill_is_previewed_and_imported(): void
    {
        $user = $this->financeUser();
        $project = Project::factory()->create(['company_id' => $user->company_id]);

        $token = $this->tokenFrom($this->upload($user, $project, $this->xlsx(self::CONTRACTOR_BILL)));

        $this->actingAs($user)
            ->get(route('boq-items.import.preview', $token))
            ->assertOk()
            ->assertSee('The header was found on row 3')
            ->assertSee('EARTHWORK')
            ->assertSee('Lump sum — imported as quantity 1.');

        $this->actingAs($user)
            ->post(route('boq-items.import.confirm', $token))
            ->assertRedirect(route('boq-items.index', ['project' => $project->id]))
            ->assertSessionHas('status');

        $items = BoqItem::query()->where('project_id', $project->id)->orderBy('sort_order')->get();
        $this->assertSame(
            ['1', '2(a)', '2(b)', '3', '4'],
            $items->pluck('item_code')->all(),
        );
        $this->assertSame(['EARTHWORK', 'EARTHWORK', 'EARTHWORK', 'CONCRETE', 'CONCRETE'], $items->pluck('category')->all());
        $this->assertStringStartsWith('Earth work in excavation by mechanical means', $items[1]->description);
        $this->assertStringEndsWith('(a) Ordinary soil', $items[1]->description);
        $this->assertSame('96200.00', $items[3]->amount);
        $this->assertSame('LS', $items[4]->unit);
        $this->assertSame('1.000', $items[4]->quantity);
        $this->assertSame(182200.0, (float) $items->sum('amount'));
        $this->assertSame($user->id, $items[0]->created_by);
    }

    public function test_unpriced_csv_boq_imports_with_rate_zero(): void
    {
        $user = $this->financeUser();
        $project = Project::factory()->create(['company_id' => $user->company_id]);
        $csv = "Item,Description,Unit,Qty,Rate\n1,Brickwork in CM 1:6,Cum,40,\n2,Plaster 12 mm,Sqm,300,\n";

        $token = $this->tokenFrom($this->upload($user, $project, UploadedFile::fake()->createWithContent('client-boq.csv', $csv)));
        $this->actingAs($user)->post(route('boq-items.import.confirm', $token))->assertRedirect();

        $this->assertSame(2, BoqItem::query()->where('project_id', $project->id)->count());
        $this->assertSame(0.0, (float) BoqItem::query()->sum('amount'));
    }

    public function test_rows_with_bad_numbers_are_skipped_and_reported(): void
    {
        $user = $this->financeUser();
        $project = Project::factory()->create(['company_id' => $user->company_id]);
        $csv = "Description,Qty,Rate\nGood line,2,100\nBad line,two,100\n";

        $token = $this->tokenFrom($this->upload($user, $project, UploadedFile::fake()->createWithContent('boq.csv', $csv)));

        $this->actingAs($user)
            ->get(route('boq-items.import.preview', $token))
            ->assertSee('Quantity &quot;two&quot; is not a number.', false);

        $this->actingAs($user)->post(route('boq-items.import.confirm', $token));

        $this->assertSame(['Good line'], BoqItem::query()->pluck('description')->all());
    }

    public function test_user_can_fix_a_wrongly_guessed_column(): void
    {
        $user = $this->financeUser();
        $project = Project::factory()->create(['company_id' => $user->company_id]);
        $csv = "Work,Measure,Price\nWaterproofing,25,400\n";

        $token = $this->tokenFrom($this->upload($user, $project, UploadedFile::fake()->createWithContent('boq.csv', $csv)));

        $this->actingAs($user)->post(route('boq-items.import.confirm', $token), [
            'header_row' => 1,
            'headings' => 1,
            'map' => ['description' => 0, 'quantity' => 1, 'rate' => 2, 'unit' => '', 'item_code' => '', 'category' => '', 'amount' => ''],
        ])->assertRedirect();

        $item = BoqItem::query()->sole();
        $this->assertSame('Waterproofing', $item->description);
        $this->assertSame('10000.00', $item->amount);
    }

    public function test_replace_existing_removes_old_lines_of_that_project_only(): void
    {
        $user = $this->financeUser();
        $project = Project::factory()->create(['company_id' => $user->company_id]);
        $otherProject = Project::factory()->create(['company_id' => $user->company_id]);
        $this->actingAs($user);
        BoqItem::create(['project_id' => $project->id, 'description' => 'Old line', 'quantity' => 1, 'rate' => 1, 'amount' => 1]);
        BoqItem::create(['project_id' => $otherProject->id, 'description' => 'Other project line', 'quantity' => 1, 'rate' => 1, 'amount' => 1]);

        $token = $this->tokenFrom($this->upload($user, $project, UploadedFile::fake()->createWithContent('boq.csv', "Description,Qty,Rate\nNew line,1,10\n")));
        $this->post(route('boq-items.import.confirm', $token), ['replace_existing' => 1])->assertRedirect();

        $this->assertSame(['New line', 'Other project line'], BoqItem::query()->orderBy('description')->pluck('description')->all());
    }

    public function test_old_xls_file_is_rejected_with_a_helpful_message(): void
    {
        $user = $this->financeUser();
        $project = Project::factory()->create(['company_id' => $user->company_id]);

        $this->upload($user, $project, UploadedFile::fake()->create('old-bill.xls', 20))
            ->assertSessionHasErrors(['file' => 'Upload an Excel .xlsx or a .csv file. For an old .xls file, open it in Excel and use File → Save As → Excel Workbook (.xlsx).']);
    }

    public function test_file_renamed_to_xlsx_that_is_not_a_workbook_is_rejected(): void
    {
        $user = $this->financeUser();
        $project = Project::factory()->create(['company_id' => $user->company_id]);

        $this->upload($user, $project, UploadedFile::fake()->createWithContent('bill.xlsx', 'not a zip'))
            ->assertSessionHasErrors('file');
    }

    public function test_worker_cannot_import(): void
    {
        $worker = User::factory()->create();
        $project = Project::factory()->create(['company_id' => $worker->company_id]);

        $this->actingAs($worker)->get(route('boq-items.import.create'))->assertForbidden();
        $this->upload($worker, $project, UploadedFile::fake()->createWithContent('boq.csv', "Description,Qty,Rate\nX,1,1\n"))->assertForbidden();
    }

    public function test_project_from_another_company_is_rejected(): void
    {
        $user = $this->financeUser();
        $foreignProject = Project::factory()->create();

        $this->upload($user, $foreignProject, UploadedFile::fake()->createWithContent('boq.csv', "Description,Qty,Rate\nX,1,1\n"))
            ->assertSessionHasErrors('project_id');
    }

    public function test_another_user_cannot_use_someone_elses_upload(): void
    {
        $user = $this->financeUser();
        $project = Project::factory()->create(['company_id' => $user->company_id]);
        $token = $this->tokenFrom($this->upload($user, $project, UploadedFile::fake()->createWithContent('boq.csv', "Description,Qty,Rate\nX,1,1\n")));

        $intruder = $this->financeUser();

        $this->actingAs($intruder)
            ->post(route('boq-items.import.confirm', $token))
            ->assertRedirect(route('boq-items.import.create'));

        $this->assertSame(0, BoqItem::query()->withoutGlobalScopes()->count());
    }

    public function test_template_downloads(): void
    {
        $response = $this->actingAs($this->financeUser())->get(route('boq-items.import.template'));

        $response->assertOk();
        $this->assertStringContainsString('Category,Description,Unit,Quantity,Rate,Amount', $response->streamedContent());
    }
}
