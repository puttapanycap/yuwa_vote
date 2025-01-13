<?php

session_start();
define('_WEBROOT_PATH_', './');

if (!isset($_SESSION['session_key'])) {
	header('location: ' . _WEBROOT_PATH_ . 'login.php');
	exit(0);
}
if ($_SESSION['session_key'] != 'Vote12345') {
	header('location: ' . _WEBROOT_PATH_ . 'login.php');
	exit(0);
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
	<?php require _WEBROOT_PATH_ . 'components/head.html'; ?>
	<?php require _WEBROOT_PATH_ . 'components/script.html'; ?>
	<script src="./assets/plugins/custom/datatables/datatables.bundle.js"></script>
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

	<?php require _WEBROOT_PATH_ . '/components/navbar.php'; ?>

	<div class="container flex-column-fluid my-4">

		<div class="card rounded-4 shadow-sm">
			<div class="card-header">
				<div class="card-title">
					<h3>Current Topics</h3>
				</div>
				<div class="card-toolbar">
					<button onclick="topicAdd()" class="btn btn-primary">Add</button>
				</div>
			</div>
			<div class="card-body">
				<table class="table table-striped" id="topicsTable">
					<thead>
						<tr>
							<th>Close Time</th>
							<th>Topic Title</th>
							<th class="text-end">Actions</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>

	</div>

	<!-- QR Code Modal -->
	<div class="modal fade" id="qrModal" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="qrModalLabel">QR Code for Voting</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body text-center">

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="topicManageModal" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="topicManageTitle">Topic Manager</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body text-center">
					<div id="topic_manage_form"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-success" id="modalActionBtn"></button>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

	<?php require _WEBROOT_PATH_ . 'components/footer.php'; ?>
	<script src="./assets/plugins/custom/datatables/datatables.bundle.js"></script>
	<script src="./assets/plugins/custom/formrepeater/formrepeater.bundle.js"></script>

	<script>
		var tableTopics = function() {
			var table;
			var dt;

			var initDatatable = function() {
				dt = $('#topicsTable').DataTable({
					searchDelay: 500,
					processing: true,
					serverSide: true,
					autoWidth: true,
					lengthMenu: [
						[5, 10, 25, 100, -1],
						[5, 10, 25, 100, "ทั้งหมด"]
					],
					pageLength: 5,
					language: {
						processing: "<span>กำลังโหลด..</span>",
						lengthMenu: "แสดง _MENU_ รายการต่อหน้า",
						zeroRecords: "ไม่มีข้อมูล",
						info: "หน้า _PAGE_ จาก _PAGES_",
						infoEmpty: "ไม่มีข้อมูล",
						infoFiltered: "(กรองทั้งหมด _MAX_ แถว)"
					},
					order: [
						[0, 'desc']
					],
					ajax: {
						url: './actions/topics_datatable.php', // PHP file to fetch data
						type: 'POST',
						dataType: 'JSON',
						cache: false,
						data: function(data) {
							// data.input_search = $('#input_search').val();
						}
					},
					columns: [{
							data: 'expire_datetime'
						},
						{
							data: 'topic_title'
						},
						{
							data: null
						},
					],
					columnDefs: [{
						targets: -1,
						orderable: false,
						render: function(data, type, row) {
							return `<div class="d-flex flex-row align-items-end justify-content-end gap-2">
										<button class="btn btn-icon btn-sm btn-warning" onclick="topicEdit('${row.id}')">
											<i class="fa-solid fa-pen fs-3"></i>
										</button>
										<button class="btn btn-icon btn-sm btn-success" onclick="topicQRCode('${row.id}')">
											<i class="fa-solid fa-qrcode fs-3"></i>
										</button>
										<a target="_blank" href="./monitor.php?key=${row.share_key}" class="btn btn-icon btn-sm btn-info">
											<i class="fa-solid fa-chart-pie fs-3"></i>
										</a>
										<button class="btn btn-icon btn-sm btn-danger" onclick="topicDelete('${row.id}')">
											<i class="fa-solid fa-trash fs-3"></i>
										</button>
									</div>`;

						}
					}, ]
				});

			}

			// Public methods
			return {
				init: function() {
					initDatatable();
				}
			}

		}();

		// On document ready
		KTUtil.onDOMContentLoaded(function() {
			tableTopics.init();
		});

		function topicAdd() {
			$('#modalActionBtn').html('เพิ่ม');
			$('#modalActionBtn').attr('onclick', 'topicInsert()');
			$.ajax({
				type: 'GET',
				url: './actions/topics_add.php',
				dataType: 'HTML',
				success: function(result_html) {
					$('#topic_manage_form').html(result_html);
					$('#topicManageModal').modal('show');
				}
			});
		}

		function topicInsert() {
			let topicTitle = $('#topicTitle').val();
			let expireDateTime = $('#expireDateTime').val();

			let choicesArr = [];
			let choicesInvalidArr = [];
			$('[data-repeater-item]').each(function(key, val) {
				let sort = $(this).attr('data-choice-sort');
				let title = $(this).find('[data-choice-title]');
				if (title.val().length == 0) {
					title.addClass('is-invalid');
					choicesInvalidArr.push(1);
				} else {
					title.removeClass('is-invalid');
					choicesArr.push({
						sort: sort,
						title: title.val()
					});
				}
			});

			$('#topicTitle').removeClass('is-invalid');
			$('#expireDateTime').removeClass('is-invalid');

			let invalidArr = [];

			if (topicTitle.length == 0) {
				$('#topicTitle').addClass('is-invalid');
				invalidArr.push('กรอก Topic Name');
			}
			if (expireDateTime.length == 0) {
				$('#expireDateTime').addClass('is-invalid');
				invalidArr.push('กรอก Close Time');
			}
			if (choicesInvalidArr.length != 0) {
				invalidArr.push('กรอกตัวเลือก');
			}
			if (choicesArr.length == 0) {
				invalidArr.push('ต้องมีอย่างน้อย 1 ตัวเลือก');
			}

			if (invalidArr.length > 0) {
				alert('กรุณาตรวจสอบ: ' + invalidArr.join(', '));
			} else {

				$.ajax({
					type: 'POST',
					url: './actions/topics_insert.php',
					dataType: 'JSON',
					data: {
						topicTitle: $('#topicTitle').val(),
						expireDateTime: $('#expireDateTime').val(),
						choicesArr: choicesArr
					},
					success: function(data) {
						$('#topicManageModal').modal('hide');
						$('#topicsTable').DataTable().ajax.reload();
					}
				});
			}

		}

		function topicEdit(topic_id) {
			$('#modalActionBtn').html('บันทึก');
			$('#modalActionBtn').attr('onclick', `topicUpdate('${topic_id}')`);
			$.ajax({
				type: 'POST',
				url: './actions/topics_edit.php',
				dataType: 'HTML',
				data: {
					topic_id: topic_id,
				},
				success: function(result_html) {
					$('#topic_manage_form').html(result_html);
					$('#topicManageModal').modal('show');
				}
			});
		}

		function topicUpdate(topic_id) {
			let topicTitle = $('#topicTitle').val();
			let expireDateTime = $('#expireDateTime').val();

			let choicesCurrentArr = [];
			let choicesNewArr = [];
			let choicesAllArr = [];
			let choicesInvalidArr = [];
			$('[data-repeater-item]').each(function(key, val) {
				let cid = $(this).attr('data-choice-id');
				let sort = $(this).attr('data-choice-sort');
				let title = $(this).find('[data-choice-title]');
				if (title.val().length == 0) {
					title.addClass('is-invalid');
					choicesInvalidArr.push(1);
				} else {
					title.removeClass('is-invalid');
					choicesAllArr.push(1);
					if (cid != 'new') {
						choicesCurrentArr.push({
							id: cid,
							sort: sort,
							title: title.val()
						});
					} else {
						choicesNewArr.push({
							sort: sort,
							title: title.val()
						});
					}

				}
			});

			$('#topicTitle').removeClass('is-invalid');
			$('#expireDateTime').removeClass('is-invalid');

			let invalidArr = [];

			if (topicTitle.length == 0) {
				$('#topicTitle').addClass('is-invalid');
				invalidArr.push('Topic Name');
			}
			if (expireDateTime.length == 0) {
				$('#expireDateTime').addClass('is-invalid');
				invalidArr.push('Close Time');
			}
			if (choicesInvalidArr.length != 0) {
				invalidArr.push('ตัวเลือก');
			}
			if (choicesAllArr.length == 0) {
				invalidArr.push('ต้องมีอย่างน้อย 1 ตัวเลือก');
			}

			if (invalidArr.length > 0) {
				alert('กรุณาตรวจสอบ: ' + invalidArr.join(', '));
			} else {

				$.ajax({
					type: 'POST',
					url: './actions/topics_update.php',
					dataType: 'JSON',
					data: {
						topic_id: topic_id,
						topicTitle: $('#topicTitle').val(),
						expireDateTime: $('#expireDateTime').val(),
						choicesCurrentArr: choicesCurrentArr,
						choicesNewArr: choicesNewArr
					},
					success: function(data) {
						$('#topicManageModal').modal('hide');
						$('#topicsTable').DataTable().ajax.reload();
					}
				});
			}
		}

		function topicQRCode(topic_id) {
			$.ajax({
				type: 'POST',
				url: './actions/topics_qrcode.php',
				dataType: 'HTML',
				data: {
					topic_id: topic_id,
				},
				success: function(result_html) {
					$('#qrModal * .modal-body').html(result_html);
					$('#qrModal').modal('show');
				}
			});
		}

		function topicDelete(topic_id) {
			Swal.fire({
				text: `ต้องการลบ Topic นี้หรือไม่`,
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
						url: './actions/topics_delete.php',
						type: 'POST',
						data: {
							topic_id: topic_id,
						},
						dataType: 'JSON',
						success: function(result) {
							if (result.status === 'success') {
								$('#topicsTable').DataTable().ajax.reload();
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
							else {
								Swal.fire({
									text: 'ระบบขัดข้อง ลบไม่สำเร็จ',
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
	</script>
</body>

</html>