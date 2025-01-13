<?php

$result = [];

if ($_POST['input_password'] == 'Vote12345') {
    session_start();
    $_SESSION['session_key'] = 'Vote12345';
    $result['status'] = 'success';
}
else {
    $result['status'] = 'error';
    $result['message'] = 'รหัสผ่านไม่ถูกต้อง';
}

header("Content-Type: application/json; charset=utf-8");
echo json_encode($result);