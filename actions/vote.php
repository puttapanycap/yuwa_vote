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
$vote_datas = [
    'topic_id' => $_POST['topic_id'],
    'choice_id' => $_POST['choice_id'],
    'timestamp' => date('Y-m-d H:i:s'),
    'ipaddress' => $IPAddress,
    'cookie_key' => $rememberKey,
];
$vote_sql = arrayToInsertSQL('vote_results', $vote_datas);

if ($vote_conn->query($vote_sql) === TRUE) {
    $response['status'] = 'success';
    $response['message'] = 'Topic created successfully';
} else {
    $response['status'] = 'error';
    $response['message'] = 'Error creating topic';
}

echo json_encode($response);
$vote_conn->close();
