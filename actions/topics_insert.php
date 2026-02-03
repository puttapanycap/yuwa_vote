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

$topic_title = $_POST['topicTitle'];
$expire_datetime = $_POST['expireDateTime'];
$choices_arr = $_POST['choicesArr'];
$display_mode = isset($_POST['displayMode']) ? $_POST['displayMode'] : 'card';
$show_score = (isset($_POST['showScore']) && $_POST['showScore'] == 1) ? 1 : 0;
$is_public = (isset($_POST['isPublic']) && $_POST['isPublic'] == 1) ? 1 : 0;

// Get session key from POST
$session_key = isset($_POST['session_key']) ? $_POST['session_key'] : '';

// Validate session key
if (empty($session_key) || !isValidSessionKey($session_key)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid session key'
    ]);
    exit;
}

$response = [];
$topic_datas = [
    'topic_title' => $topic_title,
    'expire_datetime' => $expire_datetime,
    'session_key' => $session_key,
    'share_key' => generateTopicKey(16),
    'display_mode' => $display_mode,
    'show_score' => $show_score,
    'is_public' => $is_public,
    'vote_mode' => isset($_POST['voteMode']) ? $_POST['voteMode'] : 'single',
    'max_choices' => isset($_POST['maxChoices']) ? intval($_POST['maxChoices']) : 1
];
$topic_sql = arrayToInsertSQL('vote_topics', $topic_datas);

if ($vote_conn->query($topic_sql) === TRUE) {
    $vote_last_id = $vote_conn->insert_id;

    $choice_sql = "";
    $choices_arr = $_POST['choicesArr'];
    $choice_datas = [];
    foreach ($choices_arr as $choice) {
        $choice_datas[] = [
            'topic_id' => $vote_last_id,
            'choice_title' => $choice['title'],
            'choice_sort' => $choice['sort'],
        ];
    }
    $choice_sql = arrayToInsertSQL('vote_choices', $choice_datas, 'multi');
    if ($vote_conn->query($choice_sql) === TRUE) {
        $response['status'] = 'success';
        $response['message'] = 'Topic created successfully';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Error creating topic';
    }
} else {
    $response['status'] = 'error';
    $response['sql'] = $topic_sql;
    $response['message'] = 'Error creating topic';
}

echo json_encode($response);
$vote_conn->close();
