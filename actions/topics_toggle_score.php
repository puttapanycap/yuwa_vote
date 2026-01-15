<?php

session_start();
define('_WEBROOT_PATH_', '../');

// Connection Setup :: START
require _WEBROOT_PATH_ . '/helpers/load_env.php';
require _WEBROOT_PATH_ . '/helpers/functions.php';
$connections = getDatabaseConnections();
$vote_conn = $connections['vote'];
// Connection Setup :: END

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit(0);
}

if (!isset($_POST['topic_id']) || !isset($_POST['show_score'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit(0);
}

$topic_id = intval($_POST['topic_id']);
$show_score = intval($_POST['show_score']) ? 1 : 0;

$stmt = $vote_conn->prepare("UPDATE vote_topics SET show_score = ? WHERE id = ? AND member_id = ?");
$stmt->bind_param("iii", $show_score, $topic_id, $_SESSION['user_id']);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => $show_score ? 'แสดงคะแนนแล้ว' : 'ซ่อนคะแนนแล้ว',
        'show_score' => $show_score
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}

$stmt->close();
$vote_conn->close();
