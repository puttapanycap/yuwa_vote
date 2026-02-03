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
$topic_title = $_POST['topicTitle'];
$expire_datetime = $_POST['expireDateTime'];
$display_mode = isset($_POST['displayMode']) ? $_POST['displayMode'] : 'card';
$show_score = (isset($_POST['showScore']) && $_POST['showScore'] == 1) ? 1 : 0;
$is_public = (isset($_POST['isPublic']) && $_POST['isPublic'] == 1) ? 1 : 0;
$choices_current_arr = isset($_POST['choicesCurrentArr']) ? $_POST['choicesCurrentArr'] : [];
$choices_new_arr = isset($_POST['choicesNewArr']) ? $_POST['choicesNewArr'] : [];
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
        'message' => 'ไม่มีสิทธิ์แก้ไข Topic นี้'
    ]);
    exit;
}

$vote_mode = isset($_POST['voteMode']) ? $_POST['voteMode'] : 'single';
$max_choices = isset($_POST['maxChoices']) ? intval($_POST['maxChoices']) : 1;

$response = [];
$delete_not_ids = [];
$query_success = [];

$topic_datas = [
    'topic_title' => $topic_title,
    'expire_datetime' => $expire_datetime,
    'display_mode' => $display_mode,
    'show_score' => $show_score,
    'is_public' => $is_public,
    'vote_mode' => $vote_mode,
    'max_choices' => $max_choices,
];
$topic_sql = arrayToUpdateSQL('vote_topics', $topic_datas, ['id' => $topic_id]);
if ($vote_conn->query($topic_sql)) {
    $query_success[] = ['status' => true, 'message' => 'Topic updated successfully.'];
} else {
    $query_success[] = ['status' => false, 'message' => 'Failed to update topic.'];
}

if (count($choices_current_arr) > 0) {
    $choice_upd_sql = [];
    foreach ($choices_current_arr as $cr_key => $cr_val) {
        $update_where = [
            'id' => $cr_val['id']
        ];
        $update_data = [
            'choice_sort' => $cr_val['sort'],
            'choice_title' => $cr_val['title'],
        ];
        $choice_upd_sql[] = arrayToUpdateSQL('vote_choices', $update_data, $update_where);
        $delete_not_ids[] = $cr_val['id'];
    }
    if (count($choice_upd_sql) > 0) {
        if ($vote_conn->multi_query(implode('', $choice_upd_sql))) {
            do {
            } while ($vote_conn->next_result());
            $query_success[] = ['status' => true, 'message' => 'Choices updated successfully.'];
        } else {
            $query_success[] = ['status' => false, 'message' => 'Error updating choices.'];
        }
    }
}

// if (count($delete_not_ids) > 0) {
//     $delete_sql = "DELETE FROM vote_choices WHERE topic_id='$topic_id' AND id NOT IN (" . implode(',', $delete_not_ids) . ")";
//     if ($vote_conn->query($delete_sql)) {
//         $query_success[] = [ 'status' => true, 'message' => 'Choices not in use deleted successfully.' ];
//     }
//     else {
//         $query_success[] = [ 'status' => false, 'message' => 'Error deleting choices not in use.' ];
//     }
// }

if (count($choices_new_arr) > 0) {
    $choice_ins_sql = [];
    $insert_data = [];
    foreach ($choices_new_arr as $nr_key => $nr_val) {
        $insert_data[] = [
            'topic_id' => $topic_id,
            'choice_sort' => $nr_val['sort'],
            'choice_title' => $nr_val['title'],
        ];
    }
    $choice_ins_sql = arrayToInsertSQL('vote_choices', $insert_data, 'multi');
    if ($vote_conn->query($choice_ins_sql)) {
        $query_success[] = ['status' => true, 'message' => 'Choices inserted successfully.'];
    } else {
        $query_success[] = ['status' => false, 'message' => 'Error inserting choices.'];
    }
}

// RESULT
$query_statuses = [];
$query_messages = [];
foreach ($query_success as $qs) {
    $query_statuses[] = $qs['status'];
    if (!$qs['status']) {
        $query_messages[] = $qs['message'];
    }
}

if (!in_array(false, $query_statuses)) {
    $response['status'] = 'success';
    $response['message'] = 'ALL updated successfully';
} else {
    $response['status'] = 'error';
    $response['message'] = 'Error: ' . implode(', ', $query_messages);
}

echo json_encode($response);
$vote_conn->close();
