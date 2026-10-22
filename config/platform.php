<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Platform super admins
    |--------------------------------------------------------------------------
    |
    | A super admin can see EVERY company on the platform (crossing the usual
    | tenant boundary) at /platform/companies, together with each company's
    | subscription / trial expiry. This is the only place in the app where one
    | account sees across companies, so access is deliberately narrow.
    |
    | Super admins are now controlled by users.is_admin. This file is retained
    | as a stable platform configuration namespace for the console; it does not
    | grant access by email or environment variable.
    |
    */

    'super_admins' => [],

];
