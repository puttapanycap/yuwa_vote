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

function arrayToInsertSQL($tableName, $data, $type = 'single')
{
    // ตรวจสอบว่าข้อมูลไม่ว่างเปล่า
    if (empty($tableName) || empty($data) || !is_array($data)) {
        return "Not Response";
    }

    // สร้างรายการคอลัมน์และค่าจาก Array
    $columns = "";

    // สร้าง placeholders และตรวจสอบค่า null
    $placeholders = [];
    $placeholders_final = "";

    // Single
    if ($type == 'single') :
        $columns = implode(", ", array_keys($data));
        foreach ($data as $key => $value) {
            if ($value === null) {
                $placeholders[] = " NULL ";
            } else {
                $placeholders[] = " '$value' ";
            }
        }
        // ใช้ implode เพื่อรวม placeholders
        $placeholders_final = "(" . implode(", ", $placeholders) . ")";


    // Multiple
    elseif ($type == 'multi') :
        foreach ($data as $key => $datas) {
            $columns = implode(", ", array_keys($datas));
            $values = [];
            foreach ($datas as $value) {
                if ($value === null) {
                    $values[] = " NULL ";
                } else {
                    $values[] = " '$value' ";
                }
            }
            $placeholders[] = "(" . implode(", ", $values) . ")";
        }
        $placeholders_final = implode(", ", $placeholders);
    endif;

    // สร้างคำสั่ง SQL
    $sql = "INSERT INTO $tableName ($columns) VALUES $placeholders_final;";

    return $sql;
}

function arrayToUpdateSQL($tableName, $data, $where = [])
{
    // ตรวจสอบข้อมูลเบื้องต้น
    if (empty($tableName) || empty($data) || !is_array($data) || empty($where)) {
        throw new InvalidArgumentException("Invalid table name, data, or where clause.");
    }

    $set_sql = "";
    $set_arr = [];
    foreach ($data as $key => $value) {
        
        if ($value === null) {
            $set_arr[] = " $key = NULL ";
        } else {
            $set_arr[] = " $key = '$value' ";
        }
    }
    count($set_arr) > 0 ? $set_sql = implode(", ", $set_arr) : false;

    $where_sql = "";
    if (count($where) > 0) {
        $where_arr = [];
        foreach ($where as $key => $value) {
            if ($value === null) {
                $where_arr[] = " $key = NULL ";
            } else {
                $where_arr[] = " $key = '$value' ";
            }
        }
        $where_sql = " WHERE " . implode(' AND ', $where_arr);
    }

    // สร้างคำสั่ง SQL
    $sql = "UPDATE $tableName SET $set_sql $where_sql;";

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

function generateTopicKey($length = 16) {
    // Generate random bytes and convert to hexadecimal
    return bin2hex(random_bytes($length / 2));
}

function setRememberKeyCookie($key, $value, $expiry = 2592000, $secure = true, $httpOnly = true) {
    // Set cookie parameters
    $cookieOptions = [
        'expires' => time() + $expiry, // Current time + expiry duration
        'path' => '/',                 // Available across the entire domain
        'domain' => '',                // Default to the current domain
        'secure' => $secure,           // Send only over HTTPS
        'httponly' => $httpOnly,       // Accessible only via HTTP, not JavaScript
        'samesite' => 'Strict',        // Prevent cross-site requests
    ];

    // Set the cookie
    setcookie($key, $value, $cookieOptions);
}

function getRememberKeyCookie($key) {
    if (isset($_COOKIE[$key])) {
        // Return the sanitized cookie value
        return htmlspecialchars($_COOKIE[$key]);
    }
    return null;
}