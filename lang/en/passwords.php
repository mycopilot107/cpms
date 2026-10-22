<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Password Reset Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are the default lines which match reasons
    | that are given by the password broker for a password update attempt
    | outcome such as failure due to an invalid password / reset token.
    |
    */

    'reset' => 'Your password has been reset. You can now sign in with your new password.',
    'sent' => 'We have emailed your password reset link.',
    'throttled' => 'Please wait a moment before requesting another reset link.',
    'token' => 'This password reset link is invalid or has expired. Please request a new one.',
    'user' => "We can't find a user with that email address.",

    /*
    | Shown after a "forgot password" submission. It is intentionally the same
    | whether or not the email matched an account, so the form can't be used to
    | find out which email addresses are registered.
    */
    'sent_generic' => 'If an account matches that email address, we have sent a password reset link to it. Please check your inbox — and your spam folder — in a few minutes.',

];
