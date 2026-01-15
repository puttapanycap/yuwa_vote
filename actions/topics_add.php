<div class="row">
    <!-- Left Column: Topic Details & Choices -->
    <div class="col-lg-7">
        <div class="mb-3">
            <label for="topicTitle" class="form-label fw-bold">Topic Name</label>
            <input type="text" class="form-control" id="topicTitle" required>
        </div>
        <div class="mb-3">
            <label for="expireDateTime" class="form-label">Close Time</label>
            <input type="text" class="form-control" id="expireDateTime" required>
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
                                            <input type="text" class="form-control" id="input_choice" data-choice-title
                                                placeholder="หัวข้อ" />
                                            <label for="input_choice">หัวข้อ</label>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="d-flex flex-row h-100 gap-2 align-items-center">
                                            <a href="javascript:;" data-repeater-delete
                                                class="btn btn-icon btn-light-danger">
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
    </div>

    <!-- Right Column: Display Options -->
    <div class="col-lg-5">
        <div class="card border border-1 border-dashed border-primary">
            <div class="card-header border-0 pt-5 pb-0">
                <h4 class="card-title text-primary">
                    <i class="fa-solid fa-cog me-2"></i>ตั้งค่า
                </h4>
            </div>
            <div class="card-body">
                <!-- Display Mode -->
                <div class="mb-4">
                    <label class="form-label fw-bold">รูปแบบการแสดงผล Monitor</label>
                    <div class="d-flex flex-column gap-2">
                        <label class="btn btn-outline btn-outline-dashed btn-active-light-primary p-3 d-flex align-items-center gap-2">
                            <input type="radio" class="btn-check" name="displayMode" value="card" checked id="displayModeCard" />
                            <i class="fa-solid fa-grid-2 fs-3"></i>
                            <span>Card View</span>
                        </label>
                        <label class="btn btn-outline btn-outline-dashed btn-active-light-primary p-3 d-flex align-items-center gap-2">
                            <input type="radio" class="btn-check" name="displayMode" value="list" id="displayModeList" />
                            <i class="fa-solid fa-list fs-3"></i>
                            <span>List View</span>
                        </label>
                    </div>
                </div>

                <!-- Show Score -->
                <div class="mb-4">
                    <label class="form-label fw-bold">การแสดงคะแนน</label>
                    <div class="form-check form-switch form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" id="showScore" name="showScore" checked />
                        <label class="form-check-label fw-semibold" for="showScore">
                            แสดงคะแนนในหน้า Monitor
                        </label>
                    </div>
                    <div class="form-text text-muted">เปิดใช้งานเพื่อแสดงคะแนนรวมของแต่ละตัวเลือกบนหน้า Monitor</div>
                </div>

                <!-- Public Mode -->
                <div class="mb-3">
                    <label class="form-label fw-bold">โหมดสาธารณะ</label>
                    <div class="form-check form-switch form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" id="isPublic" name="isPublic" />
                        <label class="form-check-label fw-semibold" for="isPublic">
                            เปิดโหมดสาธารณะ
                        </label>
                    </div>
                    <div class="form-text text-muted">เปิดใช้งานเพื่อให้ทุกคนสามารถดูหน้า Monitor ได้โดยไม่ต้องล็อกอิน</div>
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
</script>