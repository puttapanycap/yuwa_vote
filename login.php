<?php

session_start();
define('_WEBROOT_PATH_', './');

require _WEBROOT_PATH_ . 'helpers/load_env.php';

if (isset($_SESSION['session_key'])) {
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
        var defaultThemeMode = "light";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>
    <main class="d-flex flex-column justify-content-center align-items-center gap-6 h-100">
        <div class="login">
            <img class="logo" src="assets/medias/logos/android-chrome-192x192.png" alt="">

            <span class="fs-3 fw-bold">เข้าสู่ระบบ</span>
            <form id="login-form" class="d-flex w-250px flex-column gap-2">

                <div class="form-floating">
                    <input type="password" class="form-control form-control-solid border" id="input_password" name="input_password" placeholder="รหัสผ่าน" />
                    <label for="input_password">รหัสผ่าน</label>
                </div>

                <div class="d-flex justify-content-between">
                    <div class="form-check form-check-custom form-check-solid form-check-lg">
                        <input class="form-check-input" type="checkbox" value="true" id="cb_remember" />
                        <label class="form-check-label" for="cb_remember">
                            จดจำการเข้าสู่ระบบ
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>

        </div>

        <span class="d-flex flex-row justify-content-start align-items-center gap-2">
            <span class="fw-bold">Create By YUWA IT</span>
        </span>

    </main>

    <div class="modal modal-stacked fade" tabindex="-1" id="modal_reset_password">
        <div class="modal-dialog modal-dialog-centered modal-md modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header px-4 py-3">
                    <h5 class="modal-title" id="modal_reset_password_title">รีเซ็ตรหัสผ่าน</h5>
                    <div class="btn btn-icon btn-sm btn-color-gray-600 btn-active-light-danger ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark fs-2"></i>
                    </div>
                </div>
                <div class="modal-body bg-light p-3" id="modal_view_visa_request_body">
                    <div class="container">
                        <div class="d-flex flex-column gap-4">

                            <div class="card">
                                <div class="card-body">
                                    <label for="input_reset_pass_email" class="required form-label">Email (เข้าสู่ระบบ)</label>
                                    <input type="email" id="input_reset_pass_email" name="input_reset_pass_email" class="form-control form-control-solid" placeholder="example@gmail.com" />
                                </div>
                            </div>

                            <button class="btn btn-primary" onclick="sendResetPassword()">ส่ง</button>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

            let input_password = $('#input_password').val();

            let pass_arr_status = [];
            let pass_arr_message = [];

            if (input_password == '') {
                $('#input_password').addClass('is-invalid');
                initCheckInvalid('input_password');
                pass_arr_status.push(0);
                pass_arr_message.push('Password');
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
                    url: './actions/auth_login.php',
                    type: 'POST',
                    data: {
                        input_password: $('#input_password').val(),
                    },
                    dataType: 'JSON',
                    beforeSend: function() {
                        Swal.fire({
                            html: `<div class="d-flex flex-column flex-center gap-3">
                                    <span class="fs-5 fw-bold">กำลังตรวจสอบข้อมูล</span>
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
                                text: `เข้าสู่ระบบสำเร็จ`,
                                icon: "success",
                                buttonsStyling: false,
                                showConfirmButton: false,
                                timer: 1000
                            }).then(function() {
                                window.location = './';
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