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

$result = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']) ?? '';
    $password = trim($_POST['password']) ?? '';

    if ($username && $password) {
        $sql = "SELECT * FROM vote_members WHERE member_username = '$username' ";
        $query = $vote_conn->query($sql);
        $user = $query->fetch_assoc();
        $options = [ 'cost' => 10 ];
        if ($user && password_verify($password, $user['member_password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['member_username'];
            $result['status'] = 'success';
            $result['message'] = 'สำเร็จ';
        } else {
            $result['status'] = 'error';
            $result['message'] = 'Username หรือ Password ไม่ถูกต้อง';
        }
    } else {
        $result['status'] = 'error';
        $result['message'] = 'กรุณากรอกข้อมูลใให้ครบทุกช่อง';
    }
}

header("Content-Type: application/json; charset=utf-8");
echo json_encode($result);