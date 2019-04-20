<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");

include("../../inc/config.php");

$query = $pdo->query("SELECT id,filename FROM wow_rootfiles WHERE filename LIKE 'DBFilesClient%.db2'");

$fhandle = fopen("/home/wow/buildbackup/dbcs.txt", "w");

while($row = $query->fetch()){
	fwrite($fhandle, $row['id'].";".$row['filename']."\n");
}
fclose($fhandle);

$query =
"SELECT
wow_versions.cdnconfig,
wow_versions.buildconfig,
wow_buildconfig.description,
wow_buildconfig.root_cdn,
wow_buildconfig.product
FROM wow_versions
LEFT OUTER JOIN wow_buildconfig ON wow_versions.buildconfig=wow_buildconfig.hash
ORDER BY wow_buildconfig.description
";

$processedRootFiles = array();
$res = $pdo->query($query);
while($row = $res->fetch()){
	$rawdesc = str_replace("WOW-", "", $row['description']);
	$build = substr($rawdesc, 0, 5);

	if($build < 26530) continue;

	$rawdesc = str_replace(array($build, "patch"), "", $rawdesc);
	$descexpl = explode("_", $rawdesc);
	$outdir = $descexpl[0].".".$build;

	if(file_exists("/home/wow/dbcs/".$outdir."/")){
		continue;
	}

	echo "Exporting DBCs to ".$outdir."\n";
	$output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll extractfilesbyfdidlist ".$row['buildconfig']." ".$row['cdnconfig']." /home/wow/dbcs/".$outdir."/ dbcs.txt");
	$processedRootFiles[] = $row['root_cdn'];
}

?>