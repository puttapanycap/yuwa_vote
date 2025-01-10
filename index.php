<?php

session_start();
define('_WEBROOT_PATH_', './');

if (!isset($_SESSION['session_key'])) {
	header('location: ' . _WEBROOT_PATH_ . 'login.php');
	exit(0);
}
if ($_SESSION['session_key'] != 'ycapHA0012251') {
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

	<div class="container my-4">
		<h1 class="text-center">Vote Management System</h1>
		<ul class="nav nav-tabs" id="menuTabs">
			<li class="nav-item">
				<button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard" type="button">Dashboard</button>
			</li>
			<li class="nav-item">
				<button class="nav-link" id="manage-tab" data-bs-toggle="tab" data-bs-target="#manage" type="button">Manage Voting Topics</button>
			</li>
		</ul>

		<div class="tab-content my-3">
			<!-- Dashboard Tab -->
			<div class="tab-pane fade show active" id="dashboard">
				<h2>Dashboard</h2>
				<div id="chart"></div>
			</div>

			<!-- Manage Tab -->
			<div class="tab-pane fade" id="manage">
				<div class="d-flex flex-column gap-3">
					<div class="card shadow-sm">
						<div class="card-header">
							<div class="card-title">
								<h3>Manage Voting Topics</h3>
							</div>
							<div class="card-toolbar"></div>
						</div>
						<div class="card-body">

						</div>
					</div>

					<div class="card shadow-sm">
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
										<th>Topic</th>
										<th>Close Time</th>
										<th>Actions</th>
									</tr>
								</thead>
							</table>
						</div>
					</div>

				</div>

			</div>
		</div>
	</div>

	<!-- QR Code Modal -->
	<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="qrModalLabel">QR Code for Voting</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body text-center">
					<div id="qrCode"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="topicManageModal" tabindex="-1" aria-labelledby="topicManageTitle" aria-hidden="true">
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

	<script>
		function topicAdd() {
			$('#modalActionBtn').html('เพิ่ม');
			$('#modalActionBtn').attr('onclick', 'topicInsert()');
			$('#topic_manage_form').html(`
				<div class="mb-3">
					<label for="topicName" class="form-label fw-bold">Topic Name</label>
					<input type="text" class="form-control" id="topicName" required>
				</div>
				<div class="mb-3">
					<label for="closeTime" class="form-label">Close Time</label>
					<input type="text" class="form-control" id="closeTime" required>
				</div>
			`);
			const input_search_date = new tempusDominus.TempusDominus(document.getElementById("closeTime"), {
				display: {
					viewMode: "calendar",
					components: {
						decades: false,
						year: true,
						month: true,
						date: true,
						hours: true,
						minutes: true,
						seconds: true,
						useTwentyfourHour: true
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
					format: "yyyy-MM-dd HH:mm:ss"
				},
			});
			$('#topicManageModal').modal('show');
		}

		function topicInsert() {
			let topicName = $('#topicName').val();
			let closeTime = $('#closeTime').val();

			if (topicName.length == 0) {
				alert('Please enter topic name');
			} else if (closeTime.length == 0) {
				alert('Please enter close time');
			} else {
				$.ajax({
					type: 'POST',
					url: './actions/topics_insert.php',
					dataType: 'JSON',
					data: {
						topicName: $('#topicName').val(),
						closeTime: $('#closeTime').val(),
					},
					success: function(data) {
						$('#topicManageModal').modal('hide');
						$('#topicsTable').DataTable().ajax.reload();
					}
				});
			}

		}

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
										<button class="btn btn-success" onclick="">Gen QR</button>
										<button class="btn btn-danger" onclick="">Delete</button>
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
	</script>
</body>

</html>