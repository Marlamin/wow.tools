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

function getDiff($fromFile, $toFile)
{
    $cmd = "diff -u " . escapeshellarg($fromFile) . " " . escapeshellarg($toFile);
    $result = shell_exec($cmd);

    return $result;
}

function getParsedDiff($fromFile, $toFile){
    $fromFileContent = file_get_contents($fromFile);
    $toFileContent = file_get_contents($toFile);

    switch(substr($fromFileContent, 0, 5)){
        case "# Bui":
        case "# CDN":
        case "# Pat":
            $from = parseConfig($fromFile);
            if(!empty($from['archives'])){
                $from['archives'] = array_fill_keys(explode(" ", $from['archives']), '');
            }

             if(!empty($from['patch-archives'])){
                $from['patch-archives'] = array_fill_keys(explode(" ", $from['patch-archives']), '');
            }

            if(!empty($from['archives-index-size'])){
                $from['archives-index-size'] = explode(" ", $from['archives-index-size']);
            }

            if(!empty($from['patch-archives-index-size'])){
                $from['patch-archives-index-size'] = explode(" ", $from['patch-archives-index-size']);
            }

            unset($from['original-filename']);
        break;
    }

    switch(substr($toFileContent, 0, 5)){
        case "# Bui":
        case "# CDN":
        case "# Pat":
            $to = parseConfig($toFile);
            if(!empty($to['archives'])){
                $to['archives'] = array_fill_keys(explode(" ", $to['archives']), '');
            }

             if(!empty($to['patch-archives'])){
                $to['patch-archives'] = array_fill_keys(explode(" ", $to['patch-archives']), '');
            }

            if(!empty($to['archives-index-size'])){
                $to['archives-index-size'] = explode(" ", $to['archives-index-size']);
            }

            if(!empty($to['patch-archives-index-size'])){
                $to['patch-archives-index-size'] = explode(" ", $to['patch-archives-index-size']);
            }

            unset($to['original-filename']);
        break;
    }

    if(empty($from)){
        $from = json_decode($fromFileContent, true);
    }

    if(empty($to)){
        $to = json_decode($toFileContent, true);
    }

    if(!$from || !$to){
        $diffs = "Unsupported";
    }else{
        $diffs = CompareArrays::Diff($from, $to);
        if(!empty($diffs)){
            $diffs = CompareArrays::Flatten($diffs);
        }
    }

    return $diffs;

}

if(empty($_GET['from']) || empty($_GET['to']))
    die("Not enough information to diff");

if(substr($_GET['from'], 0, 3) != "tpr" || !ctype_xdigit(substr($_GET['from'], -32))){
    die("Invalid from URL");
}

if(substr($_GET['to'], 0, 3) != "tpr" || !ctype_xdigit(substr($_GET['to'], -32))){
    die("Invalid to URL");
}

$fromFile = tempnam('/tmp/', 'MONDIFF');
$toFile = tempnam('/tmp/', 'MONDIFF');

downloadFile("http://blzddist1-a.akamaihd.net/".$_GET['from'], $fromFile);
downloadFile("http://blzddist1-a.akamaihd.net/".$_GET['to'], $toFile);

$diff = getDiff($fromFile, $toFile);
$parsedDiffs = getParsedDiff($fromFile, $toFile);

unlink($fromFile);
unlink($toFile);
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
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="sbs-tab" data-toggle="tab" href="#sbs" role="tab" aria-controls="sbs" aria-selected="true">Side-by-Side</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="parsed-tab" data-toggle="tab" href="#parsed" role="tab" aria-controls="parsed" aria-selected="false">Diff</a>
        </li>
    </ul>
    <div class="tab-content" style='width: 100%'>
        <div class="tab-pane show active" id="sbs" role="tabpanel" aria-labelledby="sbs-tab">
            <div class='row'>
                <div id='rawdiff' style='display: none'><?=$diff?></div>
                <div id='diff' style='width: 100%;'>Generating diff..</div>
            </div>
        </div>
        <div class="tab-pane" id="parsed" role="tabpanel" aria-labelledby="parsed-tab">
               <div id="jsondiff">
                <table class='table table-sm table-striped'>
                    <?php
                    if($parsedDiffs == "Unsupported"){
                        echo $parsedDiffs;
                    }else{
                        $difftext = "";
                        $color = "";
                        foreach($parsedDiffs as $name => $parsedDiff){
                            switch($parsedDiff->Type){
                                case "added":
                                    $icon = 'plus';
                                    $color = 'success';
                                break;
                                case "modified":
                                    $icon = 'pencil';
                                    $color = 'warning';
                                break;
                                case "removed":
                                    $icon = 'times';
                                    $color = 'danger';
                                break;
                            }
                            
                            echo "<tr><td class='text-" . $color . "'><i class='fa fa-".$icon."'></i></td><td>".$name."</td><td>".$parsedDiff->OldValue."</td><td>".$parsedDiff->NewValue."</td><td></td></tr>";
                        }
                    }
                   
                    ?>
            </table>
        </div>
    </div>
    </div>
