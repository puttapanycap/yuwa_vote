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
    <style>
        /* Modern Vote Page Styles */
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .vote-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.25);
            padding: 2rem;
            color: white;
            text-align: center;
        }

        .vote-header .subtitle {
            opacity: 0.9;
            font-size: 1.2rem;
        }

        .vote-header .title {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .vote-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            transition: all 0.3s ease;
        }

        /* Status Icons */
        .status-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .status-icon.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            box-shadow: 0 10px 30px rgba(17, 153, 142, 0.3);
        }

        .status-icon.error {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            box-shadow: 0 10px 30px rgba(235, 51, 73, 0.3);
        }

        /* Countdown Timer */
        .countdown-container {
            margin: 1.5rem 0;
        }

        .countdown-item {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(145deg, #1a1a2e 0%, #16213e 100%);
            box-shadow: 0 8px 25px rgba(26, 26, 46, 0.3);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.3s ease;
        }

        .countdown-item:hover {
            transform: scale(1.1);
        }

        .countdown-item .number {
            font-size: 2rem;
            font-weight: bold;
            line-height: 1;
            background: linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .countdown-item .label {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        /* Choice Cards */
        .choice-label {
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .choice-label:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .choice-label .choice-inner {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            border: 2px solid transparent;
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }

        .btn-check:checked+.choice-label .choice-inner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: transparent;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .btn-check:checked+.choice-label .choice-inner .star-icon {
            color: #ffd700;
            filter: drop-shadow(0 0 10px rgba(255, 215, 0, 0.5));
        }

        .star-icon {
            color: #ffc107;
            transition: all 0.3s ease;
        }

        .choice-label:hover .star-icon {
            transform: scale(1.2) rotate(20deg);
        }

        /* Submit Button */
        .submit-btn {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            border-radius: 50px;
            padding: 1rem 3rem;
            font-size: 1.2rem;
            font-weight: bold;
            color: white;
            box-shadow: 0 10px 30px rgba(17, 153, 142, 0.3);
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(17, 153, 142, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Responsive */
        @media (max-width: 576px) {
            .countdown-item {
                width: 70px;
                height: 70px;
            }

            .countdown-item .number {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <!--begin::Theme mode setup on page load-->
    <script>
        document.documentElement.setAttribute("data-bs-theme", "light");
    </script>

    <div class="container flex-column-fluid my-4">

        <!-- Enhanced Header -->
        <div class="w-100 vote-header mb-4">
            <span class="subtitle d-block mb-2">ลงคะแนน</span>
            <span class="fs-1 fw-bold title d-block"><?php echo htmlspecialchars($topic_title) ?></span>
        </div>

        <!-- Vote Card -->
        <div class="vote-card" id="topic_votes">

            <?php if ($isExpire): ?>
                <!-- Expired State -->
                <div class="d-flex flex-column align-items-center py-4">
                    <div class="status-icon error">
                        <i class="fa-solid fa-xmark fs-2hx text-white"></i>
                    </div>
                    <span class="fs-2 fw-bold text-gray-800 mb-2">หมดเวลาลงคะแนนแล้ว</span>
                    <span class="text-gray-500">ขอบคุณสำหรับความสนใจ</span>
                </div>
            <?php else: ?>
                <?php if ($vote_count > 0): ?>
                    <!-- Already Voted State -->
                    <div class="d-flex flex-column align-items-center py-4">
                        <div class="status-icon success">
                            <i class="fa-solid fa-check fs-2hx text-white"></i>
                        </div>
                        <span class="fs-2 fw-bold text-gray-800 mb-2">ท่านลงคะแนนแล้ว</span>
                        <span class="text-gray-500">ขอบคุณสำหรับการมีส่วนร่วม</span>
                    </div>
                <?php else: ?>
                    <!-- Voting Interface -->
                    <div class="text-center mb-4">
                        <div class="fs-2x fw-bold text-gray-800 mb-2">เลือกลงคะแนน</div>
                        <div class="countdown-container" id="countdown"></div>
                    </div>

                    <div class="d-flex flex-row flex-wrap justify-content-center gap-3 mb-4">
                        <?php
                        $i = 0;
                        foreach ($choices as $cc):
                            ?>
                            <input type="radio" class="btn-check" name="choice_for_vote" value="<?php echo $cc['id'] ?>" <?php echo $i == 0 ? 'checked' : ''; ?> id="choice_<?php echo $cc['id'] ?>" />
                            <label class="choice-label" for="choice_<?php echo $cc['id'] ?>">
                                <div class="choice-inner d-flex flex-column align-items-center gap-2">
                                    <i class="ki-solid ki-star fs-3x star-icon"></i>
                                    <span class="fw-bold fs-4"><?php echo htmlspecialchars($cc['choice_title']) ?></span>
                                </div>
                            </label>
                            <?php
                            $i++;
                        endforeach;
                        ?>
                    </div>

                    <div class="text-center">
                        <button class="submit-btn" onclick="sendVote()">
                            <i class="fa-solid fa-paper-plane me-2"></i>
                            ส่งผลโหวต
                        </button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>

    </div>


    <?php require _WEBROOT_PATH_ . 'components/footer.php'; ?>

    <script>
        countdown('<?php echo $topic_info['expire_datetime'] ?>');

        function sendVote() {
            Swal.fire({
                title: 'ยืนยันการโหวต',
                text: "คุณต้องการส่งผลโหวตใช่หรือไม่",
                icon: "question",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: '<i class="fa-solid fa-check me-2"></i>ยืนยัน',
                cancelButtonText: "ยกเลิก",
                customClass: {
                    confirmButton: "btn fw-bold btn-success mx-2",
                    cancelButton: "btn fw-bold btn-secondary mx-2"
                }
            }).then(function (result) {
                if (result.value) {
                    $.ajax({
                        url: './actions/vote.php',
                        type: 'POST',
                        data: {
                            topic_id: '<?php echo $topic_id ?>',
                            choice_id: $('input[name="choice_for_vote"]:checked').val(),
                        },
                        dataType: 'JSON',
                        success: function (result) {
                            if (result.status === 'success') {
                                Swal.fire({
                                    title: 'สำเร็จ!',
                                    text: 'ส่งผล Vote สำเร็จ',
                                    icon: "success",
                                    buttonsStyling: false,
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(function () {
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
                    clearInterval(intervalId);
                    intervalId = null;
                    document.getElementById("countdown").innerHTML = `
                        <div class="text-danger fw-bold fs-3">หมดเวลา</div>
                    `;
                } else {
                    const hours = Math.floor((timeRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);

                    document.getElementById("countdown").innerHTML = `
                        <div class="d-flex flex-row flex-wrap gap-3 justify-content-center align-items-center">
                            <div class="countdown-item">
                                <span class="number">${hours.toString().padStart(2, '0')}</span>
                                <span class="label">ชม.</span>
                            </div>
                            <div class="countdown-item">
                                <span class="number">${minutes.toString().padStart(2, '0')}</span>
                                <span class="label">นาที</span>
                            </div>
                            <div class="countdown-item">
                                <span class="number">${seconds.toString().padStart(2, '0')}</span>
                                <span class="label">วินาที</span>
                            </div>
                        </div>
                    `;

                    intervalId = setTimeout(updateCountdown, 1000);
                }
            }

            updateCountdown();
        }
    </script>
</body>

</html>