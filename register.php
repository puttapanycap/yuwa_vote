<?php

session_start();
define('_WEBROOT_PATH_', './');

require _WEBROOT_PATH_ . 'helpers/load_env.php';

if (isset($_SESSION['user_id']) || isset($_SESSION['user_name'])) {
    header('location: ' . _WEBROOT_PATH_);
    exit(0);
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require _WEBROOT_PATH_ . 'components/head.html'; ?>

    <style>
        [data-bs-theme=light] {
            --mod-bg-panel: #FFFFFF;
            --mod-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
        }

        [data-bs-theme=dark] {
            --mod-bg-panel: #1E1E2D;
            --mod-shadow: transparent;
        }

        .logo {
            width: 150px;
            height: auto;
        }

        .login {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 25px 30px;
            border-radius: 20px;
            min-height: 100px;
            width: fit-content;
            background: var(--mod-bg-panel);
            box-shadow: var(--mod-shadow);
            gap: 10px;
        }
    </style>

</head>

<body>
    <!--begin::Theme mode setup on page load-->
    <script>
        document.documentElement.setAttribute("data-bs-theme", "light");
    </script>
    <main class="d-flex flex-column justify-content-center align-items-center gap-6 h-100">
        <a href="./login.php" class="btn bg-body btn-color-primary shadow-sm">Login</a>
        <div class="login">
            <img class="logo" src="assets/medias/logos/android-chrome-192x192.png" alt="">

            <span class="fs-3 fw-bold">ลงทะเบียน</span>
            <form id="login-form" class="d-flex w-250px flex-column gap-2">
                <div class="mt-2 d-flex flex-row align-items-center">
                    <i class="ki-solid ki-badge fs-2 me-2"></i> ชื่อผู้ใช้
                </div>
                <div class="form-floating">
                    <input type="text" class="form-control form-control-solid border"
                        id="input_username"
                        name="input_username"
                        placeholder="Username"
                        required />
                    <label for="input_username" class="required">Username</label>
                </div>

                <div class="mt-2 d-flex flex-row align-items-center">
                    <i class="ki-solid ki-key-square fs-2 me-2"></i> รหัสผ่าน
                </div>
                <div class="form-floating">
                    <input type="password" class="form-control form-control-solid border"
                        id="input_password"
                        name="input_password"
                        placeholder="Password"
                        required />
                    <label for="input_password" class="required">Password</label>
                </div>
                <div class="form-floating">
                    <input type="password" class="form-control form-control-solid border"
                        id="input_password_confirm"
                        name="input_password_confirm"
                        placeholder="ยืนยัน Password"
                        required />
                    <label for="input_password_confirm" class="required">ยืนยัน Password</label>
                </div>

                <div class="mt-2 d-flex flex-row align-items-center">
                    <i class="ki-solid ki-sms fs-2 me-2"></i> อีเมลล์
                </div>
                <div class="form-floating">
                    <input type="email" class="form-control form-control-solid border"
                        id="input_email"
                        name="input_email"
                        placeholder="user@example.com"
                        required />
                    <label for="input_email" class="required">Email</label>
                </div>

                <button type="submit" class="btn btn-success w-100">ลงทะเบียน</button>
            </form>

        </div>

        <span class="d-flex flex-row justify-content-start align-items-center gap-2">
            <span class="fw-bold">Create By YUWA IT</span>
        </span>

    </main>

    <!--begin::Javascript-->
    <script>
        var hostUrl = "assets/";
    </script>

    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="assets/plugins/global/plugins.bundle.js"></script>
    <script src="assets/js/scripts.bundle.js"></script>

    <script>
        $("form").submit(function(event) {
            event.preventDefault();

            let input_username = $('#input_username');
            let input_password = $('#input_password');
            let input_password_confirm = $('#input_password_confirm');
            let input_email = $('#input_email');

            input_username.removeClass('is-invalid');
            input_password.removeClass('is-invalid');
            input_password_confirm.removeClass('is-invalid');
            input_email.removeClass('is-invalid');

            let pass_arr_status = [];
            let pass_arr_message = [];

            if (input_username.val() == '') {
                $('#input_username').addClass('is-invalid');
                pass_arr_status.push(0);
                pass_arr_message.push('Username');
            }
            
            if (input_password.val() == '') {
                $('#input_password').addClass('is-invalid');
                pass_arr_status.push(0);
                pass_arr_message.push('Password');
            }

            if (input_password_confirm.val() == '') {
                $('#input_password_confirm').addClass('is-invalid');
                pass_arr_status.push(0);
                pass_arr_message.push('ยืนยัน Password');
            }
            
            if (input_email.val() == '') {
                $('#input_email').addClass('is-invalid');
                pass_arr_status.push(0);
                pass_arr_message.push('Email');
            }

            if (input_password.val() != input_password_confirm.val()) {
                $('#input_password').addClass('is-invalid');
                pass_arr_status.push(0);
                pass_arr_message.push('Password ไม่ตรงกัน');
            }

            if (pass_arr_status.includes(0)) {
                Swal.fire({
                    text: 'กรุณาตรวจสอบ ' + pass_arr_message.join(', '),
                    icon: "warning",
                    buttonsStyling: false,
                    confirmButtonText: "ตกลง",
                    customClass: {
                        confirmButton: "btn fw-bold btn-primary",
                    }
                });
            } else {

                $.ajax({
                    url: './actions/register.php',
                    type: 'POST',
                    data: {
                        username: $('#input_username').val(),
                        password: $('#input_password').val(),
                        email: $('#input_email').val(),
                    },
                    dataType: 'JSON',
                    beforeSend: function() {
                        Swal.fire({
                            html: `<div class="d-flex flex-column flex-center gap-3">
                                    <span class="fs-5 fw-bold">กำลังส่งข้อมูล</span>
                                    <img class="h-100px" src="./assets/medias/svg/loading-dots-transparent.svg">
                                </div>`,
                            buttonsStyling: false,
                            showConfirmButton: false,
                            allowOutsideClick: false
                        })
                    },
                    success: function(result) {
                        if (result.status === 'success') {
                            Swal.fire({
                                text: result.message,
                                icon: "success",
                                buttonsStyling: false,
                                showConfirmButton: false,
                                timer: 1000
                            }).then(function() {
                                window.location.href = './login.php';
                            });
                        } else if (result.status === 'error' || result.status === 'warning') {
                            Swal.fire({
                                text: result.message,
                                icon: result.status,
                                buttonsStyling: false,
                                confirmButtonText: "ตกลง",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary",
                                }
                            });
                        } else {
                            Swal.fire({
                                text: "Error Response!",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "ตกลง",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary",
                                }
                            });
                        }
                    },
                    error: function(jqXHR, exception) {
                        Swal.fire({
                            text: getErrorMessage(jqXHR, exception),
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "ตกลง",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary",
                            }
                        });
                    },
                });

            }

        });

        function getErrorMessage(jqXHR, exception) {
            var msg = '';
            if (jqXHR.status === 0) {
                msg = 'Not connect.\n Verify Network.';
            } else if (jqXHR.status == 404) {
                msg = 'Requested page not found. [404]';
            } else if (jqXHR.status == 500) {
                msg = 'Internal Server Error [500].';
            } else if (exception === 'parsererror') {
                msg = 'Requested JSON parse failed.';
            } else if (exception === 'timeout') {
                msg = 'Time out error.';
            } else if (exception === 'abort') {
                msg = 'Ajax request aborted.';
            } else {
                msg = 'Uncaught Error.\n' + jqXHR.responseText;
            }
            return msg;
        }
    </script>

    <!--end::Javascript-->
</body>
<!--end::Body-->

</html>