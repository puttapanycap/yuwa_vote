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
$session_key = isset($_POST['session_key']) ? $_POST['session_key'] : '';

// Validate session key
if (empty($session_key) || !isValidSessionKey($session_key)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid session key'
    ]);
    exit;
}

// Verify topic belongs to this session
$verify_sql = "SELECT id FROM vote_topics WHERE id='$topic_id' AND session_key='$session_key'";
$verify_query = $vote_conn->query($verify_sql);
if ($verify_query->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ไม่มีสิทธิ์ลบ Topic นี้'
    ]);
    exit;
}

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
