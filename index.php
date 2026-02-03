<?php

session_start();
define('_WEBROOT_PATH_', './');

// Connection Setup :: START
require _WEBROOT_PATH_ . '/helpers/load_env.php';
require _WEBROOT_PATH_ . '/helpers/functions.php';
$connections = getDatabaseConnections();
$vote_conn = $connections['vote'];
// Connection Setup :: END

// Session Key Management
$session_key = isset($_GET['key']) ? $_GET['key'] : null;
$workspace = getOrCreateWorkspaceSession($vote_conn, $session_key);

// If new session or no valid key, redirect with new key
if ($workspace['is_new'] || !$session_key) {
	$_SESSION['workspace_key'] = $workspace['session_key'];
	header('location: ' . _WEBROOT_PATH_ . '?key=' . $workspace['session_key']);
	exit(0);
}

// Store in session for AJAX calls
$_SESSION['workspace_key'] = $workspace['session_key'];
$current_session_key = $workspace['session_key'];
$short_key = substr($current_session_key, 0, 8) . '...';

// Get topic count for this session
$count_sql = "SELECT COUNT(*) as total FROM vote_topics WHERE session_key='$current_session_key'";
$count_query = $vote_conn->query($count_sql);
$topic_count = $count_query->fetch_assoc()['total'];

?>


<!DOCTYPE html>
<html lang="en">

<head>
	<?php require _WEBROOT_PATH_ . 'components/head.html'; ?>
	<?php require _WEBROOT_PATH_ . 'components/script.html'; ?>
	<script src="./assets/plugins/custom/datatables/datatables.bundle.js"></script>

	<style>
		/* Modern Dashboard Styles */
		:root {
			--primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			--success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
			--bg-gradient: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
			--card-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
		}

		body {
			background: var(--bg-gradient);
			min-height: 100vh;
		}

		/* Hero Section */
		.hero-section {
			background: var(--primary-gradient);
			border-radius: 24px;
			padding: 2.5rem;
			color: white;
			box-shadow: 0 15px 50px rgba(102, 126, 234, 0.3);
			margin-bottom: 2rem;
		}

		.hero-title {
			font-size: 2.5rem;
			font-weight: 800;
			text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
		}

		.hero-subtitle {
			opacity: 0.9;
			font-size: 1.1rem;
		}

		/* Session Badge */
		.session-badge {
			background: rgba(255, 255, 255, 0.2);
			backdrop-filter: blur(10px);
			padding: 0.75rem 1.5rem;
			border-radius: 50px;
			display: inline-flex;
			align-items: center;
			gap: 0.75rem;
			font-family: 'Monaco', monospace;
			border: 1px solid rgba(255, 255, 255, 0.3);
		}

		.copy-btn {
			background: rgba(255, 255, 255, 0.3);
			border: none;
			border-radius: 8px;
			padding: 0.5rem 1rem;
			color: white;
			cursor: pointer;
			transition: all 0.3s ease;
			display: flex;
			align-items: center;
			gap: 0.5rem;
		}

		.copy-btn:hover {
			background: rgba(255, 255, 255, 0.5);
			transform: scale(1.05);
		}

		/* Action Buttons */
		.action-btn-group {
			display: flex;
			gap: 1rem;
			flex-wrap: wrap;
		}

		.action-btn {
			padding: 0.75rem 1.5rem;
			border-radius: 12px;
			border: 2px solid rgba(255, 255, 255, 0.3);
			background: rgba(255, 255, 255, 0.15);
			color: white;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.3s ease;
			display: flex;
			align-items: center;
			gap: 0.5rem;
		}

		.action-btn:hover {
			background: rgba(255, 255, 255, 0.3);
			transform: translateY(-2px);
		}

		.action-btn.primary {
			background: white;
			color: #667eea;
			border-color: white;
		}

		.action-btn.primary:hover {
			background: #f8f9fa;
			box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
		}

		/* Stats Cards */
		.stats-card {
			background: white;
			border-radius: 20px;
			padding: 1.5rem;
			box-shadow: var(--card-shadow);
			transition: all 0.3s ease;
		}

		.stats-card:hover {
			transform: translateY(-5px);
			box-shadow: 0 15px 50px rgba(0, 0, 0, 0.12);
		}

		.stats-icon {
			width: 60px;
			height: 60px;
			border-radius: 16px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 1.5rem;
		}

		.stats-icon.purple {
			background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
			color: #667eea;
		}

		.stats-icon.green {
			background: linear-gradient(135deg, #11998e20 0%, #38ef7d20 100%);
			color: #11998e;
		}

		/* Topics Section */
		.topics-section {
			background: white;
			border-radius: 24px;
			box-shadow: var(--card-shadow);
			overflow: hidden;
		}

		.topics-header {
			padding: 1.5rem 2rem;
			border-bottom: 1px solid #f0f0f0;
			display: flex;
			justify-content: space-between;
			align-items: center;
			flex-wrap: wrap;
			gap: 1rem;
		}

		.topics-title {
			font-size: 1.5rem;
			font-weight: 700;
			color: #1e293b;
		}

		.add-topic-btn {
			background: var(--primary-gradient);
			color: white;
			border: none;
			padding: 0.75rem 1.5rem;
			border-radius: 12px;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.3s ease;
			display: flex;
			align-items: center;
			gap: 0.5rem;
		}

		.add-topic-btn:hover {
			transform: translateY(-2px);
			box-shadow: 0 8px 25px rgba(102, 126, 234, 0.35);
		}

		/* Empty State */
		.empty-state {
			padding: 4rem 2rem;
			text-align: center;
		}

		.empty-state-icon {
			width: 120px;
			height: 120px;
			background: linear-gradient(135deg, #f0f2f5 0%, #e4e8ed 100%);
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 0 auto 1.5rem;
			font-size: 3rem;
			color: #94a3b8;
		}

		.empty-state-title {
			font-size: 1.5rem;
			font-weight: 700;
			color: #475569;
			margin-bottom: 0.5rem;
		}

		.empty-state-desc {
			color: #94a3b8;
			margin-bottom: 1.5rem;
		}

		/* DataTable Overrides */
		.dataTables_wrapper {
			padding: 1.5rem;
		}

		/* Topic Row Actions */
		.topic-actions {
			display: flex;
			gap: 0.5rem;
			justify-content: flex-end;
		}

		.topic-action-btn {
			width: 40px;
			height: 40px;
			border-radius: 10px;
			display: flex;
			align-items: center;
			justify-content: center;
			transition: all 0.3s ease;
		}

		.topic-action-btn:hover {
			transform: scale(1.1);
		}

		/* New/First Time User Banner */
		.welcome-banner {
			background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
			border: 2px dashed #667eea50;
			border-radius: 16px;
			padding: 2rem;
			text-align: center;
			margin-bottom: 1.5rem;
		}

		.welcome-banner h3 {
			color: #667eea;
			margin-bottom: 0.5rem;
		}

		/* Responsive */
		@media (max-width: 768px) {
			.hero-section {
				padding: 1.5rem;
			}

			.hero-title {
				font-size: 1.75rem;
			}

			.session-badge {
				flex-direction: column;
				text-align: center;
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

		<!-- Hero Section -->
		<div class="hero-section">
			<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-4">
				<div>
					<div class="d-flex align-items-center gap-3 mb-3">
						<img src="./assets/medias/logos/android-chrome-192x192.png" alt="Logo" style="height: 50px;">
						<span class="hero-title">YUWA Vote</span>
					</div>
					<p class="hero-subtitle mb-3">สร้างโหวตและแชร์ลิ้งค์ได้ทันที ไม่ต้องสมัครสมาชิก</p>

					<div class="session-badge">
						<i class="fa-solid fa-key"></i>
						<span id="sessionKeyDisplay"><?php echo $short_key; ?></span>
						<button class="copy-btn" onclick="copyShareLink()">
							<i class="fa-solid fa-copy"></i>
							<span>Copy Link</span>
						</button>
					</div>
				</div>

				<div class="action-btn-group">
					<button class="action-btn primary" onclick="topicAdd()">
						<i class="fa-solid fa-plus"></i>
						สร้าง Topic ใหม่
					</button>
					<button class="action-btn" onclick="newWorkspace()">
						<i class="fa-solid fa-rotate"></i>
						Workspace ใหม่
					</button>
				</div>
			</div>
		</div>

		<!-- Stats Row -->
		<div class="row g-4 mb-4">
			<div class="col-md-6 col-lg-3">
				<div class="stats-card">
					<div class="d-flex align-items-center gap-3">
						<div class="stats-icon purple">
							<i class="fa-solid fa-list-check"></i>
						</div>
						<div>
							<div class="fs-3 fw-bold" id="topicCountDisplay"><?php echo $topic_count; ?></div>
							<div class="text-muted">Topics</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6 col-lg-3">
				<div class="stats-card">
					<div class="d-flex align-items-center gap-3">
						<div class="stats-icon green">
							<i class="fa-solid fa-share-nodes"></i>
						</div>
						<div>
							<div class="fs-3 fw-bold">∞</div>
							<div class="text-muted">Share ได้ไม่จำกัด</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Topics Section -->
		<div class="topics-section">
			<div class="topics-header">
				<h2 class="topics-title">
					<i class="fa-solid fa-inbox me-2 text-primary"></i>
					Topics ของคุณ
				</h2>
				<button class="add-topic-btn" onclick="topicAdd()">
					<i class="fa-solid fa-plus"></i>
					เพิ่ม Topic
				</button>
			</div>

			<?php if ($topic_count == 0): ?>
				<!-- Empty State -->
				<div class="empty-state">
					<div class="empty-state-icon">
						<i class="fa-solid fa-box-open"></i>
					</div>
					<h3 class="empty-state-title">ยังไม่มี Topic</h3>
					<p class="empty-state-desc">เริ่มสร้าง Topic แรกของคุณได้เลย!<br>กดปุ่ม "เพิ่ม Topic" เพื่อเริ่มต้น</p>
					<button class="add-topic-btn" onclick="topicAdd()">
						<i class="fa-solid fa-plus"></i>
						สร้าง Topic แรก
					</button>
				</div>
			<?php else: ?>
				<!-- Topics Table -->
				<div class="card-body p-8">
					<table class="table table-striped" id="topicsTable">
						<thead>
							<tr>
								<th>เวลาปิดโหวต</th>
								<th>ชื่อ Topic</th>
								<th class="text-end">จัดการ</th>
							</tr>
						</thead>
					</table>
				</div>
			<?php endif; ?>
		</div>

	</div>

	<!-- QR Code Modal -->
	<div class="modal fade" id="qrModal" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="qrModalLabel">QR Code สำหรับ Vote</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body text-center">

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="topicManageModal" tabindex="-1">
		<div class="modal-dialog modal-xl">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="topicManageTitle">จัดการ Topic</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body text-center">
					<div id="topic_manage_form"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-success" id="modalActionBtn"></button>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
				</div>
			</div>
		</div>
	</div>

	<?php require _WEBROOT_PATH_ . 'components/footer.php'; ?>
	<script src="./assets/plugins/custom/datatables/datatables.bundle.js"></script>
	<script src="./assets/plugins/custom/formrepeater/formrepeater.bundle.js"></script>

	<script>
		// Session Key for all requests
		const WORKSPACE_KEY = '<?php echo $current_session_key; ?>';
		const SHARE_URL = '<?php echo generateShareableLink($current_session_key); ?>';

		function copyShareLink() {
			// Use Clipboard API if available, otherwise fallback to textarea method
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(SHARE_URL).then(() => {
					Swal.fire({
						icon: 'success',
						title: 'Copied!',
						text: 'ลิ้งค์ถูก Copy แล้ว',
						timer: 1500,
						showConfirmButton: false
					});
				}).catch(() => {
					fallbackCopyText(SHARE_URL);
				});
			} else {
				fallbackCopyText(SHARE_URL);
			}
		}

		function fallbackCopyText(text) {
			const textArea = document.createElement('textarea');
			textArea.value = text;
			textArea.style.position = 'fixed';
			textArea.style.left = '-9999px';
			textArea.style.top = '-9999px';
			document.body.appendChild(textArea);
			textArea.focus();
			textArea.select();
			
			try {
				document.execCommand('copy');
				Swal.fire({
					icon: 'success',
					title: 'Copied!',
					text: 'ลิ้งค์ถูก Copy แล้ว',
					timer: 1500,
					showConfirmButton: false
				});
			} catch (err) {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'ไม่สามารถ Copy ได้ กรุณา Copy ด้วยตนเอง: ' + text,
				});
			}
			
			document.body.removeChild(textArea);
		}

		function newWorkspace() {
			Swal.fire({
				title: 'สร้าง Workspace ใหม่?',
				text: "คุณจะได้รับ Session Key ใหม่ และจะไม่สามารถเข้าถึง Topics เดิมได้ (ยกเว้นบันทึก Link ไว้)",
				icon: 'question',
				showCancelButton: true,
				confirmButtonText: 'สร้างใหม่',
				cancelButtonText: 'ยกเลิก',
				buttonsStyling: false,
				customClass: {
					confirmButton: 'btn btn-primary mx-2',
					cancelButton: 'btn btn-secondary mx-2'
				}
			}).then((result) => {
				if (result.isConfirmed) {
					window.location.href = './';
				}
			});
		}

		<?php if ($topic_count > 0): ?>
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
					pageLength: 10,
					language: {
						processing: "<span>กำลังโหลด..</span>",
						lengthMenu: "แสดง _MENU_ รายการต่อหน้า",
						zeroRecords: "ไม่มีข้อมูล",
						info: "หน้า _PAGE_ จาก _PAGES_",
						infoEmpty: "ไม่มีข้อมูล",
						infoFiltered: "(กรองทั้งหมด _MAX_ แถว)",
						search: "ค้นหา:"
					},
					order: [
						[0, 'desc']
					],
					ajax: {
						url: './actions/topics_datatable.php',
						type: 'POST',
						dataType: 'JSON',
						cache: false,
						data: function(data) {
							data.session_key = WORKSPACE_KEY;
						}
					},
					columns: [{
							data: 'expire_datetime',
							render: function(data) {
								const date = new Date(data);
								const now = new Date();
								const isExpired = date < now;
								const formattedDate = date.toLocaleString('th-TH');
								const badge = isExpired ? 
									'<span class="badge badge-light-danger ms-2">หมดเวลา</span>' : 
									'<span class="badge badge-light-success ms-2">Active</span>';
								return formattedDate + badge;
							}
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
							return `<div class="topic-actions">
										<button class="btn btn-icon btn-sm btn-light-warning topic-action-btn" onclick="topicEdit('${row.id}')" title="แก้ไข">
											<i class="fa-solid fa-pen fs-5"></i>
										</button>
										<button class="btn btn-icon btn-sm btn-light-success topic-action-btn" onclick="topicQRCode('${row.id}')" title="QR Code">
											<i class="fa-solid fa-qrcode fs-5"></i>
										</button>
										<a href="./monitor.php?key=${row.share_key}" class="btn btn-icon btn-sm btn-light-info topic-action-btn" title="ดูผลโหวต">
											<i class="fa-solid fa-chart-pie fs-5"></i>
										</a>
										<button class="btn btn-icon btn-sm btn-light-danger topic-action-btn" onclick="topicDelete('${row.id}')" title="ลบ">
											<i class="fa-solid fa-trash fs-5"></i>
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
		<?php endif; ?>

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
						session_key: WORKSPACE_KEY,
						topicTitle: $('#topicTitle').val(),
						expireDateTime: $('#expireDateTime').val(),
						choicesArr: choicesArr,
						displayMode: $('input[name="displayMode"]:checked').val(),
						showScore: $('#showScore').is(':checked') ? 1 : 0,
						isPublic: $('#isPublic').is(':checked') ? 1 : 0,
						voteMode: $('input[name="voteMode"]:checked').val(),
						maxChoices: $('#maxChoices').val()
					},
					success: function(data) {
						$('#topicManageModal').modal('hide');
						// Reload page to show new topic
						location.reload();
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
						session_key: WORKSPACE_KEY,
						topic_id: topic_id,
						topicTitle: $('#topicTitle').val(),
						expireDateTime: $('#expireDateTime').val(),
						choicesCurrentArr: choicesCurrentArr,
						choicesNewArr: choicesNewArr,
						displayMode: $('input[name="displayMode"]:checked').val(),
						showScore: $('#showScore').is(':checked') ? 1 : 0,
						isPublic: $('#isPublic').is(':checked') ? 1 : 0,
						voteMode: $('input[name="voteMode"]:checked').val(),
						maxChoices: $('#maxChoices').val()
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
							session_key: WORKSPACE_KEY,
							topic_id: topic_id,
						},
						dataType: 'JSON',
						success: function(result) {
							if (result.status === 'success') {
								Swal.fire({
									text: `ลบแล้ว !`,
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