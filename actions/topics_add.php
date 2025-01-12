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
                                    <input type="text" class="form-control" id="input_choice" data-choice-title placeholder="หัวข้อ" />
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
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    $('[data-repeater-list="choices_list"]').sortable({
        handle: ".handle-sortable",
        update: function( event, ui ) {
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
            reNumCheckListSort();
            $(this).slideDown();
        },

        hide: function(deleteElement) {
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