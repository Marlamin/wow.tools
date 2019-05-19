<?php
require_once("../inc/config.php");

if (empty($_GET['from']) || empty($_GET['to']) || empty($_GET['filedataid'])) {
    die("Not enough information!");
}

$fq = $pdo->prepare("SELECT type FROM wow_rootfiles WHERE id = ?");
$fq->execute([$_GET['filedataid']]);
$row = $fq->fetch();
if(empty($row)){
    die("File not found in database or type is unknown!");
}

$diff_api_url = "/files/scripts/diff_api.php?from=" . $_GET['from'] . "&to=" . $_GET['to'] . "&filedataid=" . $_GET['filedataid'];

?>
<link rel="stylesheet" type="text/css" href="/css/diff2html.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript" src="/js/diff2html-ui.min.js"></script>
<script type="text/javascript" src="/js/diff2html.min.js"></script>

<script type="text/javascript" charset="utf-8">
    $(document).ready(function() {
        if(localStorage.getItem('theme') == "dark"){
            $('#previewModalContent').append('<link rel="stylesheet" type="text/css" href="/css/diff2html-dark.css?v= ' + Date.now() +'">');
        }
        $.get("<?= $diff_api_url ?>", function(data) {
            var diffHtml = Diff2Html.getPrettyHtml(
                data, {
                    inputFormat: 'diff',
                    showFiles: false,
                    matching: 'lines',
                    outputFormat: 'side-by-side'
                }
            );
            document.getElementById("diff").innerHTML = diffHtml;
        });
    });
</script>

<div id="diff"></div>