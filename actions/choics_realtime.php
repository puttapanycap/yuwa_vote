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

// Fetch current settings from database for realtime updates
$settings_sql = "SELECT 
                    COALESCE(show_score, 1) as show_score,
                    COALESCE(display_mode, 'card') as display_mode,
                    COALESCE(is_public, 0) as is_public,
                    topic_title
                FROM vote_topics 
                WHERE id = '$topic_id'";
$settings_query = $vote_conn->query($settings_sql);
$topic_settings = $settings_query->fetch_assoc();

$show_score = $topic_settings ? intval($topic_settings['show_score']) : 1;
$display_mode = $topic_settings ? $topic_settings['display_mode'] : 'card';
$is_public = $topic_settings ? intval($topic_settings['is_public']) : 0;
$topic_title = $topic_settings ? $topic_settings['topic_title'] : '';

$choices_sql = "SELECT
				ch.choice_title,
				COUNT( rs.id ) AS score 
			FROM
				vote_choices AS ch
				LEFT JOIN vote_results AS rs ON ch.id = rs.choice_id 
			WHERE
				ch.topic_id = '$topic_id'
			GROUP BY
				ch.id 
			ORDER BY
				score DESC,
				ch.choice_sort ASC";
$choices_query = $vote_conn->query($choices_sql);
$choices = [];
while ($choice_row = $choices_query->fetch_assoc()) {
    $choices[] = $choice_row;
}

$vote_conn->close();

$number = 1;
$ribbon_colors = [null, 'danger', 'warning', 'success', 'info', 'primary'];
$gradient_classes = ['gradient-gold', 'gradient-silver', 'gradient-bronze', '', ''];

// Start output buffering to capture HTML
ob_start();

// ========== LIST VIEW ==========
if ($display_mode === 'list'):
    ?>
    <div class="w-100">
        <table class="table table-rounded table-hover table-striped border gy-4 gs-4">
            <thead>
                <tr class="fw-bold fs-4 text-gray-800 border-bottom border-gray-200">
                    <th class="text-center" style="width: 60px;">อันดับ</th>
                    <th>ตัวเลือก</th>
                    <th class="text-center" style="width: 150px;">คะแนน</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($choices as $choice):
                    $ribbon_class = isset($ribbon_colors[$number]) ? $ribbon_colors[$number] : 'secondary';
                    ?>
                    <tr class="vote-list-item">
                        <td class="text-center">
                            <span class="badge badge-circle badge-lg bg-<?php echo $ribbon_class ?> text-white fs-4 fw-bold">
                                <?php echo $number ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="ki-solid ki-star fs-2x text-warning me-3 star-pulse"></i>
                                <span class="fs-3 fw-bold"><?php echo htmlspecialchars($choice['choice_title']) ?></span>
                            </div>
                        </td>
                        <td class="text-center">
                            <?php if ($show_score): ?>
                                <span class="fs-2x fw-bold text-primary score-value" data-kt-countup="true"
                                    data-kt-countup-value="<?php echo $choice['score'] ?>">
                                    <?php echo $choice['score'] ?>
                                </span>
                            <?php else: ?>
                                <span class="score-hidden">
                                    <i class="fa-solid fa-eye-slash fs-2 text-gray-400"></i>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                    $number++;
                endforeach;
                ?>
            </tbody>
        </table>
    </div>
    <?php

    // ========== CARD VIEW ==========
else:
    foreach ($choices as $choice):
        $ribbon_class = isset($ribbon_colors[$number]) ? $ribbon_colors[$number] : 'dark';
        $gradient_class = isset($gradient_classes[$number - 1]) ? $gradient_classes[$number - 1] : '';
        ?>
        <div class="vote-card card rounded-4 shadow-sm <?php echo $gradient_class ?>">
            <div class="card-header min-h-50px px-4 ribbon ribbon-top ribbon-vertical">
                <?php if (in_array($number, [1, 2, 3, 4, 5])): ?>
                    <div class="ribbon-label bg-<?php echo $ribbon_class ?>">
                        <span class="fw-bold fs-2"><?php echo $number ?></span>
                    </div>
                <?php endif; ?>
                <div class="card-title fw-bold fs-4"><?php echo htmlspecialchars($choice['choice_title']) ?></div>
            </div>
            <div class="card-body p-3">
                <div class="score-circle d-flex flex-column flex-center h-200px w-200px rounded-circle">
                    <i class="ki-solid ki-star fs-4hx text-warning star-pulse"></i>
                    <?php if ($show_score): ?>
                        <div class="fs-3hx fw-bold text-center min-w-70px lh-1 score-value" data-kt-countup="true"
                            data-kt-countup-value="<?php echo $choice['score'] ?>"><?php echo $choice['score'] ?></div>
                        <span class="text-center text-gray-600 fw-semibold fs-1 lh-1">คะแนน</span>
                    <?php else: ?>
                        <div class="fs-3hx fw-bold text-center min-w-70px lh-1 score-hidden">
                            <i class="fa-solid fa-eye-slash fs-2hx text-gray-400"></i>
                        </div>
                        <span class="text-center text-gray-400 fw-semibold fs-3 lh-1">ซ่อนคะแนน</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        $number++;
    endforeach;
endif;

// Get the HTML output
$html_output = ob_get_clean();

// Return JSON response with settings and HTML
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'settings' => [
        'show_score' => $show_score,
        'display_mode' => $display_mode,
        'is_public' => $is_public,
        'topic_title' => $topic_title
    ],
    'html' => $html_output
]);
?>