<?php

session_start();
define('_WEBROOT_PATH_', './');

if (!isset($_GET['key'])) {
    header('location: ' . _WEBROOT_PATH_);
    exit(0);
}

// Connection Setup :: START
require _WEBROOT_PATH_ . '/helpers/load_env.php';
require _WEBROOT_PATH_ . '/helpers/functions.php';
$connections = getDatabaseConnections();
$vote_conn = $connections['vote'];
// Connection Setup :: END

$topic_key = $_GET['key'];
$topic_sql = "  SELECT
                    id,
                    topic_title,
                    expire_datetime,
                    member_id,
                    share_key
                FROM
                    vote_topics 
				WHERE
					share_key='$topic_key' ";
$topic_query = $vote_conn->query($topic_sql);
$topic_info = $topic_query->fetch_assoc();
$topic_id = $topic_info['id'];
$topic_title = $topic_info['topic_title'];

$topic_expire = new DateTime($topic_info['expire_datetime']);
$today = new DateTime(date("Y-m-d H:i:s"));
$isExpire = $today > $topic_expire ? true : false;

// Cookie Setup
$cookieName = 'remember_key';
$rememberKey = getRememberKeyCookie($cookieName);
$client_info = getClientInfo();
$IPAddress = explode(', ', $client_info['ip_address']);
$IPAddress = $IPAddress[0];
if (!$rememberKey) {
    $newRememberKey = hash('sha256', uniqid('user_', true));
    setRememberKeyCookie($cookieName, $newRememberKey);
    $rememberKey = $newRememberKey;
}

$check_sql = "SELECT id FROM vote_results WHERE topic_id='$topic_id' AND (ipaddress='$IPAddress' OR cookie_key='$rememberKey') ";
$check_query = $vote_conn->query($check_sql);
$vote_count = $check_query->num_rows;

$choices_sql = "SELECT
                    id,
                    topic_id,
                    choice_title,
                    choice_sort
				FROM
					vote_choices
				WHERE
					topic_id='$topic_id'
                ORDER BY
					choice_sort ASC";
$choices_query = $vote_conn->query($choices_sql);
$choices = [];
while ($choice_row = $choices_query->fetch_assoc()) {
    $choices[] = $choice_row;
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <?php require _WEBROOT_PATH_ . 'components/head.html'; ?>
    <?php require _WEBROOT_PATH_ . 'components/script.html'; ?>
</head>

<body>
    <!--begin::Theme mode setup on page load-->
    <script>
        document.documentElement.setAttribute("data-bs-theme", "light");
    </script>

    <div class="container flex-column-fluid my-4">

        <div class="w-100 card p-4 d-flex flex-column flex-center mb-4">
            <span class="fs-3 fw-bold text-center me-4">ลงคะแนน</span>
            <span class="fs-1 fw-bold text-center me-4"><?php echo $topic_title ?></span>
        </div>

        <div class="card d-flex flex-column flex-wrap flex-center gap-4 shadow-sm p-4" id="topic_votes">


            <?php if ($isExpire) : ?>
                <div class="d-flex flex-column flex-wrap flex-center">
                    <span class="h-60px w-60px rounded-circle d-flex flex-center bg-secondary text-danger">
                        <i class="fa-solid fa-xmark-large fs-2hx"></i>
                    </span>
                    <span class="fs-3 text-gray-700">หมดเวลาลงคะแนนแล้ว</span>
                </div>
            <?php else: ?>
                <?php if ($vote_count > 0) : ?>
                    <div class="d-flex flex-column flex-wrap flex-center">
                        <span class="h-60px w-60px rounded-circle d-flex flex-center bg-secondary text-success">
                            <i class="fa-solid fa-check fs-2hx"></i>
                        </span>
                        <span class="fs-3 text-gray-700">ท่านลงคะแนนแล้ว</span>
                    </div>
                <?php else: ?>
                    <div class="fs-2x fw-bold">เลือกลงคะแนน</div>
                    <div id="countdown"></div>
                    <div class="d-flex flex-row flex-wrap flex-center gap-4">

                        <?php
                        $i = 0;
                        foreach ($choices as $cc) :
                        ?>
                            <input type="radio" class="btn-check" name="choice_for_vote" value="<?php echo $cc['id'] ?>" <?php echo $i == 0 ? 'checked' : ''; ?> id="choice_<?php echo $cc['id'] ?>" />
                            <label class="btn btn-outline btn-outline-dashed btn-active-light-primary p-4 d-flex align-items-center shadow-sm" for="choice_<?php echo $cc['id'] ?>">
                                <div class="d-flex flex-column flex-center gap-2 p-4">
                                    <i class="ki-solid ki-star text-warning fs-4x"></i>
                                    <span class="d-block fw-semibold text-start">
                                        <span class="text-gray-900 fw-bold d-block fs-3"><?php echo $cc['choice_title'] ?></span>
                                    </span>
                                </div>
                            </label>
                        <?php
                            $i++;
                        endforeach;
                        ?>

                    </div>
                    <button class="btn btn-success" onclick="sendVote()">ส่งผลโหวต</button>
                <?php endif; ?>
            <?php endif; ?>

        </div>

    </div>


    <?php require _WEBROOT_PATH_ . 'components/footer.php'; ?>

    <script>
        countdown('<?php echo $topic_info['expire_datetime'] ?>');

        function sendVote() {
            Swal.fire({
                text: `ยืนยันการโหวต`,
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "ตกลง",
                cancelButtonText: "ยกเลิก",
                customClass: {
                    confirmButton: "btn fw-bold btn-danger",
                    cancelButton: "btn fw-bold btn-active-light-primary"
                }
            }).then(function(result) {
                if (result.value) {
                    $.ajax({
                        url: './actions/vote.php',
                        type: 'POST',
                        data: {
                            topic_id: '<?php echo $topic_id ?>',
                            choice_id: $('input[name="choice_for_vote"]:checked').val(),
                        },
                        dataType: 'JSON',
                        success: function(result) {
                            if (result.status === 'success') {
                                Swal.fire({
                                    text: `ส่งผล Vote สำเร็จ`,
                                    icon: "success",
                                    buttonsStyling: false,
                                    showConfirmButton: false,
                                    timer: 1000
                                }).then(function() {
                                    location.reload();
                                });
                            } else if (result.status === 0) {
                                Swal.fire({
                                    text: result.message,
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "ตกลง",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                });
                            } else {
                                Swal.fire({
                                    text: 'ระบบขัดข้องส่งผลโหวตไม่สำเร็จ',
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "ตกลง",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                });
                            }
                        }
                    });

                }
            });

        }

        function countdown(targetDateTime) {
            const targetDate = new Date(targetDateTime).getTime();
            let intervalId = null;

            function updateCountdown() {
                const now = new Date().getTime();
                const timeRemaining = targetDate - now;

                if (timeRemaining <= 0) {
                    // หากถึงเวลาที่กำหนดให้รีเฟรชหน้า
                    clearInterval(intervalId);
                    intervalId = null;
                    // $('#topic_votes').html(`<div class="d-flex flex-column flex-wrap flex-center">
                    //                 <span class="h-60px w-60px rounded-circle d-flex flex-center bg-secondary text-danger">
                    //                     <i class="fa-solid fa-xmark-large fs-2hx"></i>
                    //                 </span>
                    //                 <span class="fs-3 text-gray-700">หมดเวลาลงคะแนนแล้ว</span>
                    //             </div>`);
                } else {
                    // คำนวณเวลา
                    const days = Math.floor(timeRemaining / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((timeRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);

                    // แสดงผลเวลา (อัพเดตใน HTML ถ้ามี)
                    document.getElementById("countdown").innerHTML = `
                        <div class="d-flex flex-row flex-wrap gap-2 align-items-center">
                            <div class="d-flex flex-column flex-center bg-dark h-80px w-80px rounded-circle text-inverse-dark">
                                <span class="lh-1 fs-2hx">${hours}</span>
                                <span class="lh-1">ชม.</span>
                            </div>
                            <div class="d-flex flex-column flex-center bg-dark h-80px w-80px rounded-circle text-inverse-dark">
                                <span class="lh-1 fs-2hx">${minutes}</span>
                                <span class="lh-1">นาที</span>
                            </div>
                            <div class="d-flex flex-column flex-center bg-dark h-80px w-80px rounded-circle text-inverse-dark">
                                <span class="lh-1 fs-2hx">${seconds}</span>
                                <span class="lh-1">วินาที</span>
                            </div>
                        </div>
                    `;

                    // เรียกฟังก์ชันนี้ซ้ำทุก 1 วินาที
                    intervalId = setTimeout(updateCountdown, 1000);
                }
            }

            updateCountdown();
        }
    </script>
</body>

</html>