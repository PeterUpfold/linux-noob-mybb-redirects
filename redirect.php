<?php
/*
    linux-noob-mybb-redirects

    A script to return 301 Moved Permanently status codes for URLs for the old Invision Community
    forums and redirect users and search engines to the new posts in MyBB.

    Copyright (C) 2021 Peter Upfold. See LICENSE file for details.
*/

/* Functions */

/**
 * Display an error instead of redirecting.
 */
function display_error($message) {
    header('HTTP/1.1 503 Service Unavailable');
    ?><html><head><title>Linux-Noob: Service Unavailable</title></head>
    <body>
    <img src='/linuxnoob_hatch_64.png' alt='Linux-Noob logo'>
    <h1>There is a problem</h1>
    <p>Apologies, but it was not possible to redirect you to where this topic is now located.</p>
    <p>Please notify peter@linux-noob.com that you saw this error.</p>
    <p><strong><?php echo $message; ?></strong></p>
    </body>
    </html><?php
    die();
}

/**
 * Sanitise a URL for display. Note that this is not entirely sufficient if we really were dealing
 * with arbitrary user-provided input for redirects — as it happens we're only ever pulling from our database where
 * we are assuming that inputs to the old database were sanitised at save time.
 */
function sanitise_url($url) {
    $url = filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);
    return $url;
}

/**
 * Issue a 301 Moved Permanently status code
 * 
 * @param $destination The relative destination
 */
function issue_301($destination) {
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . MYBB_URI_BASE . $destination);
    ?><h1>This topic is now found at <a href='<?php echo sanitise_url(MYBB_URI_BASE . $destination); ?>'><?php echo sanitise_url(MYBB_URI_BASE . $destination); ?></a>
    <?php
    die();
}

/**
 * Lookup a topic/thread from the legacy database and find its new URI in the
 * MyBB database. We are matching, somewhat imprecisely, using firstpost Unix timestamp,
 * as unfortunately the converter did not maintain thread/topic IDs or any other consistent
 * identifier I can find.
 * 
 */
function lookup_legacy_thread() {
    $matches = [];
    $matched = preg_match('/topic\/([0-9]+)([a-z\-]+)/', $_SERVER['REQUEST_URI'], $matches);

    if (!$matched) {
        return false;
    }

    if (count($matches) !== 3) {
        return false;
    }

    // matches[1] will be the old thread id
    // matches[2] will be the slug, with a leading hyphen

    $old_tid;
    $slug = ltrim($matches[2], "- \r\n\0\t\v");

    // look up in legacy database and find firstpost Unix timestamp so we can find it in the new database
    $old_db_conn = new \mysqli('localhost', IPS_USERNAME, IPS_PASSWORD, IPS_DATABASE);
    if ($old_db_conn->connect_errno) {
        display_error('Unable to establish legacy database connection');
    }

    
     

}


/* Main Logic */
if (!file_exists(__DIR__ . '/config.php')) {
    display_error('Unable to find config.');
}

require(__DIR__ . '/config.php');

if (!defined('MYBB_URI_BASE')) {
    display_error('Config missing MYBB_URI_BASE');
}

var_dump($_SERVER['REQUEST_URI']);

