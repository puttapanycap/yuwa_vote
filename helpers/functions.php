<?php

// Database configuration function
function getDatabaseConnections(): array
{
    $vote_host = $_ENV['VOTE_DB_HOST'];
    $vote_user = $_ENV['VOTE_DB_USER'];
    $vote_pass = $_ENV['VOTE_DB_PASS'];
    $vote_db = $_ENV['VOTE_DB_NAME'];

    // Work Connection
    $pdo_vote = new mysqli($vote_host, $vote_user, $vote_pass, $vote_db);
    $pdo_vote->set_charset("utf8");
    $pdo_vote->connect_error ? die("Connection failed: " . $pdo_vote->connect_error) : null;

    return [
        'vote' => $pdo_vote
    ];
}

function convertDateToWeekdayArr($date): array
{
    $weekdays = [
        [
            "name_en_full" => "Sunday",
            "name_th_short" => "อา.",
            "color_class" => "danger"
        ],
        [
            "name_en_full" => "Monday",
            "name_th_short" => "จ.",
            "color_class" => "primary"
        ],
        [
            "name_en_full" => "Tuesday",
            "name_th_short" => "อ.",
            "color_class" => "success"
        ],
        [
            "name_en_full" => "Wednesday",
            "name_th_short" => "พ.",
            "color_class" => "info"
        ],
        [
            "name_en_full" => "Thursday",
            "name_th_short" => "พฤ.",
            "color_class" => "warning"
        ],
        [
            "name_en_full" => "Friday",
            "name_th_short" => "ศ.",
            "color_class" => "secondary"
        ],
        [
            "name_en_full" => "Saturday",
            "name_th_short" => "ส.",
            "color_class" => "dark"
        ]
    ];

    $timestamp = strtotime($date);
    $englishDay = date('w', $timestamp); // คืนค่าชื่อวันเป็นภาษาอังกฤษ (Monday, Tuesday, ...)

    return $weekdays[$englishDay] ?? []; // แปลงเป็นวันภาษาไทย
}

function getClientInfo()
{
    $clientInfo = [];

    // Get client IP address
    $clientInfo['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

    // Check for proxy or forwarded IP address
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $clientInfo['ip_address'] = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $clientInfo['ip_address'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    // Get User-Agent (browser details)
    $clientInfo['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

    // Get server details
    $clientInfo['server_name'] = $_SERVER['SERVER_NAME'] ?? 'UNKNOWN';
    $clientInfo['server_addr'] = $_SERVER['SERVER_ADDR'] ?? 'UNKNOWN';
    $clientInfo['request_method'] = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
    $clientInfo['request_uri'] = $_SERVER['REQUEST_URI'] ?? 'UNKNOWN';

    return $clientInfo;
}

function generateSessionKey()
{
    // สร้างคีย์แบบสุ่ม 32 ไบต์ และเข้ารหัสในรูปแบบ Base64
    $key = bin2hex(random_bytes(32));
    return $key;
}

function generateRememberKey()
{
    // สร้างคีย์แบบสุ่ม 64 ไบต์ และเข้ารหัสในรูปแบบ Base64
    $key = base64_encode(random_bytes(64));
    return $key;
}

function arrayToInsertSQL($tableName, $data)
{
    // ตรวจสอบว่าข้อมูลไม่ว่างเปล่า
    if (empty($tableName) || empty($data) || !is_array($data)) {
        throw new InvalidArgumentException("Invalid table name or data.");
    }

    // สร้างรายการคอลัมน์และค่าจาก Array
    $columns = implode(", ", array_keys($data));

    // สร้าง placeholders และตรวจสอบค่า null
    $placeholders = [];

    foreach ($data as $key => $value) {
        if ($value === null) {
            $placeholders[] = " 'NULL' ";
        } else {
            $placeholders[] = " '$value' ";
        }
    }

    // ใช้ implode เพื่อรวม placeholders
    $placeholders = implode(", ", $placeholders);

    // สร้างคำสั่ง SQL
    $sql = "INSERT INTO $tableName ($columns) VALUES ($placeholders);";

    return $sql;
}


function arrayToUpdateSQL($tableName, $data, $where)
{
    // ตรวจสอบข้อมูลเบื้องต้น
    if (empty($tableName) || empty($data) || !is_array($data) || empty($where)) {
        throw new InvalidArgumentException("Invalid table name, data, or where clause.");
    }

    // สร้างส่วนของคอลัมน์สำหรับการอัปเดต
    $setClause = implode(", ", array_map(fn($key) => "$key = :$key", array_keys($data)));

    // สร้างคำสั่ง SQL
    $sql = "UPDATE $tableName SET $setClause WHERE $where;";

    return $sql;
}

function getTimeRangeOptions($timeRange)
{
    // กำหนดอาร์เรย์ของช่วงเวลา
    $time_range_arr = [
        1 => 1 * 24 * 60 * 60,   // 1 วัน
        2 => 7 * 24 * 60 * 60,   // 1 สัปดาห์
        3 => 30 * 24 * 60 * 60,  // 1 เดือน (ประมาณ 30 วัน)
        4 => 90 * 24 * 60 * 60,  // 3 เดือน (ประมาณ 90 วัน)
        5 => 180 * 24 * 60 * 60, // 6 เดือน (ประมาณ 180 วัน)
        6 => 365 * 24 * 60 * 60  // 1 ปี (365 วัน)
    ];

    // ตรวจสอบว่า timeRange ที่ส่งมาเป็นค่าที่ถูกต้อง
    if (!array_key_exists($timeRange, $time_range_arr)) {
        throw new InvalidArgumentException("Invalid time range selected.");
    }
    $expiryTime = time() + $time_range_arr[$timeRange];

    // คำนวณเวลา expiryTime ตามช่วงเวลา
    return [
        'expire_time' => $expiryTime,
        'expire_datetime' => date("Y-m-d H:i:s", $expiryTime),
    ];
}

function setLongTermCookie($name, $value, $timeRange)
{

    $expiryTime = getTimeRangeOptions($timeRange);

    // ตั้งค่า Cookie
    setcookie($name, $value, $expiryTime['expire_time'], "/", "", true, true);

    // ส่งคืนวันที่หมดอายุในรูปแบบที่ต้องการ
    return $expiryTime['expire_datetime'];
}

function clearAllCookies()
{
    // วนลูปลบ Cookie ทั้งหมด
    foreach ($_COOKIE as $name => $value) {
        // ตั้งค่าเวลาหมดอายุของ Cookie เป็นอดีต
        setcookie($name, '', time() - 3600, '/');
        unset($_COOKIE[$name]); // ลบจากตัวแปร $_COOKIE
    }
}

function getCurrentFiscalYear()
{
    // รับค่าเดือนและปีปัจจุบัน
    $currentMonth = (int)date('m'); // เดือน (01-12)
    $currentYear = (int)date('Y');  // ปีปัจจุบัน

    // หากเดือนปัจจุบันน้อยกว่า 10 (ก่อนตุลาคม) ปีงบประมาณจะเป็นปีปัจจุบัน - 1
    if ($currentMonth > 10) {
        return $currentYear + 1;
    }

    // หากเป็นเดือนตุลาคมถึงธันวาคม ปีงบประมาณจะเป็นปีปัจจุบัน
    return (int)$currentYear;
}

function getAvatarImage($imageBlob)
{
    // image convert
    $act_img = "";
    if ($imageBlob == null || strlen($imageBlob) == 0) {
        $act_img = './assets/medias/avatars/blank.png';
    } else {
        $act_img_blob = $imageBlob;
        $act_img_info = finfo_open(FILEINFO_MIME_TYPE);
        $act_img_mime_type = finfo_buffer($act_img_info, $act_img_blob);
        finfo_close($act_img_info);
        $act_img_base64 = base64_encode($act_img_blob); // เข้ารหัส Base64
        $act_img = "data:" . $act_img_mime_type . ";base64," . $act_img_base64;
    }

    return $act_img;
}
