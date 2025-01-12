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

// Parameters Setup :: START
$requestData = $_REQUEST;
$draw = isset($requestData['draw']) ? intval($requestData['draw']) : 0;
$searchValue = isset($requestData['input_search']) ? $requestData['input_search'] : "";
$orderableDir = isset($requestData['order'][0]['dir']) ? $requestData['order'][0]['dir'] : "asc";
$orderableIndexes = 0;
foreach ($requestData['columns'] as $index => $column) {
    if (isset($column['orderable']) && $column['orderable'] === true) {
        $orderableIndexes = $index;
    }
}
$hip_columns = ['expire_datetime', 'topic_title', null];
$hip_order_by = $hip_columns[$orderableIndexes];
// Parameters Setup :: END

$topic_sql = "  SELECT
                    id,
                    topic_title,
                    expire_datetime,
                    member_id,
                    share_key
                FROM
                    vote_topics ";
$topic_query = $vote_conn->query($topic_sql);
$total_all = $topic_query->num_rows;

if ( strlen($searchValue) > 0 ) {
    $topic_sql .= " WHERE topic_title LIKE '%$searchValue%'";
}
$topic_query = $vote_conn->query($topic_sql);
$total_filtered = $topic_query->num_rows;

$topic_sql .= " ORDER BY $hip_order_by $orderableDir ";
$topic_sql .= " LIMIT " . intval($requestData['start']) . ", " . intval($requestData['length'])." ";
$topic_query = $vote_conn->query($topic_sql);
$topic_data = [];
while ($topic_row = $topic_query->fetch_assoc()) {
    $topic_data[] = $topic_row;
}

$response = [
    "draw" => $draw,
    "recordsTotal" => $total_all,
    "recordsFiltered" => $total_filtered,
    "data" => $topic_data
];

echo json_encode($response);
$vote_conn->close();

