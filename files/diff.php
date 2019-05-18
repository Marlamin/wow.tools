<?php
require_once("../inc/config.php");

if (empty($_GET['from']) || empty($_GET['to']) || empty($_GET['filedataid'])) {
    die("Not enough information!");
}

$diff_api_url = "http://wow.tools.local:8080/files/scripts/diff_api.php?from=" . $_GET['from'] . "&to=" . $_GET['to'] . "&filedataid=" . $_GET['filedataid'];

?>
<link rel="stylesheet" type="text/css" href="http://wow.tools.local:8080/css/diff2html.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript" src="http://wow.tools.local:8080/js/diff2html-ui.min.js"></script>
<script type="text/javascript" src="http://wow.tools.local:8080/js/diff2html.min.js"></script>

<script type="text/javascript" charset="utf-8">
    $(document).ready(function() {
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