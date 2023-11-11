<?php
/*
    linux-noob-mybb-redirects

    A script to return 301 Moved Permanently status codes for URLs for the old Invision Community
    forums and redirect users and search engines to the new posts in MyBB.

    Copyright (C) 2021 Peter Upfold. See LICENSE file for details.
*/

/**
 * Risks and Caveats
 * 
 * Note that this **does** allow unauthenticated users to enumerate which thread IDs exist
 * and which do not -- although they will be redirected to a page that requires login,
 * so they are not able to determine the contents/title of those threads.
 */

/* Functions */

/**
 * Display an error instead of redirecting.
 */
function fail_with_message($message) {
    header('HTTP/1.1 503 Service Unavailable');
    ?><html><head><title>Linux-Noob: Service Unavailable</title></head>
    <body>
    <img src='/linuxnoob_hatch_64.png' alt='Linux-Noob logo'>
    <h1>There is a problem</h1>
    <p>Apologies, but it was not possible to redirect you to where this topic is now located.</p>
    <p>Please notify the site administrators that you saw this error.</p>
    <p><strong><?php echo $message; ?></strong></p>
    </body>
    </html><?php
    die();
}

/**
 * Issue a 404 Not Found.
 */
function issue_404($reason) {
    header('HTTP/1.1 404 Not Found');
    ?><html><head><title>Linux-Noob: Item Not Found</title></head>
    <body>
    <img src='/linuxnoob_hatch_64.png' alt='Linux-Noob logo'>
    <h1>We weren't able to find that</h1>
    <p>Apologies, but it was not possible to redirect you to where this topic is now located.</p>
    <p>Please notify the site administrators that you saw this error.</p>
    <p><strong><?php echo $reason; ?></strong></p>
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
    header('Location: ' . sanitise_url(MYBB_URI_BASE . $destination));
    ?><h1>This content is now found at <a href='<?php echo sanitise_url(MYBB_URI_BASE . $destination); ?>'><?php echo sanitise_url(MYBB_URI_BASE . $destination); ?></a></h1>
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

    $old_tid = intval($matches[1]);
    $slug = ltrim($matches[2], "- \r\n\0\t\v");

    // look up in legacy database and find firstpost Unix timestamp so we can find it in the new database
    $old_db_conn = new \mysqli('localhost', IPS_USERNAME, IPS_PASSWORD, IPS_DATABASE);
    if ($old_db_conn->connect_errno) {
        $old_db_conn->close();
        fail_with_message('Unable to establish legacy database connection');
    }

    if (!($legacy_thread_stmt = $old_db_conn->prepare(
        'SELECT start_date FROM ' . IPS_PREFIX . 'forums_topics WHERE tid = ?'
    ))) {
        $old_db_conn->close();
        fail_with_message('Unable to prepare legacy thread database query statement');
    }

    $legacy_thread_stmt->bind_param('i', $old_tid);
    $legacy_thread_stmt->execute();
    $legacy_thread_stmt->bind_result($start_date);
    $legacy_thread_stmt->store_result();

    if ($legacy_thread_stmt->num_rows !== 1) {
	$num_rows = $legacy_thread_stmt->num_rows;
        $legacy_thread_stmt->close();
        $old_db_conn->close();
        issue_404('There were ' . intval($num_rows) . ' rows found for the query for this legacy thread.');
    }
    
    $legacy_thread_stmt->fetch();
    $legacy_thread_stmt->close();

    $old_db_conn->close();     

    // armed with the start_date, let's find the new thread by the new 'dateline' item

    $new_db_conn = new \mysqli('localhost', MYBB_USERNAME, MYBB_PASSWORD, MYBB_DATABASE);
    if ($new_db_conn->connect_errno) {
        $new_db_conn->close();
        fail_with_message('Unable to establish new database connection');
    }
    
    if (!($new_thread_stmt = $new_db_conn->prepare(
        'SELECT tid FROM ' . MYBB_PREFIX . 'threads WHERE dateline = ?'
    ))) {
        $new_db_conn->close();
        fail_with_message('Unable to prepare new thread database query statement');
    }

    $new_thread_stmt->bind_param('i', $start_date);
    $new_thread_stmt->execute();
    $new_thread_stmt->bind_result($new_tid);
    $new_thread_stmt->store_result();

    if ($new_thread_stmt->num_rows !== 1) {
	$num_rows = $new_thread_stmt->num_rows;
        $new_thread_stmt->close();
        $new_db_conn->close();
        issue_404('There were ' . intval($num_rows) . ' rows found for the query for this new thread.');
    }

    $new_thread_stmt->fetch();
    $new_thread_stmt->close();

    $new_db_conn->close();

    issue_301('/thread-' . intval($new_tid) . '.html');
}

/**
 * Issue a 301 redirect for a legacy forum link to its current link.
 */
function lookup_legacy_forum() {
    //  look up IPS_PREFIX forums_forums -> id.
    // get from core_sys_lang_words the forums_forum_[id] language string -> word_default field

    $matches = [];
    $matched = preg_match('/forum\/([0-9]+)([a-z\-]+)/', $_SERVER['REQUEST_URI'], $matches);

    if (!$matched) {
        return false;
    }

    if (count($matches) !== 3) {
        return false;
    }

    $old_fid = intval($matches[1]);

    // look up in legacy database 
    $old_db_conn = new \mysqli('localhost', IPS_USERNAME, IPS_PASSWORD, IPS_DATABASE);
    if ($old_db_conn->connect_errno) {
        $old_db_conn->close();
        fail_with_message('Unable to establish legacy database connection');
    }

    $word_key = 'forums_forum_' . $old_fid;

    if (!($legacy_forum_stmt = $old_db_conn->prepare(
        'SELECT word_default FROM ' . IPS_PREFIX . 'core_sys_lang_words WHERE word_key = ?'
    ))) {
        $old_db_conn->close();
        fail_with_message('Unable to prepare legacy forum database query statement');
    }

    $legacy_forum_stmt->bind_param('s', $word_key);
    $legacy_forum_stmt->execute();
    $legacy_forum_stmt->bind_result($word_default);
    $legacy_forum_stmt->store_result();
    if ($legacy_forum_stmt->num_rows !== 1) {
	$num_rows = $legacy_forum_stmt->num_rows;
        $legacy_forum_stmt->close();
        $old_db_conn->close();
        issue_404('There were ' . intval($num_rows) . ' rows found for the query for this legacy forum.');
    }
    $legacy_forum_stmt->fetch();
    $legacy_forum_stmt->close();
    $old_db_conn->close();

     // armed with the start_date, let's find the new thread by the new 'dateline' item

     $new_db_conn = new \mysqli('localhost', MYBB_USERNAME, MYBB_PASSWORD, MYBB_DATABASE);
     if ($new_db_conn->connect_errno) {
         $new_db_conn->close();
         fail_with_message('Unable to establish new database connection');
     }
     
     if (!($new_forum_stmt = $new_db_conn->prepare(
         'SELECT fid FROM ' . MYBB_PREFIX . 'forums WHERE name = ?'
     ))) {
         $new_db_conn->close();
         fail_with_message('Unable to prepare new forum database query statement');
     }
 
     $new_forum_stmt->bind_param('s', $word_default);
     $new_forum_stmt->execute();
     $new_forum_stmt->bind_result($new_fid);
     $new_forum_stmt->store_result();
 
     if ($new_forum_stmt->num_rows !== 1) {
	 $num_rows = $new_forum_stmt->num_rows;
         $new_forum_stmt->close();
         $new_db_conn->close();
         issue_404('There were ' . intval($num_rows) . ' rows found for the query for this new forum.');
     }
 
     $new_forum_stmt->fetch();
     $new_forum_stmt->close();
 
     $new_db_conn->close();
 
     issue_301('/forum-' . intval($new_fid) . '.html');

}


/* Main Logic */
if (!file_exists(__DIR__ . '/config.php')) {
    fail_with_message('Unable to find config.');
}

require(__DIR__ . '/config.php');

if (!defined('MYBB_URI_BASE')) {
    fail_with_message('Config missing MYBB_URI_BASE');
}

if (array_key_exists('topic', $_GET)) {
    if (!lookup_legacy_thread()) {
	issue_404('No matches');
    }
}
else if (array_key_exists('forum', $_GET)) {
    if (!lookup_legacy_forum()) {
	issue_404('No matches');
    }
}

