<?php
require_once(__DIR__ . "/../../inc/config.php");

function downloadFile($url, $out)
{
    $fileHandle = fopen($out, 'w+');
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_FILE, $fileHandle);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);

    $exec = curl_exec($curl);
    curl_close($curl);
    fclose($fileHandle);

    if ($exec) {
        return true;
    } else {
        return false;
    }
}

function getDiff($from, $to)
{
    $fromFile = tempnam('/tmp/', 'MONDIFF');
    $toFile = tempnam('/tmp/', 'MONDIFF');

    downloadFile("http://blzddist1-a.akamaihd.net/".$from, $fromFile);
    downloadFile("http://blzddist1-a.akamaihd.net/".$to, $toFile);

    $cmd = "diff -u " . escapeshellarg($fromFile) . " " . escapeshellarg($toFile);
    $result = shell_exec($cmd);

    unlink($fromFile);
    unlink($toFile);

    return $result;
}

if(empty($_GET['from']) || empty($_GET['to']))
    die("Not enough information to diff");

$diff = getDiff($_GET['from'], $_GET['to']);
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

        var diffHtml = Diff2Html.getPrettyHtml(
            document.getElementById("rawdiff").innerHTML, {
                inputFormat: 'diff',
                showFiles: false,
                matching: 'lines',
                outputFormat: 'side-by-side'
            }
            );
        document.getElementById("diff").innerHTML = diffHtml;
    });
</script>
<div id='rawdiff' style='display: none'><?=$diff?></div>
<div id='diff'>Generating diff..</div>