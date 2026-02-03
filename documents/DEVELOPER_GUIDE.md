# 👨‍💻 คู่มือนักพัฒนา - YUWA Vote

## สารบัญ

1. [โครงสร้างโปรเจกต์](#1-โครงสร้างโปรเจกต์)
2. [สถาปัตยกรรมระบบ](#2-สถาปัตยกรรมระบบ)
3. [การตั้งค่าสภาพแวดล้อม](#3-การตั้งค่าสภาพแวดล้อม)
4. [ฐานข้อมูล](#4-ฐานข้อมูล)
5. [Helper Functions](#5-helper-functions)
6. [API Endpoints (Actions)](#6-api-endpoints-actions)
7. [Components](#7-components)
8. [การป้องกันความปลอดภัย](#8-การป้องกันความปลอดภัย)
9. [การปรับแต่งและขยายระบบ](#9-การปรับแต่งและขยายระบบ)
10. [แนวทางการพัฒนา](#10-แนวทางการพัฒนา)

---

## 1. โครงสร้างโปรเจกต์

```
yuwa_vote/
├── .env                    # ไฟล์ตัวแปรสภาพแวดล้อม
├── .gitignore              # ไฟล์ที่ Git จะไม่ติดตาม
├── composer.json           # Dependencies ของ PHP
├── index.php               # หน้าหลัก (Dashboard)
├── login.php               # หน้าเข้าสู่ระบบ
├── register.php            # หน้าลงทะเบียน
├── vote.php                # หน้าลงคะแนน
├── monitor.php             # หน้าดูผล Real-time
│
├── actions/                # API Endpoints
│   ├── auth_login.php          # ตรวจสอบการล็อกอิน
│   ├── register.php            # บันทึกการลงทะเบียน
│   ├── logout.php              # ออกจากระบบ
│   ├── topics_add.php          # ฟอร์มเพิ่มหัวข้อ
│   ├── topics_insert.php       # บันทึกหัวข้อใหม่
│   ├── topics_edit.php         # ฟอร์มแก้ไขหัวข้อ
│   ├── topics_update.php       # อัปเดตหัวข้อ
│   ├── topics_delete.php       # ลบหัวข้อ
│   ├── topics_datatable.php    # ดึงข้อมูลสำหรับ DataTable
│   ├── topics_qrcode.php       # สร้าง QR Code
│   ├── topics_toggle_score.php # สลับการแสดงคะแนน
│   ├── choices_delete.php      # ลบตัวเลือก
│   ├── choics_realtime.php     # ดึงข้อมูลผล Real-time
│   └── vote.php                # บันทึกการโหวต
│
├── assets/                 # ไฟล์ Static
│   ├── css/                    # Stylesheets
│   ├── js/                     # JavaScript
│   ├── medias/                 # รูปภาพ, โลโก้
│   └── plugins/                # Third-party plugins
│
├── components/             # PHP Components
│   ├── head.html               # Meta tags และ CSS
│   ├── script.html             # JavaScript imports
│   ├── navbar.php              # Navigation bar
│   └── footer.php              # Footer
│
├── css/                    # Custom Stylesheets
│   └── style.css               # Custom styles
│
├── documents/              # เอกสารประกอบ
│   ├── USER_GUIDE.md           # คู่มือผู้ใช้
│   ├── DEVELOPER_GUIDE.md      # คู่มือนักพัฒนา
│   └── CHANGELOG.md            # บันทึกการเปลี่ยนแปลง
│
├── ErrorPages/             # หน้า Error
│   ├── 403.php
│   ├── 404.php
│   ├── 500.php
│   └── ...
│
├── helpers/                # Helper Functions
│   ├── functions.php           # ฟังก์ชันทั่วไป
│   └── load_env.php            # โหลดตัวแปรสภาพแวดล้อม
│
├── installer/              # ไฟล์สำหรับติดตั้ง
│   └── yuwa-vote.sql           # SQL Schema
│
└── vendor/                 # Composer Dependencies
```

---

## 2. สถาปัตยกรรมระบบ

### 2.1 ภาพรวม

```
┌─────────────────────────────────────────────────────────────┐
│                         Frontend                             │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐    │
│  │ index.php│  │ vote.php │  │monitor.php│ │ login.php│    │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘    │
│       │             │             │             │           │
│       └─────────────┴──────┬──────┴─────────────┘           │
│                            │                                │
│                     ┌──────▼──────┐                         │
│                     │  Components │                         │
│                     │ (navbar.php,│                         │
│                     │  footer.php)│                         │
│                     └──────┬──────┘                         │
└────────────────────────────┼────────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────────┐
│                         Backend                              │
│  ┌──────────────────────────────────────────────────────┐   │
│  │                    actions/*.php                      │   │
│  │  (API Endpoints - AJAX handlers)                      │   │
│  └────────────────────────┬─────────────────────────────┘   │
│                           │                                  │
│  ┌────────────────────────▼─────────────────────────────┐   │
│  │                 helpers/functions.php                 │   │
│  │  (Database connections, utilities, helpers)           │   │
│  └────────────────────────┬─────────────────────────────┘   │
└────────────────────────────┼────────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────────┐
│                        Database                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐       │
│  │ vote_topics  │  │ vote_choices │  │ vote_results │       │
│  └──────────────┘  └──────────────┘  └──────────────┘       │
│  ┌──────────────┐                                           │
│  │ vote_members │                                           │
│  └──────────────┘                                           │
└─────────────────────────────────────────────────────────────┘
```

### 2.2 Technology Stack

| Layer | Technology |
|-------|-----------|
| **Frontend** | HTML5, CSS3, JavaScript (jQuery) |
| **CSS Framework** | Bootstrap 5, Metronic Theme |
| **Backend** | PHP 7.4+ |
| **Database** | MySQL / MariaDB |
| **Dependencies** | vlucas/phpdotenv, chillerlan/php-qrcode |

---

## 3. การตั้งค่าสภาพแวดล้อม

### 3.1 ไฟล์ `.env`

```env
# Database Configuration
VOTE_DB_HOST=localhost
VOTE_DB_USER=root
VOTE_DB_PASS=secret
VOTE_DB_NAME=yuwa_vote

# Application URL
VOTE_WEB_URL=http://localhost/yuwa_vote/
```

### 3.2 การโหลด Environment Variables

ระบบใช้ `vlucas/phpdotenv` สำหรับโหลดตัวแปร:

```php
// helpers/load_env.php
require _WEBROOT_PATH_ . '/vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(_WEBROOT_PATH_);
$dotenv->load();
```

### 3.3 การเข้าถึงตัวแปร

```php
$host = $_ENV['VOTE_DB_HOST'];
$webUrl = $_ENV['VOTE_WEB_URL'];
```

---

## 4. ฐานข้อมูล

### 4.1 Entity Relationship Diagram (ERD)

```
┌──────────────────┐       ┌──────────────────┐
│   vote_members   │       │   vote_topics    │
├──────────────────┤       ├──────────────────┤
│ id (PK)          │       │ id (PK)          │
│ member_name      │───┐   │ topic_title      │
│ member_username  │   │   │ expire_datetime  │
│ member_password  │   └──▶│ member_id (FK)   │
│ member_email     │       │ share_key        │
│ create_at        │       │ show_score       │
└──────────────────┘       │ display_mode     │
                           │ is_public        │
                           └────────┬─────────┘
                                    │
              ┌─────────────────────┴─────────────────────┐
              │                                           │
              ▼                                           ▼
┌──────────────────┐                        ┌──────────────────┐
│  vote_choices    │                        │  vote_results    │
├──────────────────┤                        ├──────────────────┤
│ id (PK)          │◀──────────────────────▶│ id (PK)          │
│ topic_id (FK)    │                        │ topic_id (FK)    │
│ choice_title     │                        │ choice_id (FK)   │
│ choice_sort      │                        │ timestamp        │
└──────────────────┘                        │ ipaddress        │
                                            │ cookie_key       │
                                            └──────────────────┘
```

### 4.2 ตารางและฟิลด์

#### `vote_members` - ตารางผู้ใช้

| Field | Type | Description |
|-------|------|-------------|
| `id` | INT(11) AUTO_INCREMENT | Primary Key |
| `member_name` | VARCHAR(255) | ชื่อแสดง |
| `member_username` | VARCHAR(255) | ชื่อผู้ใช้สำหรับล็อกอิน |
| `member_password` | VARCHAR(255) | รหัสผ่าน (bcrypt hash) |
| `member_email` | VARCHAR(255) | อีเมล |
| `create_at` | TIMESTAMP | วันที่สร้าง |

#### `vote_topics` - ตารางหัวข้อโหวต

| Field | Type | Description |
|-------|------|-------------|
| `id` | INT(11) AUTO_INCREMENT | Primary Key |
| `topic_title` | VARCHAR(255) | ชื่อหัวข้อ |
| `expire_datetime` | DATETIME | วันหมดอายุ |
| `member_id` | INT(11) | Foreign Key → vote_members |
| `share_key` | VARCHAR(255) | Key สำหรับแชร์ |
| `session_key` | VARCHAR(64) | Session Key สำหรับ Workspace |
| `show_score` | TINYINT(1) | แสดงคะแนน (0/1) |
| `display_mode` | VARCHAR(20) | โหมดแสดงผล (card/list) |
| `is_public` | TINYINT(1) | สาธารณะ (0/1) |
| `vote_mode` | VARCHAR(20) | โหมดการโหวต (single/multiple) |
| `max_choices` | INT(11) | จำนวนตัวเลือกสูงสุดที่เลือกได้ |

#### `vote_choices` - ตารางตัวเลือก

| Field | Type | Description |
|-------|------|-------------|
| `id` | INT(11) AUTO_INCREMENT | Primary Key |
| `topic_id` | INT(11) | Foreign Key → vote_topics |
| `choice_title` | VARCHAR(255) | ชื่อตัวเลือก |
| `choice_sort` | INT(11) | ลำดับการแสดงผล |

#### `vote_results` - ตารางผลโหวต

| Field | Type | Description |
|-------|------|-------------|
| `id` | INT(11) AUTO_INCREMENT | Primary Key |
| `topic_id` | INT(11) | Foreign Key → vote_topics |
| `choice_id` | INT(11) | Foreign Key → vote_choices |
| `timestamp` | DATETIME | เวลาที่โหวต |
| `ipaddress` | VARCHAR(255) | IP Address ผู้โหวต |
| `cookie_key` | VARCHAR(255) | Cookie Key ผู้โหวต |

---

## 5. Helper Functions

### 5.1 Database Connection

```php
function getDatabaseConnections(): array
{
    $vote_host = $_ENV['VOTE_DB_HOST'];
    $vote_user = $_ENV['VOTE_DB_USER'];
    $vote_pass = $_ENV['VOTE_DB_PASS'];
    $vote_db = $_ENV['VOTE_DB_NAME'];

    $pdo_vote = new mysqli($vote_host, $vote_user, $vote_pass, $vote_db);
    $pdo_vote->set_charset("utf8");

    return ['vote' => $pdo_vote];
}
```

### 5.2 SQL Builder Functions

#### Insert Builder

```php
// Single Row Insert
$sql = arrayToInsertSQL('vote_topics', [
    'topic_title' => 'My Topic',
    'expire_datetime' => '2026-12-31 23:59:59'
], 'single');

// Multiple Rows Insert
$data = [
    ['choice_title' => 'Option A', 'topic_id' => 1],
    ['choice_title' => 'Option B', 'topic_id' => 1]
];
$sql = arrayToInsertSQL('vote_choices', $data, 'multi');
```

#### Update Builder

```php
$sql = arrayToUpdateSQL('vote_topics', 
    ['topic_title' => 'Updated Title'],  // SET
    ['id' => 1]                          // WHERE
);
```

### 5.3 Client Information

```php
$clientInfo = getClientInfo();
// Returns: ip_address, user_agent, server_name, etc.
```

### 5.4 Key Generation

```php
// Session Key (32 bytes hex)
$sessionKey = generateSessionKey();

// Remember Key (64 bytes base64)
$rememberKey = generateRememberKey();

// Topic Share Key (16 chars hex)
$topicKey = generateTopicKey(16);
```

### 5.5 Cookie Management

```php
// Set Remember Key Cookie (30 days default)
setRememberKeyCookie('vote_remember', $value);

// Get Remember Key Cookie
$value = getRememberKeyCookie('vote_remember');

// Clear All Cookies
clearAllCookies();
```

---

## 6. API Endpoints (Actions)

### 6.1 Authentication

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/actions/auth_login.php` | POST | ตรวจสอบล็อกอิน |
| `/actions/register.php` | POST | บันทึกการลงทะเบียน |
| `/actions/logout.php` | GET | ออกจากระบบ |

#### Login Request

```javascript
$.ajax({
    type: 'POST',
    url: './actions/auth_login.php',
    data: {
        input_username: 'admin',
        input_password: '1234'
    },
    dataType: 'JSON',
    success: function(response) {
        if (response.status == 'success') {
            window.location.href = './';
        }
    }
});
```

### 6.2 Topics Management

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/actions/topics_add.php` | GET | ดึงฟอร์มสร้างหัวข้อ |
| `/actions/topics_insert.php` | POST | บันทึกหัวข้อใหม่ |
| `/actions/topics_edit.php` | POST | ดึงฟอร์มแก้ไข |
| `/actions/topics_update.php` | POST | อัปเดตหัวข้อ |
| `/actions/topics_delete.php` | POST | ลบหัวข้อ |
| `/actions/topics_datatable.php` | GET | ดึงข้อมูล DataTable |
| `/actions/topics_qrcode.php` | POST | สร้าง QR Code |
| `/actions/topics_toggle_score.php` | POST | สลับการแสดงคะแนน |

### 6.3 Voting

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/actions/vote.php` | POST | บันทึกการโหวต |
| `/actions/choics_realtime.php` | POST | ดึงข้อมูล Real-time |
| `/actions/choices_delete.php` | POST | ลบตัวเลือก |

---

## 7. Components

### 7.1 Head Component (`components/head.html`)

รวม meta tags, favicon, และ CSS ทั้งหมด:

```html
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="assets/plugins/global/plugins.bundle.css">
<link rel="stylesheet" href="assets/css/style.bundle.css">
```

### 7.2 Script Component (`components/script.html`)

รวม JavaScript libraries:

```html
<script src="assets/plugins/global/plugins.bundle.js"></script>
<script src="assets/js/scripts.bundle.js"></script>
```

### 7.3 Navbar Component (`components/navbar.php`)

Navigation bar สำหรับผู้ใช้ที่ล็อกอินแล้ว

### 7.4 Footer Component (`components/footer.php`)

Footer และ JavaScript ส่วนล่าง

---

## 8. การป้องกันความปลอดภัย

### 8.1 Password Hashing

ระบบใช้ `bcrypt` สำหรับ hash รหัสผ่าน:

```php
// Hash password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Verify password
if (password_verify($inputPassword, $hashedPassword)) {
    // Password is correct
}
```

### 8.2 Session Management

```php
session_start();

// Set session
$_SESSION['user_id'] = $userId;
$_SESSION['user_name'] = $userName;

// Check session
if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit(0);
}
```

### 8.3 Vote Duplication Prevention

ป้องกันการโหวตซ้ำด้วย IP และ Cookie:

```php
$check_sql = "SELECT id FROM vote_results 
              WHERE topic_id='$topic_id' 
              AND (ipaddress='$IPAddress' OR cookie_key='$rememberKey')";
```

### 8.4 XSS Prevention

```php
// Escape output
echo htmlspecialchars($userInput);
```

---

## 9. การปรับแต่งและขยายระบบ

### 9.1 เพิ่ม Field ใหม่ในหัวข้อ

1. **แก้ไข SQL Schema:**
```sql
ALTER TABLE vote_topics ADD COLUMN new_field VARCHAR(255);
```

2. **แก้ไข Form:**
   - `actions/topics_add.php`
   - `actions/topics_edit.php`

3. **แก้ไข Insert/Update:**
   - `actions/topics_insert.php`
   - `actions/topics_update.php`

### 9.2 เพิ่ม Display Mode ใหม่

1. **แก้ไข `actions/choics_realtime.php`:**
```php
switch ($display_mode) {
    case 'card':
        // Card view HTML
        break;
    case 'list':
        // List view HTML
        break;
    case 'new_mode':
        // New mode HTML
        break;
}
```

2. **เพิ่ม Option ใน Form:**
```html
<option value="new_mode">New Mode</option>
```

### 9.3 Theming

แก้ไขไฟล์ CSS ที่:
- `css/style.css` - Custom styles
- `assets/css/style.bundle.css` - Main theme

---

## 10. แนวทางการพัฒนา

### 10.1 Coding Standards

- ใช้ **PSR-12** coding style สำหรับ PHP
- ใช้ **camelCase** สำหรับ JavaScript variables/functions
- ใช้ **snake_case** สำหรับ Database fields

### 10.2 Git Workflow

```bash
# Create feature branch
git checkout -b feature/new-feature

# Make changes and commit
git add .
git commit -m "feat: add new feature"

# Push to remote
git push origin feature/new-feature
```

### 10.3 Commit Message Format

```
<type>: <description>

Types:
- feat: New feature
- fix: Bug fix
- docs: Documentation
- style: Code style changes
- refactor: Code refactoring
- test: Tests
- chore: Maintenance
```

### 10.4 Testing Checklist

- [ ] ทดสอบการสร้างหัวข้อใหม่
- [ ] ทดสอบการแก้ไขหัวข้อ
- [ ] ทดสอบการลบหัวข้อ
- [ ] ทดสอบการโหวต
- [ ] ทดสอบการ Real-time update
- [ ] ทดสอบการป้องกันโหวตซ้ำ
- [ ] ทดสอบบนอุปกรณ์ต่างๆ (Responsive)

---

## 📞 ติดต่อทีมพัฒนา

- **พัฒนาโดย**: YUWA IT Team

---

*เอกสารนี้ปรับปรุงล่าสุด: กุมภาพันธ์ 2026*
