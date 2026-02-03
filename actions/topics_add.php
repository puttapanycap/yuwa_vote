<!-- Topic Manage Tabs -->
<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" data-bs-toggle="tab" href="#tab_topic_info" role="tab">
            <i class="fa-solid fa-file-lines me-2"></i>ข้อข้อและเวลา
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" data-bs-toggle="tab" href="#tab_choices" role="tab">
            <i class="fa-solid fa-list-ul me-2"></i>ตัวเลือก
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" data-bs-toggle="tab" href="#tab_settings" role="tab">
            <i class="fa-solid fa-cog me-2"></i>ตั้งค่า
        </a>
    </li>
</ul>

<div class="tab-content">
    <!-- Tab 1: ข้อข้อและเวลา -->
    <div class="tab-pane fade show active" id="tab_topic_info" role="tabpanel">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="mb-5">
                    <label for="topicTitle" class="form-label fw-bold fs-5 required">Topic Name</label>
                    <input type="text" class="form-control form-control-lg" id="topicTitle" placeholder="กรอกชื่อ Topic" required>
                    <div class="form-text text-muted">ชื่อหัวข้อที่จะแสดงบนหน้าโหวต</div>
                </div>
                <div class="mb-3">
                    <label for="expireDateTime" class="form-label fw-bold fs-5 required">Close Time</label>
                    <input type="text" class="form-control form-control-lg" id="expireDateTime" placeholder="เลือกวันและเวลาปิดโหวต" required>
                    <div class="form-text text-muted">กำหนดวันและเวลาที่จะปิดการโหวต</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 2: ตัวเลือก -->
    <div class="tab-pane fade" id="tab_choices" role="tabpanel">
        <div class="card" id="choices_list">
            <div class="card-header px-4 py-3 bg-light">
                <div class="card-title">
                    <h3 class="mb-0"><i class="fa-solid fa-list-check me-2 text-primary"></i>รายการตัวเลือก</h3>
                </div>
                <div class="card-toolbar">
                    <button class="btn btn-sm btn-success" data-repeater-create>
                        <i class="fa-solid fa-plus fs-4 me-1"></i> เพิ่มตัวเลือก
                    </button>
                </div>
            </div>
            <div class="card-body hover-scroll-y py-4 px-4 mh-400px bg-secondary rounded-bottom">
                <!--begin::Form group-->
                <div class="form-group">
                    <div data-repeater-list="choices_list" class="d-flex flex-column gap-2">
                        <div data-repeater-item data-choice-sort="1" class="card p-4 shadow-sm">
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
                                        <input type="text" class="form-control" id="input_choice" data-choice-title placeholder="หัวข้อ" />
                                        <label for="input_choice">หัวข้อตัวเลือก</label>
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 3: ตั้งค่า -->
    <div class="tab-pane fade" id="tab_settings" role="tabpanel">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Display Mode -->
                <div class="card mb-5 border border-1 border-dashed border-primary">
                    <div class="card-header border-0">
                        <h4 class="card-title text-primary mb-0">
                            <i class="fa-solid fa-display me-2"></i>รูปแบบการแสดงผล
                        </h4>
                    </div>
                    <div class="card-body pt-2">
                        <div class="d-flex flex-column gap-3">
                            <label class="btn btn-outline btn-outline-dashed btn-active-light-primary p-4 d-flex align-items-center gap-3">
                                <input type="radio" class="btn-check" name="displayMode" value="card" checked id="displayModeCard" />
                                <i class="fa-solid fa-grid-2 fs-2"></i>
                                <div class="d-flex flex-column align-items-start">
                                    <span class="fw-bold">Card View</span>
                                    <span class="text-muted fs-7">แสดงผลแบบการ์ด</span>
                                </div>
                            </label>
                            <label class="btn btn-outline btn-outline-dashed btn-active-light-primary p-4 d-flex align-items-center gap-3">
                                <input type="radio" class="btn-check" name="displayMode" value="list" id="displayModeList" />
                                <i class="fa-solid fa-list fs-2"></i>
                                <div class="d-flex flex-column align-items-start">
                                    <span class="fw-bold">List View</span>
                                    <span class="text-muted fs-7">แสดงผลแบบรายการ</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Show Score -->
                <div class="card mb-5 border border-1 border-dashed border-success">
                    <div class="card-header border-0">
                        <h4 class="card-title text-success mb-0">
                            <i class="fa-solid fa-chart-simple me-2"></i>การแสดงคะแนน
                        </h4>
                    </div>
                    <div class="card-body pt-2">
                        <div class="form-check form-switch form-check-custom form-check-solid form-check-lg">
                            <input class="form-check-input" type="checkbox" id="showScore" name="showScore" checked />
                            <label class="form-check-label fw-semibold" for="showScore">
                                แสดงคะแนนในหน้า Monitor
                            </label>
                        </div>
                        <div class="form-text text-muted mt-2">เปิดใช้งานเพื่อแสดงคะแนนรวมของแต่ละตัวเลือกบนหน้า Monitor</div>
                    </div>
                </div>

                <!-- Public Mode -->
                <div class="card mb-5 border border-1 border-dashed border-info">
                    <div class="card-header border-0">
                        <h4 class="card-title text-info mb-0">
                            <i class="fa-solid fa-globe me-2"></i>โหมดสาธารณะ
                        </h4>
                    </div>
                    <div class="card-body pt-2">
                        <div class="form-check form-switch form-check-custom form-check-solid form-check-lg">
                            <input class="form-check-input" type="checkbox" id="isPublic" name="isPublic" />
                            <label class="form-check-label fw-semibold" for="isPublic">
                                เปิดโหมดสาธารณะ
                            </label>
                        </div>
                        <div class="form-text text-muted mt-2">เปิดใช้งานเพื่อให้ทุกคนสามารถดูหน้า Monitor ได้โดยไม่ต้องล็อกอิน</div>
                    </div>
                </div>

                <!-- Voting Mode -->
                <div class="card border border-1 border-dashed border-warning">
                    <div class="card-header border-0">
                        <h4 class="card-title text-warning mb-0">
                            <i class="fa-solid fa-check-double me-2"></i>รูปแบบการโหวต
                        </h4>
                    </div>
                    <div class="card-body pt-2">
                        <div class="d-flex flex-column gap-3">
                            <label class="btn btn-outline btn-outline-dashed btn-active-light-warning p-4 d-flex align-items-center gap-3">
                                <input type="radio" class="btn-check" name="voteMode" value="single" checked id="voteModeSingle" />
                                <i class="fa-solid fa-circle-dot fs-2"></i>
                                <div class="d-flex flex-column align-items-start">
                                    <span class="fw-bold">เลือกได้ 1 ตัวเลือก</span>
                                    <span class="text-muted fs-7">ผู้โหวตสามารถเลือกได้เพียง 1 ตัวเลือกเท่านั้น</span>
                                </div>
                            </label>
                            <label class="btn btn-outline btn-outline-dashed btn-active-light-warning p-4 d-flex align-items-center gap-3">
                                <input type="radio" class="btn-check" name="voteMode" value="multiple" id="voteModeMultiple" />
                                <i class="fa-solid fa-check-double fs-2"></i>
                                <div class="d-flex flex-column align-items-start flex-grow-1">
                                    <span class="fw-bold">เลือกได้หลายตัวเลือก</span>
                                    <span class="text-muted fs-7">ผู้โหวตสามารถเลือกได้มากกว่า 1 ตัวเลือก</span>
                                </div>
                            </label>
                            <div id="maxChoicesWrapper" class="ps-10 d-none">
                                <div class="d-flex align-items-center gap-3">
                                    <label class="form-label fw-semibold mb-0">จำนวนสูงสุดที่เลือกได้:</label>
                                    <input type="number" class="form-control form-control-sm w-100px" id="maxChoices" name="maxChoices" min="2" value="2" />
                                    <span class="text-muted">ตัวเลือก</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('[data-repeater-list="choices_list"]').sortable({
        handle: ".handle-sortable",
        update: function (event, ui) {
            reNumCheckListSort();
        }
    });
    $('[data-repeater-list="choices_list"]').disableSelection();

    function reNumCheckListSort() {
        $('[data-choice-sort]').each(function (key, val) {
            const new_sort = key + 1;
            $(this).attr('data-choice-sort', new_sort);
        });
    }

    $('#choices_list').repeater({
        initEmpty: false,

        defaultValues: {
            'text-input': 'foo'
        },

        show: function () {
            reNumCheckListSort();
            $(this).slideDown();
        },

        hide: function (deleteElement) {
            $(this).slideUp(deleteElement);
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

    // Vote Mode Toggle
    $('input[name="voteMode"]').on('change', function() {
        if ($(this).val() === 'multiple') {
            $('#maxChoicesWrapper').removeClass('d-none');
        } else {
            $('#maxChoicesWrapper').addClass('d-none');
        }
    });
</script>