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

$cookieName = 'remember_key';
$rememberKey = getRememberKeyCookie($cookieName);
$client_info = getClientInfo();
$IPAddress = explode(', ', $client_info['ip_address']);
$IPAddress = $IPAddress[0];

$response = [];
$topic_id = $_POST['topic_id'];

// Handle both single choice_id (legacy) and choice_ids array (new)
$choice_ids = [];
if (isset($_POST['choice_ids']) && is_array($_POST['choice_ids'])) {
    $choice_ids = $_POST['choice_ids'];
} elseif (isset($_POST['choice_id'])) {
    $choice_ids = [$_POST['choice_id']];
}

if (empty($choice_ids)) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่ได้เลือกตัวเลือก']);
    exit;
}

$timestamp = date('Y-m-d H:i:s');
$all_success = true;

foreach ($choice_ids as $choice_id) {
    $vote_datas = [
        'topic_id' => $topic_id,
        'choice_id' => $choice_id,
        'timestamp' => $timestamp,
        'ipaddress' => $IPAddress,
        'cookie_key' => $rememberKey,
    ];
    $vote_sql = arrayToInsertSQL('vote_results', $vote_datas);

    if ($vote_conn->query($vote_sql) !== TRUE) {
        $all_success = false;
    }
}

if ($all_success) {
    $response['status'] = 'success';
    $response['message'] = 'Vote recorded successfully';
} else {
    $response['status'] = 'error';
    $response['message'] = 'Error recording vote';
}

echo json_encode($response);
$vote_conn->close();

