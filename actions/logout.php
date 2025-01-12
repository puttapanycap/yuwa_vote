<?php

session_start();

if (session_destroy()) {
    $response['status'] = 'success';
    $response['message'] = 'Topic delete successfully';
} else {
    $response['status'] = 'error';
    $response['message'] = 'Error delete topic';
}

header("Content-Type: application/json; charset=utf-8");
echo json_encode($response);