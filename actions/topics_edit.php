<?php

session_start();
define('_WEBROOT_PATH_', '../');

// Connection Setup :: START
require _WEBROOT_PATH_ . '/helpers/load_env.php';
require _WEBROOT_PATH_ . '/helpers/functions.php';
$connections = getDatabaseConnections();
$vote_conn = $connections['vote'];
// Connection Setup :: END
$topic_id = $_POST['topic_id'];
$response = [];
$topic_sql = "  SELECT
                    topic_title,
                    expire_datetime,
                    member_id
                FROM
                    vote_topics
                WHERE
                    id='$topic_id' ";
$topic_query = $vote_conn->query($topic_sql);
$topic_row = $topic_query->fetch_assoc();

$choices_sql = " SELECT
                    id,
                    choice_title,
                    choice_sort
                FROM
                    vote_choices
                WHERE
                    topic_id='$topic_id'
                ORDER BY
                    choice_sort ASC ";
$choices_query = $vote_conn->query($choices_sql);
$choices = [];
while ($choice_row = $choices_query->fetch_assoc()) {
    $choices[] = $choice_row;
}
$vote_conn->close();

?>

<div class="mb-3">
    <label for="topicTitle" class="form-label fw-bold">Topic Name</label>
    <input value="<?php echo $topic_row['topic_title'] ?>" type="text" class="form-control" id="topicTitle" required>
</div>
<div class="mb-3">
    <label for="expireDateTime" class="form-label">Close Time</label>
    <input value="<?php echo $topic_row['expire_datetime'] ?>" type="text" class="form-control" id="expireDateTime" required>
</div>

<div class="mb-3">
    <div class="card" id="choices_list">
        <div class="card-header px-4 py-3">
            <div class="card-title">
                <h3>ตัวเลือก</h3>
            </div>
            <div class="card-toolbar">
                <button class="btn btn-icon btn-sm btn-success" data-repeater-create>
                    <i class="fa-solid fa-plus fs-3"></i>
                </button>
            </div>
        </div>
        <div class="card-body hover-scroll-y py-4 px-4 h-300px bg-secondary runded-bottom-4">

            <!--begin::Form group-->
            <div class="form-group">
                <div data-repeater-list="choices_list" class="d-flex flex-column gap-2">

                    <?php
                    foreach ($choices as $choice) :
                    ?>
                        <div data-repeater-item data-choice-sort="<?php echo $choice['choice_sort'] ?>" data-choice-id="<?php echo $choice['id'] ?>" class="card p-4 shadow-sm">

                            <div class="row g-2">
                                <div class="col-auto">
                                    <div class="d-flex flex-row h-100 gap-2 align-items-center">
                                        <div class="handle-sortable btn btn-icon btn-light btn-color-dark">
                                            <i class="fa-solid fa-up-down fs-2"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-floating">
                                        <input value="<?php echo $choice['choice_title'] ?>" type="text" class="form-control" id="input_choice" data-choice-title placeholder="หัวข้อ" />
                                        <label for="input_choice">หัวข้อ</label>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="d-flex flex-row h-100 gap-2 align-items-center">
                                        <a href="javascript:;" data-repeater-delete class="btn btn-icon btn-light-danger">
                                            <i class="fa-solid fa-trash fs-3"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                        </div>

                    <?php
                    endforeach;
                    ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    $('[data-repeater-list="choices_list"]').sortable({
        handle: ".handle-sortable",
        update: function(event, ui) {
            reNumCheckListSort();
        }
    });
    $('[data-repeater-list="choices_list"]').disableSelection();

    function reNumCheckListSort() {
        $('[data-choice-sort]').each(function(key, val) {
            const new_sort = key + 1;
            $(this).attr('data-choice-sort', new_sort);
        });
    }

    $('#choices_list').repeater({
        initEmpty: false,

        defaultValues: {
            'text-input': 'foo'
        },

        show: function() {
            $(this).attr('data-choice-id', 'new');
            $(this).find('[data-choice-title]').val(null);
            $(this).slideDown();
            return reNumCheckListSort();
        },

        hide: function(deleteElement) {
            let thisElm = $(this);
            let choiceID = thisElm.attr('data-choice-id');
            Swal.fire({
                text: `ต้องการลบตัวเลือกนี้หรือไม่`,
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
                        url: './actions/choices_delete.php',
                        type: 'POST',
                        data: {
                            choice_id: choiceID,
                        },
                        dataType: 'JSON',
                        success: function(result) {
                            if (result.status === 'success') {
                                reNumCheckListSort();
                                thisElm.slideUp(deleteElement);
                                Swal.fire({
                                    text: `ลบแล้ว !`,
                                    icon: "success",
                                    buttonsStyling: false,
                                    showConfirmButton: false,
                                    timer: 1000
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
                            }
                        }
                    });

                }
            });
            
        }
    });

    if (!$('#expireDateTime').data('DateTimePicker')) {
        const input_search_date = new tempusDominus.TempusDominus(document.getElementById("expireDateTime"), {
            display: {
                viewMode: "calendar",
                components: {
                    decades: false,
                    year: true,
                    month: true,
                    date: true,
                    hours: true,
                    minutes: true,
                    seconds: true
                },
                buttons: {
                    today: true
                },
            },
            localization: {
                locale: "th",
                startOfTheWeek: 1,
                dayViewHeaderFormat: {
                    month: 'long',
                    year: 'numeric'
                },
                format: "yyyy-MM-dd HH:mm:ss",
                hourCycle: "h23" // ใช้ระบบ 24 ชั่วโมง
            },
        });
    }
</script>