<?php

$result = [];

if ($_POST['input_password'] == 'ycapHA0012251') {
    session_start();
    $_SESSION['session_key'] = 'ycapHA0012251';
    $result['status'] = 'success';
}
else {
    $result['status'] = 'error';
    $result['message'] = 'รหัสผ่านไม่ถูกต้อง';
}

header("Content-Type: application/json; charset=utf-8");
echo json_encode($result);