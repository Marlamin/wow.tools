<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");

include("../../inc/config.php");

if(empty($argv[1])){
	// Full run
	$q = $pdo->query("SELECT root_cdn, description FROM wow_buildconfig WHERE processed = 0 GROUP BY `root_cdn` ORDER BY description ASC");
	$processedRootFiles = array();
	$roots = $q->fetchAll();
	$q->closeCursor();
	foreach($roots as $row){
		if(in_array($row['root_cdn'], $processedRootFiles)){ continue; }

		processRoot($row['root_cdn'], $row['description']);
		$processedRootFiles[] = $row['root_cdn'];

		$pq = $pdo->prepare("UPDATE wow_buildconfig SET processed = 1 WHERE root_cdn = :root");
		$pq->execute([$row['root_cdn']]);
		$pq->closeCursor();


		// Invalidate total file count as it has likely changed
		// $memcached->delete("files.total");
	}
}else{
	// Partial run
	$q = $pdo->prepare("SELECT description FROM wow_buildconfig WHERE root_cdn = :root");
	$q->bindParam(":root", $argv[1]);
	$q->execute();
	$row = $q->fetch();
	processRoot($argv[1], $row['description']);
}

function processRoot($root, $build){
	global $pdo;

	if(empty(trim($root))){
		echo "No root known for this build! Skipping..";
		return;
	}

	echo "Processing ".$build."\n";

	if(!file_exists("/home/wow/buildbackup/manifests/".$root.".txt")){
		echo "	Dumping manifest..";
		$output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet /home/wow/buildbackup/BuildBackup.dll dumproot2 ".$root." > /home/wow/buildbackup/manifests/".$root.".txt");
		echo "..done!\n";
	}else{
		echo "	Manifest already dumped, skipping..\n";
	}

	echo "	Writing rootfiles..";
	$q = $pdo->exec("LOAD DATA LOCAL INFILE '/home/wow/buildbackup/manifests/".$root.".txt' INTO TABLE wow_rootfiles
		FIELDS TERMINATED BY ';' LINES TERMINATED BY '\n'
		(@filename, @lookup, @filedataid, @contenthash) SET id=@filedataid, lookup=@lookup, filename=@filename
	");
	echo "..done!\n";

	$pdo->query("UPDATE wow_rootfiles SET filename = NULL WHERE filename = ' '");

	echo "	Writing content hashes..";
	$pdo->exec("LOAD DATA LOCAL INFILE '/home/wow/buildbackup/manifests/".$root.".txt' INTO TABLE wow_rootfiles_chashes
		FIELDS TERMINATED BY ';' LINES TERMINATED BY '\n'
		(@filename, @lookup, @filedataid, @contenthash) SET filedataid=@filedataid, root_cdn='".$root."', contenthash=@contenthash
	");
	echo "..done!\n";

	// $root5 = substr($root, 0, 5);

	// echo "	Writing available files for build..";

	// // Temp table stuff
	// $pdo->exec("DROP TABLE IF EXISTS `wow_rootfiles_available_".$root5."`");
	// $pdo->exec("CREATE TABLE `wow_rootfiles_available_".$root5."` (
	//   `filedataid` int(11) NOT NULL
	// ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

	// $pdo->exec("ALTER TABLE `wow_rootfiles_available_".$root5."`
	//   ADD UNIQUE KEY `filedataid_2` (`filedataid`),
	//   ADD KEY `filedataid` (`filedataid`);");

	// $pdo->exec("LOAD DATA LOCAL INFILE '/home/wow/buildbackup/manifests/".$root.".txt' INTO TABLE wow_rootfiles_available_".$root5."
	// 	FIELDS TERMINATED BY ';' LINES TERMINATED BY '\n'
	// 	(@filename, @lookup, @filedataid, @contenthash) SET filedataid=@filedataid
	// ");

	// // Merge into master table
	// $root8 = substr($root, 0, 8);
	// $root8dec = hexdec($root8);

	// $q = $pdo->prepare("SELECT id FROM wow_rootfiles_available_roots WHERE root8 = :root8dec");
	// $q->bindParam(":root8dec", $root8dec);
	// $q->execute();
	// $res = $q->fetchAll();

	// if(count($res)){
	// 	$q2 = $pdo->prepare("INSERT INTO wow_rootfiles_available_roots (root8) VALUES (:root8dec)");
	// 	$q2->bindParam(":root8dec", $root8dec);
	// 	$q2->execute();
	// 	$rootid = $pdo->lastInsertId();
	// }else{
	// 	$rootid = $res[0]['id'];
	// }

	// $numq = $pdo->prepare("SELECT COUNT(filedataid) as filecount FROM wow_rootfiles_available WHERE root8id = ?");
	// $numq->execute([$rootid]);
	// if($numq->fetch()['filecount'] > 0){
	// 	echo "Build already seems inserted, skipping..\n";
	// }else{
	// 	$pdo->query("INSERT INTO wow_rootfiles_available SELECT filedataid, '".$rootid."' FROM wow_rootfiles_available_".$root5);
	// }

	// $pdo->query("DROP TABLE IF EXISTS `wow_rootfiles_available_".$root5."`");

	echo "..done!\n";
}
?>