<?php
include("../../inc/config.php");

$cq = $pdo->prepare("SELECT filedataid FROM wow_rootfiles_chashes WHERE contenthash = ?");
$fq = $pdo->prepare("SELECT id, filename FROM wow_rootfiles WHERE id = ? AND filename IS NOT NULL");
echo "<pre>";
foreach($pdo->query("SELECT id, contenthash FROM wow_rootfiles LEFT JOIN wow_rootfiles_chashes ON wow_rootfiles.id=wow_rootfiles_chashes.filedataid WHERE filename IS NULL AND verified = 0 AND type = 'blp' ORDER BY wow_rootfiles.id DESC") as $file){

	$cq->execute([$file['contenthash']]);
	while($cres = $cq->fetch()){
		$fq->execute([$cres['filedataid']]);
		while($fres = $fq->fetch()){
			echo "File " . $fres['id'] . " (".$fres['filename'].") has the same content as unknown filedataid " . $file['id']."\n";
		}
	}
}
?>