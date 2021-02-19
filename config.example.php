<?php
/*
    linux-noob-mybb-redirects

    A script to return 301 Moved Permanently status codes for URLs for the old Invision Community
    forums and redirect users and search engines to the new posts in MyBB.

    Copyright (C) 2021 Peter Upfold. See LICENSE file for details.

    Config Example
*/

// Details for legacy IPS database
define('IPS_DATABASE', 'database_name');
define('IPS_USERNAME', 'database_username');
define('IPS_PASSWORD', 'database_password');
define('IPS_PREFIX', 'ibf_');

// Details for new MyBB database
define('MYBB_DATABASE', 'database_name');
define('MYBB_USERNAME', 'database_username');
define('MYBB_PASSWORD', 'database_password');
define('MYBB_PREFIX', 'mybb_');

define('MYBB_URI_BASE', 'https://www.linux-noob.com/forums');