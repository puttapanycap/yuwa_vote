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
$nclass_arr = [null, 'danger', 'warning', 'success', 'dark', 'dark'];
foreach ($choices as $choice) :
?>
    <div class="card rounded-4 shadow-sm">
        <div class="card-header min-h-50px px-4 ribbon ribbon-top ribbon-vertical">
            <?php if (in_array($number, [1, 2, 3, 4, 5])) : ?>
                <div class="ribbon-label bg-<?php echo $nclass_arr[$number] ?>">
                    <span class="fw-bold fs-2"><?php echo $number ?></span>
                </div>
            <?php endif; ?>
            <div class="card-title fw-bold"><?php echo $choice['choice_title'] ?></div>
        </div>
        <div class="card-body p-3">
            <div class="d-flex flex-column flex-center h-200px w-200px bg-light rounded-circle">
                <i class="ki-solid ki-star fs-4hx text-warning"></i>
                <div class="fs-3hx fw-bold text-center min-w-70px lh-1" data-kt-countup="true" data-kt-countup-value="<?php echo $choice['score'] ?>"><?php echo $choice['score'] ?></div>
                <span class="text-center text-gray-600 fw-semibold fs-1 lh-1">
                    คะแนน
                </span>
            </div>
        </div>
    </div>
<?php
    $number++;
endforeach;
?>