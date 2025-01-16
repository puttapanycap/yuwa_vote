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
    $email = trim($_POST['email']) ?? '';

    $password_options = ['cost' => 10];
    $store_password = $password;
    $passwordHash = password_hash($store_password,  PASSWORD_BCRYPT, $password_options);

    $sql = "SELECT * FROM vote_members WHERE member_username = '$username' ";
    $query = $vote_conn->query($sql);
    if ($query->num_rows == 0) {
        $ins_sql = "INSERT INTO vote_members (member_username, member_password, member_email) VALUES ('$username', '$passwordHash', '$email')";
        if ($vote_conn->query($ins_sql)) {
            $result['status'] = 'success';
            $result['message'] = 'ลงทะเบียนสำเร็จ';
        }
        else {
            $result['status'] = 'error';
            $result['message'] = 'ระบบขัดข้อง, ลงทะเบียนไม่สำเร็จ!';
        }
    } else {
        $result['status'] = 'error';
        $result['message'] = 'มี Username นี้ในระบบแล้ว';
    }
}

header("Content-Type: application/json; charset=utf-8");
echo json_encode($result);
