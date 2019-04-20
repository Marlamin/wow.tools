<?php
include("../../inc/config.php");

$q = $pdo->query("SELECT path, name FROM `wowdata`.manifestinterfacedata");

$files = array();
while($row = $q->fetch(PDO::FETCH_ASSOC)){
	$files[] = $row['path'].$row['name'];
}

$files = array_unique($files);

$tmpfname = tempnam("/tmp", "bnetlistfile");
$tmpfile = fopen($tmpfname, "w");

foreach($files as $file){
	if(empty(trim($file))) continue;
	if(strpos($file, " (in: ") !== false){
		$expl = explode(" (in: ", $file);
		$file = $expl[0];
	}

	$file = trim($file);
	$file = strtolower(str_replace("\\", "/", $file));

	fwrite($tmpfile, $file."\n");
}

fclose($tmpfile);

$cmd = "cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll calchashlistfile ".escapeshellarg($tmpfname);
$output = explode("\n", shell_exec($cmd));

$qt = $pdo->prepare("SELECT filename FROM wow_rootfiles WHERE lookup = ?");
$addq = $pdo->prepare("UPDATE wow_rootfiles SET filename = ?, verified = 1 WHERE lookup = ?");
$numadded = 0;
foreach($output as $line){
	$expl = explode(" = ", trim($line));
	if(count($expl) < 2) continue;

	$qt->execute([$expl[1]]);
	$row = $qt->fetch();

	if(!empty($row)){
		if(empty($row['filename'])){
			$addq->execute([$expl[0], $expl[1]]);
			echo "Added ".$expl[0]." (".$expl[1].")<br>";
			$numadded++;
		}
		$validfiles[] = $expl[0];
	}else{
		$invalidfiles[] = $expl[0];
	}
}

if($numadded > 0){
	$memcached->flush();
	$lq = $pdo->prepare("INSERT INTO wow_namelog (userid, userip, numadded) VALUES (:id, :ip, :numadded)");
	$lq->bindValue(':id', 2, PDO::PARAM_INT);
	$lq->bindValue(':ip', "127.0.0.1");
	$lq->bindParam(':numadded', $numadded);
	$lq->execute();
}

unlink($tmpfname);