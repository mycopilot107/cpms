{{--
    Country + currency pickers for a company. Picking a country fills in that
    country's usual currency; the user can still choose a different one.
    Expects optional: $country, $currency (current values), $detect (bool —
    guess the country from the browser time zone when nothing is chosen yet).

    The native <select> elements stay in the form and are what gets
    submitted. With JavaScript, each is shown as a searchable list that opens
    inside the page instead of the browser's own popup.
--}}
@php
    $countries = \App\Support\Money::countries();
    $currencies = \App\Support\Money::currencies();
    $selectedCountry = old('country', $country ?? config('regional.default_country'));
    $selectedCurrency = old('currency', $currency ?? ($countries[$selectedCountry] ?? config('regional.default_currency')));
    $inputClass = 'mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500';
@endphp
<div class="space-y-5">
    <div>
        <label for="country" class="block text-sm font-medium text-slate-700">Country</label>
        <select id="country" name="country" required data-combobox data-search-placeholder="Search countries"
                class="{{ $inputClass }} @error('country') border-red-400 @enderror">
            @foreach ($countries as $name => $code)
                <option value="{{ $name }}" data-currency="{{ $code }}" @selected($selectedCountry === $name)>{{ $name }}</option>
            @endforeach
        </select>
        @error('country')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="currency" class="block text-sm font-medium text-slate-700">Currency for project costs</label>
        <select id="currency" name="currency" required data-combobox data-search-placeholder="Search currencies"
                class="{{ $inputClass }} @error('currency') border-red-400 @enderror">
            @foreach ($currencies as $code => $info)
                <option value="{{ $code }}" @selected($selectedCurrency === $code)>{{ $code }} — {{ $info['label'] }}</option>
            @endforeach
        </select>
        @error('currency')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<script>
    (function () {
        function enhance(select) {
            var wrapper = document.createElement('div');
            wrapper.className = 'relative mt-1';

            var button = document.createElement('button');
            button.type = 'button';
            button.id = select.id + '-button';
            button.className = 'flex w-full items-center justify-between gap-2 rounded-md border bg-white px-3 py-2 text-left text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 '
                + (select.classList.contains('border-red-400') ? 'border-red-400' : 'border-slate-300');
            button.setAttribute('aria-haspopup', 'listbox');
            button.setAttribute('aria-expanded', 'false');
            button.innerHTML = '<span class="truncate"></span>'
                + '<svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">'
                + '<path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.39a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>';
            var buttonText = button.querySelector('span');

            var panel = document.createElement('div');
            panel.className = 'absolute z-30 mt-1 hidden w-full overflow-hidden rounded-md border border-slate-200 bg-white shadow-lg';

            var search = document.createElement('input');
            search.type = 'text';
            search.autocomplete = 'off';
            search.placeholder = select.getAttribute('data-search-placeholder') || 'Search';
            search.className = 'block w-full border-0 border-b border-slate-100 px-3 py-2 text-sm focus:outline-none focus:ring-0';
            search.setAttribute('aria-label', search.placeholder);

            var list = document.createElement('ul');
            list.id = select.id + '-listbox';
            list.className = 'max-h-60 overflow-y-auto py-1';
            list.setAttribute('role', 'listbox');
            button.setAttribute('aria-controls', list.id);

            panel.appendChild(search);
            panel.appendChild(list);
            wrapper.appendChild(button);
            wrapper.appendChild(panel);

            select.classList.add('hidden');
            select.setAttribute('tabindex', '-1');
            select.parentNode.insertBefore(wrapper, select.nextSibling);

            var label = document.querySelector('label[for="' + select.id + '"]');
            if (label) {
                label.setAttribute('for', button.id);
                button.setAttribute('aria-labelledby', (label.id = label.id || select.id + '-label'));
            }

            var items = [];
            var activeIndex = -1;

            Array.prototype.forEach.call(select.options, function (option, index) {
                var item = document.createElement('li');
                item.id = select.id + '-option-' + index;
                item.setAttribute('role', 'option');
                item.className = 'cursor-pointer px-3 py-2 text-sm text-slate-700';
                item.textContent = option.text;
                item.addEventListener('mousedown', function (event) {
                    event.preventDefault();
                    choose(index);
                });
                item.addEventListener('mousemove', function () {
                    setActive(visibleItems().indexOf(item));
                });
                list.appendChild(item);
                items.push({ el: item, option: option, index: index });
            });

            function visibleItems() {
                return items.filter(function (it) { return !it.el.classList.contains('hidden'); }).map(function (it) { return it.el; });
            }

            function setActive(position) {
                var visible = visibleItems();
                items.forEach(function (it) { it.el.classList.remove('bg-indigo-50', 'text-indigo-700'); });
                activeIndex = Math.max(-1, Math.min(position, visible.length - 1));
                if (activeIndex >= 0) {
                    var el = visible[activeIndex];
                    el.classList.add('bg-indigo-50', 'text-indigo-700');
                    el.scrollIntoView({ block: 'nearest' });
                    search.setAttribute('aria-activedescendant', el.id);
                }
            }

            function refresh() {
                var current = select.options[select.selectedIndex];
                buttonText.textContent = current ? current.text : '';
                items.forEach(function (it) {
                    var selected = it.index === select.selectedIndex;
                    it.el.setAttribute('aria-selected', selected ? 'true' : 'false');
                    it.el.classList.toggle('font-semibold', selected);
                });
            }

            function filter() {
                var term = search.value.trim().toLowerCase();
                items.forEach(function (it) {
                    it.el.classList.toggle('hidden', term !== '' && it.option.text.toLowerCase().indexOf(term) === -1);
                });
                setActive(0);
            }

            function open() {
                panel.classList.remove('hidden');
                button.setAttribute('aria-expanded', 'true');
                search.value = '';
                filter();
                var visible = visibleItems();
                var selectedEl = items[select.selectedIndex] && items[select.selectedIndex].el;
                setActive(selectedEl ? visible.indexOf(selectedEl) : 0);
                search.focus();
            }

            function close(returnFocus) {
                panel.classList.add('hidden');
                button.setAttribute('aria-expanded', 'false');
                if (returnFocus) button.focus();
            }

            function choose(index) {
                if (select.selectedIndex !== index) {
                    select.selectedIndex = index;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
                refresh();
                close(true);
            }

            button.addEventListener('click', function () {
                panel.classList.contains('hidden') ? open() : close(true);
            });
            button.addEventListener('keydown', function (event) {
                if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                    event.preventDefault();
                    open();
                }
            });
            search.addEventListener('input', filter);
            search.addEventListener('keydown', function (event) {
                var visible = visibleItems();
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    setActive(activeIndex + 1);
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    setActive(activeIndex - 1);
                } else if (event.key === 'Enter') {
                    event.preventDefault();
                    if (activeIndex >= 0 && visible[activeIndex]) {
                        choose(items.find(function (it) { return it.el === visible[activeIndex]; }).index);
                    }
                } else if (event.key === 'Escape') {
                    event.preventDefault();
                    close(true);
                } else if (event.key === 'Tab') {
                    close(false);
                }
            });
            document.addEventListener('mousedown', function (event) {
                if (!wrapper.contains(event.target)) close(false);
            });
            select.addEventListener('change', refresh);

            refresh();
        }

        var country = document.getElementById('country');
        var currency = document.getElementById('currency');
        if (!country || !currency) return;

        function syncCurrency() {
            var option = country.options[country.selectedIndex];
            var code = option && option.getAttribute('data-currency');
            if (code && currency.value !== code) {
                currency.value = code;
                currency.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        country.addEventListener('change', syncCurrency);

        @if (($detect ?? false) && ! old('country'))
            try {
                var tz = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
                var byZone = {
                    'Asia/Kolkata': 'India', 'Asia/Calcutta': 'India', 'Asia/Dubai': 'United Arab Emirates',
                    'Asia/Riyadh': 'Saudi Arabia', 'Asia/Qatar': 'Qatar', 'Asia/Muscat': 'Oman',
                    'Asia/Kuwait': 'Kuwait', 'Asia/Bahrain': 'Bahrain', 'Asia/Singapore': 'Singapore',
                    'Asia/Kuala_Lumpur': 'Malaysia', 'Asia/Kathmandu': 'Nepal', 'Asia/Colombo': 'Sri Lanka',
                    'Asia/Dhaka': 'Bangladesh', 'Europe/London': 'United Kingdom', 'Europe/Dublin': 'Ireland',
                    'Europe/Berlin': 'Germany', 'Europe/Paris': 'France', 'Europe/Amsterdam': 'Netherlands',
                    'Africa/Johannesburg': 'South Africa', 'Africa/Nairobi': 'Kenya', 'Africa/Lagos': 'Nigeria',
                    'Pacific/Auckland': 'New Zealand'
                };
                var guess = byZone[tz]
                    || (/^Australia\//.test(tz) ? 'Australia' : null)
                    || (/^America\/(Toronto|Vancouver|Edmonton|Winnipeg|Halifax|St_Johns|Regina)/.test(tz) ? 'Canada' : null)
                    || (/^(America|US)\//.test(tz) ? 'United States' : null);
                if (guess) {
                    country.value = guess;
                    syncCurrency();
                }
            } catch (e) {}
        @endif

        document.querySelectorAll('select[data-combobox]').forEach(enhance);
    })();
</script>
