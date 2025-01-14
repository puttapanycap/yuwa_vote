<?php

session_start();
define('_WEBROOT_PATH_', './');

// Connection Setup :: START
require _WEBROOT_PATH_ . '/helpers/load_env.php';
require _WEBROOT_PATH_ . '/helpers/functions.php';
$connections = getDatabaseConnections();
$vote_conn = $connections['vote'];
// Connection Setup :: END

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
	header('location: ' . _WEBROOT_PATH_ . 'login.php');
	exit(0);
}

if (!isHasMember($_SESSION['user_id'], $_SESSION['user_name'])) {
	header('location: ' . _WEBROOT_PATH_ . 'login.php');
	exit(0);
}

if (!isset($_GET['key'])) {
	header('location: ' . _WEBROOT_PATH_);
	exit(0);
}

$topic_key = $_GET['key'];
$topic_sql = "  SELECT
                    id,
                    topic_title,
                    expire_datetime,
                    member_id,
                    share_key
                FROM
                    vote_topics 
				WHERE
					share_key='$topic_key' ";
$topic_query = $vote_conn->query($topic_sql);
$topic_info = $topic_query->fetch_assoc();
$topic_id = $topic_info['id'];

$vote_conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<?php require _WEBROOT_PATH_ . 'components/head.html'; ?>
	<?php require _WEBROOT_PATH_ . 'components/script.html'; ?>
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

		<div class="w-100 card p-4 d-flex flex-row flex-center mb-4">
			<span class="fs-2hx fw-bold text-center me-4"><?php echo $topic_info['topic_title'] ?></span>
			<button id="togglePlay" class="btn btn-dark btn-icon" onclick=""></button>
		</div>

		<div class="d-flex flex-row flex-wrap flex-center gap-4" id="topic_votes">

		</div>

	</div>


	<?php require _WEBROOT_PATH_ . 'components/footer.php'; ?>

	<script>
		let intervalId = null; // To store the interval ID

		function initCount() {
			$('#topic_votes').html('');
			$.ajax({
				url: './actions/choics_realtime.php',
				type: 'POST',
				data: {
					topic_id: '<?php echo $topic_id ?>'
				},
				dataType: 'HTML',
				success: function(result_html) {
					$('#topic_votes').html(result_html);
				}
			});
		}

		function startCount() {
			if (!intervalId) {
				intervalId = setInterval(initCount, 5000);
				$('#togglePlay').attr('onclick', 'stopCount()');
				$('#togglePlay').attr('class', 'btn btn-sm btn-icon btn-dark');
				$('#togglePlay').html(`<i class="fa-solid fa-pause fs-2"></i>`);
			}
		}

		function stopCount() {
			if (intervalId) {
				clearInterval(intervalId);
				intervalId = null;
				$('#togglePlay').attr('onclick', 'startCount()');
				$('#togglePlay').attr('class', 'btn btn-sm btn-icon btn-success');
				$('#togglePlay').html(`<i class="fa-solid fa-play fs-2"></i>`);
			}
		}

		$(document).ready(function() {
			initCount();
			startCount();
		})
	</script>
</body>

</html>