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

$topic_title = $_POST['topicName'];
$expire_datetime = $_POST['closeTime'];
$response = [];
$topic_sql = "INSERT INTO 
                vote_topics (
                    topic_title,
                    expire_datetime,
                    member_id
                )
                VALUES (
                    '$topic_title',
                    '$expire_datetime',
                    1
                )";
$topic_query = $vote_conn->query($topic_sql);

if ($topic_query) {
    $response['status'] = 'success';
}
else {
    $response['status'] = 'error';
}

echo json_encode($response);
$vote_conn->close();

