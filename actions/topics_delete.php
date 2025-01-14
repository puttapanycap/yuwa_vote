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

$topic_id = $_POST['topic_id'];

$choice_sql = "DELETE FROM vote_choices WHERE topic_id='$topic_id' ";
if ($vote_conn->query($choice_sql) === TRUE) {

    $topic_sql = "DELETE FROM vote_topics WHERE id='$topic_id' ";

    if ($vote_conn->query($topic_sql) === TRUE) {
        $response['status'] = 'success';
        $response['message'] = 'Topic delete successfully';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Error delete topic';
    }

} else {
    $response['status'] = 'error';
    $response['message'] = 'Error delete choice';
}

echo json_encode($response);
$vote_conn->close();
