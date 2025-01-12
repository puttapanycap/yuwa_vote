<?php

session_start();
header("Content-Type: application/json; charset=utf-8");
define('_WEBROOT_PATH_', '../');

// Connection Setup :: START
require _WEBROOT_PATH_ . '/helpers/load_env.php';
require _WEBROOT_PATH_ . '/helpers/functions.php';
$connections = getDatabaseConnections();
$vote_conn = $connections['vote'];
// Connection Setup :: END

$choice_id = $_POST['choice_id'];
$choice_sql = "DELETE FROM vote_choices WHERE id='$choice_id' ";

if ($vote_conn->query($choice_sql) === TRUE) {
    $response['status'] = 'success';
    $response['message'] = 'Choice delete successfully';
} else {
    $response['status'] = 'error';
    $response['message'] = 'Error delete choice';
}

echo json_encode($response);
$vote_conn->close();