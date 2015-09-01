<?php
// In the top frame, we use cookies for session.
define('COOKIE_SESSION', true);
require_once("../../config.php");
require_once($CFG->dirroot."/pdo.php");
require_once($CFG->dirroot."/lib/lms_lib.php");

use \Tsugi\UI\Table;

header('Content-Type: text/html; charset=utf-8');
session_start();

if ( ! isAdmin() ) {
    $_SESSION['login_return'] = getUrlFull(__FILE__) . "/index.php";
    header('Location: '.$CFG->wwwroot.'/login.php');
    return;
}

$query_parms = false;

$searchfields = array("membership_id", "context_id", "user_id", "role", "role_override", 
	"created_at", "updated_at", "email", "displayname", "user_key");

$sql = "SELECT membership_id AS Membership, context_id AS Context, M.user_id as User, 
            role, role_override, M.created_at, M.updated_at, email, displayname, user_key
        FROM {$CFG->dbprefix}lti_membership as M
        JOIN {$CFG->dbprefix}lti_user AS U ON M.user_id = U.user_id
        WHERE context_id = :CID";
$query_parms = array(":CID" => $_REQUEST['context_id']);

if ( !isAdmin() ) {
    die ("Fix this");
    $sql .= "\nWHERE R.user_id = :UID";
    $query_parms = array(":UID" => $_SESSION['id']);
}

$newsql = Table::pagedQuery($sql, $query_parms, $searchfields);
// echo("<pre>\n$newsql\n</pre>\n");
$rows = $PDOX->allRowsDie($newsql, $query_parms);
$newrows = array();
foreach ( $rows as $row ) {
    $newrow = $row;
    $newrows[] = $newrow;
}

$OUTPUT->header();
$OUTPUT->bodyStart();
$OUTPUT->topNav();
$OUTPUT->flashMessages();
?>
<p>
  <a href="index.php" class="btn btn-default">View Contexts</a>
</p>
<?php

Table::pagedTable($newrows, $searchfields);

$OUTPUT->footer();

