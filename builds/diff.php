<?php

require_once(__DIR__ . "/../inc/header.php");

if(!empty($_GET['root1']) && !empty($_GET['root2'])){
    $bc1['root_cdn'] = $_GET['root1'];
    $bc1['description'] = "Manual 1";
    $bc2['root_cdn'] = $_GET['root2'];
    $bc2['description'] = "Manual 2";
}else{
    if (empty($_GET['from']) || empty($_GET['to'])) {
        die("Missing from and to buildconfig arguments in URL.");
    }
    $bc1 = getBuildConfigByBuildConfigHash($_GET['from']);
    $bc2 = getBuildConfigByBuildConfigHash($_GET['to']);
    if (empty($bc1) || empty($bc2)) {
        die("Invalid builds!");
    }
}

?>
<div class='container-fluid' id='diffContainer'>
<h3>Diff between <?=$bc1['description']?> and <?=$bc2['description']?></h3>
<pre>
<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:5005/casc/root/diff?from=" . $bc1['root_cdn'] . "&to=" . $bc2['root_cdn']) . "&cb= " . strtotime("now");
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);
curl_close($ch);
print_r($data);
if ($data == "") {
    echo "No content differences found or an error occurred during diffing. Changes in executables and other non in-game content are not detected by this page.";
}
?>
</pre>
</div>
<?php
require_once(__DIR__ . "/../inc/footer.php");
?>
