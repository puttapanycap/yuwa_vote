<?php

session_start();
header("Content-Type: application/json; charset=utf-8");
define('_WEBROOT_PATH_', '../');

// Connection Setup :: START
require _WEBROOT_PATH_ . '/helpers/load_env.php';

require _WEBROOT_PATH_ . '/helpers/functions.php';
$connections = getDatabaseConnections();
$vote_conn = $connections['vote'];
// Connection Setup :: END

$topic_id = $_POST['topic_id'];
$response = [];
$topic_sql = "  SELECT
                    topic_title,
                    expire_datetime,
                    member_id,
                    share_key
                FROM
                    vote_topics
                WHERE
                    id='$topic_id' ";
$topic_query = $vote_conn->query($topic_sql);
$topic_row = $topic_query->fetch_assoc();
$expire_datetime = $topic_row['expire_datetime'];
$topic_title = $topic_row['topic_title'];

$url = $_ENV['VOTE_WEB_URL'] . 'vote.php?key=' . $topic_row['share_key'];

use chillerlan\QRCode\QRCode;
$qrCodeSrc = (new QRCode)->render($url);

?>

<div class="d-flex flex-column align-items-center gap-3 w-100">
    <h2 class="text-center"><?php echo $topic_title ?></h2>
    <div class="d-flex flex-center border border-1 border-solid border-gray-400 rounded-3 p-1">
        <img class="w-250px h-250px" src="<?php echo $qrCodeSrc ?>" alt="">
    </div>
    <div class="input-group">
        <input id="clipboard_url" type="text" class="form-control" placeholder="https://example.com/" value="<?php echo $url ?>" readonly />
        <button class="btn btn-icon btn-secondary" data-clipboard-target="#clipboard_url">
            <i class="fa-solid fa-clone fs-3"></i>
        </button>
        <a class="btn btn-icon btn-secondary" target="_blank" href="<?php echo $url ?>">
            <i class="fa-solid fa-arrow-up-right-from-square fs-3"></i>
        </a>
    </div>
</div>

<script>
    // Select elements
    

    // Init clipboard -- for more info, please read the offical documentation: https://clipboardjs.com/
    if (typeof ClipboardJS !== 'undefined') {
        const target = document.getElementById('clipboard_url');
        const button = target.nextElementSibling;
        var clipboard = new ClipboardJS(button, {
            target: target,
            text: function() {
                return target.value;
            }
        });

        // Success action handler
        clipboard.on('success', function(e) {

            navigator.clipboard.writeText(e.text);
            
            const currentLabel = button.innerHTML;

            // Exit label update when already in progress
            if (button.innerHTML === '<i class="fa-solid fa-check fs-3"></i>') {
                return;
            }

            // Update button label
            button.innerHTML = '<i class="fa-solid fa-check fs-3"></i>';

            // Revert button label after 3 seconds
            setTimeout(function() {
                button.innerHTML = currentLabel;
            }, 1500)
        });
    }
</script>