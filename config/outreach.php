<?php

/*
|--------------------------------------------------------------------------
| Outreach email (/admin/send-email)
|--------------------------------------------------------------------------
| The page has no login. When OUTREACH_ACCESS_KEY is set, it opens only after
| visiting /admin/send-email?key=<that value> once in the browser. In
| production the page stays hidden (404) until a key is set, so a public
| server can't be used to send mail by anyone who finds the URL.
*/

return [

    'access_key' => env('OUTREACH_ACCESS_KEY'),

    // Emails sent per scheduler run (once a minute) or per "Send now" click.
    'batch_size' => (int) env('OUTREACH_BATCH_SIZE', 20),

];
