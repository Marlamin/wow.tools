<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

function importDB2($name, $outdir, $fields)
{
    global $pdo;
    $db2 = "http://127.0.0.1:5000/api/export/?name=" . $name . "&build=" . $outdir . "&useHotfixes=true&t=" . strtotime("now");
    $csv = "/tmp/" . $name . ".csv";
    if (file_exists($csv)) {
        unlink($csv);
    }
    $outputdump = shell_exec("/usr/bin/curl " . escapeshellarg($db2) . " -o " . escapeshellarg($csv) . " 2>&1");
    if (!file_exists($csv)) {
        echo "An error occured during " . $name . " import: " . $outputdump;
    } else {
        echo "[DB2 import] Writing " . $name . " (" . $outdir . ")..";
        $pdo->exec("
            LOAD DATA LOCAL INFILE '" . $csv . "'
            INTO TABLE `wowdata`." . $name . "
            FIELDS OPTIONALLY ENCLOSED BY '\"' 
            TERMINATED BY ',' ESCAPED BY '\b'
            LINES TERMINATED BY '\n'
            IGNORE 1 LINES
            " . $fields . "
        ");

        echo "..done!\n";
    }
}

include(__DIR__ . "/../../inc/config.php");

$q = $pdo->query("SELECT description FROM wow_buildconfig WHERE product LIKE 'wow%' AND ID > 1575 ORDER BY description DESC LIMIT 3");
while($row = $q->fetch(PDO::FETCH_ASSOC)){
    $rawdesc = str_replace("WOW-", "", $row['description']);
    $build = substr($rawdesc, 0, 5);
    $rawdesc = str_replace(array($build, "patch"), "", $rawdesc);
    $descexpl = explode("_", $rawdesc);
    $outdir = $descexpl[0] . "." . $build;

    importDB2("broadcasttext", $outdir, "(@TextLang, @Text1Lang, @ID, @LanguageID, @ConditionID, @EmotesID, @Flags, @ChatBubbleDurationMS, @SoundKit0, @SoundKit1) SET ID = @ID, Text = @TextLang, Text1 = @Text1Lang, SoundKit0 = @SoundKit0, SoundKit1 = @SoundKit1");
}
