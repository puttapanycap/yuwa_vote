<?php

session_start();
define('_WEBROOT_PATH_', './');

// Connection Setup :: START
require _WEBROOT_PATH_ . '/helpers/load_env.php';
require _WEBROOT_PATH_ . '/helpers/functions.php';
$connections = getDatabaseConnections();
$vote_conn = $connections['vote'];
// Connection Setup :: END

if (!isset($_GET['key'])) {
	header('location: ' . _WEBROOT_PATH_);
	exit(0);
}

$topic_key = $_GET['key'];
$topic_sql = "SELECT
                    id,
                    topic_title,
                    expire_datetime,
                    member_id,
                    share_key,
                    COALESCE(show_score, 1) as show_score,
                    COALESCE(display_mode, 'card') as display_mode,
                    COALESCE(is_public, 0) as is_public
                FROM
                    vote_topics 
				WHERE
					share_key='$topic_key' ";
$topic_query = $vote_conn->query($topic_sql);
$topic_info = $topic_query->fetch_assoc();

// Check if topic exists
if (!$topic_info) {
	header('location: ' . _WEBROOT_PATH_);
	exit(0);
}

$is_public = $topic_info['is_public'];

$topic_id = $topic_info['id'];
$show_score = $topic_info['show_score'];
$display_mode = $topic_info['display_mode'];

$vote_conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<?php require _WEBROOT_PATH_ . 'components/head.html'; ?>
	<?php require _WEBROOT_PATH_ . 'components/script.html'; ?>
	<style>
		/* Monitor Page Enhanced Styles */
		.monitor-header {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			border-radius: 20px;
			box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
			padding: 1.5rem 2rem;
			color: white;
		}

		.monitor-header .topic-title {
			color: white;
			text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
		}

		.control-btn {
			width: 50px;
			height: 50px;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			transition: all 0.3s ease;
			box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
		}

		.control-btn:hover {
			transform: translateY(-3px);
			box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
		}

		.control-btn.active {
			animation: pulse-btn 2s infinite;
		}

		@keyframes pulse-btn {

			0%,
			100% {
				box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
			}

			50% {
				box-shadow: 0 4px 25px rgba(0, 0, 0, 0.4);
			}
		}

		/* Vote Cards */
		.vote-card {
			transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
			border: none;
			overflow: hidden;
		}

		.vote-card:hover {
			transform: translateY(-10px) scale(1.02);
			box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
		}

		.vote-card.gradient-gold {
			background: linear-gradient(135deg, #fff9e6 0%, #ffe066 100%);
		}

		.vote-card.gradient-silver {
			background: linear-gradient(135deg, #f8f9fa 0%, #ced4da 100%);
		}

		.vote-card.gradient-bronze {
			background: linear-gradient(135deg, #fff5eb 0%, #f5c28e 100%);
		}

		/* Score Circle */
		.score-circle {
			background: linear-gradient(145deg, #f0f0f0 0%, #ffffff 100%);
			box-shadow: inset 0 4px 15px rgba(0, 0, 0, 0.08),
				0 8px 20px rgba(0, 0, 0, 0.06);
			transition: all 0.3s ease;
		}

		.vote-card:hover .score-circle {
			transform: scale(1.05);
		}

		.star-pulse {
			animation: star-glow 2s ease-in-out infinite;
		}

		@keyframes star-glow {

			0%,
			100% {
				filter: drop-shadow(0 0 5px rgba(255, 193, 7, 0.5));
				transform: scale(1);
			}

			50% {
				filter: drop-shadow(0 0 20px rgba(255, 193, 7, 0.8));
				transform: scale(1.1);
			}
		}

		.score-value {
			background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8a 100%);
			-webkit-background-clip: text;
			-webkit-text-fill-color: transparent;
			background-clip: text;
		}

		.score-hidden {
			animation: fade-pulse 2s ease-in-out infinite;
		}

		@keyframes fade-pulse {

			0%,
			100% {
				opacity: 0.5;
			}

			50% {
				opacity: 1;
			}
		}

		/* Voting Cards Container */
		#topic_votes {
			display: flex;
			flex-wrap: wrap;
			justify-content: center;
			gap: 1.5rem;
		}

		/* List View Styles */
		.vote-list-item {
			transition: all 0.3s ease;
		}

		.vote-list-item:hover {
			background: rgba(102, 126, 234, 0.05) !important;
			transform: scale(1.01);
		}

		.vote-list-item td {
			vertical-align: middle;
		}

		/* Total Count Badge */
		.total-count-badge {
			background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
			padding: 0.5rem 1.5rem;
			border-radius: 30px;
			color: white;
			font-weight: bold;
			box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
		}

		/* Display Mode Badge */
		.display-mode-badge {
			background: rgba(255, 255, 255, 0.2);
			padding: 0.25rem 0.75rem;
			border-radius: 15px;
			font-size: 0.8rem;
		}

		/* Ribbon enhancements */
		.ribbon-label {
			box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
		}
	</style>
</head>

<body>
	<!--begin::Theme mode setup on page load-->
	<script>
		document.documentElement.setAttribute("data-bs-theme", "light");
	</script>


	<div class="container flex-column-fluid my-4">

		<!-- Enhanced Header Card -->
		<div class="w-100 monitor-header d-flex flex-row flex-wrap align-items-center justify-content-between mb-4">
			<div class="d-flex flex-column flex-md-row align-items-center gap-3">
				<span
					class="fs-2hx fw-bold topic-title"><?php echo htmlspecialchars($topic_info['topic_title']) ?></span>
				<div class="d-flex flex-row gap-2">
					<span id="displayModeBadge" class="display-mode-badge">
						<i class="fa-solid <?php echo $display_mode === 'list' ? 'fa-list' : 'fa-grid-2' ?> me-1"></i>
						<?php echo $display_mode === 'list' ? 'List' : 'Card' ?>
					</span>
					<span id="showScoreBadge" class="display-mode-badge">
						<i class="fa-solid <?php echo $show_score ? 'fa-eye' : 'fa-eye-slash' ?> me-1"></i>
						<?php echo $show_score ? 'แสดงคะแนน' : 'ซ่อนคะแนน' ?>
					</span>
					<span id="publicBadge"
						class="display-mode-badge bg-success <?php echo $is_public ? '' : 'd-none' ?>">
						<i class="fa-solid fa-globe me-1"></i>
						สาธารณะ
					</span>
				</div>
			</div>

			<div class="d-flex flex-row gap-3 mt-3 mt-md-0">
				<!-- Play/Pause Button -->
				<button id="togglePlay" class="control-btn btn btn-dark active" onclick="stopCount()">
					<i class="fa-solid fa-pause fs-4"></i>
				</button>
			</div>
		</div>

		<!-- Voting Cards/List Container -->
		<div class="d-flex flex-row flex-wrap flex-center gap-4" id="topic_votes">
			<!-- Cards/List will be loaded via AJAX -->
		</div>

	</div>


	<?php require _WEBROOT_PATH_ . 'components/footer.php'; ?>

	<script>
		let intervalId = null;
		let currentShowScore = <?php echo $show_score ? 'true' : 'false' ?>;
		let currentDisplayMode = '<?php echo $display_mode ?>';
		let currentIsPublic = <?php echo $is_public ? 'true' : 'false' ?>;
		const topicId = '<?php echo $topic_id ?>';

		function updateSettingsBadges(settings) {
			// Update display mode badge
			const displayModeIcon = settings.display_mode === 'list' ? 'fa-list' : 'fa-grid-2';
			const displayModeText = settings.display_mode === 'list' ? 'List' : 'Card';
			$('#displayModeBadge').html(`<i class="fa-solid ${displayModeIcon} me-1"></i>${displayModeText}`);

			// Update show score badge
			const showScoreIcon = settings.show_score ? 'fa-eye' : 'fa-eye-slash';
			const showScoreText = settings.show_score ? 'แสดงคะแนน' : 'ซ่อนคะแนน';
			$('#showScoreBadge').html(`<i class="fa-solid ${showScoreIcon} me-1"></i>${showScoreText}`);

			// Update public badge visibility
			if (settings.is_public) {
				$('#publicBadge').removeClass('d-none').show();
			} else {
				$('#publicBadge').addClass('d-none').hide();
			}

			// Update local state
			currentShowScore = settings.show_score;
			currentDisplayMode = settings.display_mode;
			currentIsPublic = settings.is_public;
		}

		function initCount() {
			$.ajax({
				url: './actions/choics_realtime.php',
				type: 'POST',
				data: {
					topic_id: topicId
				},
				dataType: 'JSON',
				success: function (response) {
					if (response.success) {
						// Update HTML content
						$('#topic_votes').html(response.html);

						// Update settings badges in realtime
						updateSettingsBadges(response.settings);

						// Reinitialize countup animations
						if (typeof KTUtil !== 'undefined') {
							$('[data-kt-countup="true"]').each(function () {
								if (!$(this).hasClass('counted')) {
									$(this).addClass('counted');
								}
							});
						}
					}
				}
			});
		}

		function startCount() {
			if (!intervalId) {
				intervalId = setInterval(initCount, 5000);
				$('#togglePlay')
					.attr('onclick', 'stopCount()')
					.removeClass('btn-success')
					.addClass('btn-dark active')
					.html('<i class="fa-solid fa-pause fs-4"></i>');
			}
		}

		function stopCount() {
			if (intervalId) {
				clearInterval(intervalId);
				intervalId = null;
				$('#togglePlay')
					.attr('onclick', 'startCount()')
					.removeClass('btn-dark active')
					.addClass('btn-success')
					.html('<i class="fa-solid fa-play fs-4"></i>');
			}
		}

		$(document).ready(function () {
			initCount();
			startCount();
		});
	</script>
</body>

</html>