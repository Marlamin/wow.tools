<?php
function generateURL($type, $hash, $cdndir = "wow"){
	return "tpr/".$cdndir."/".$type."/".$hash[0].$hash[1]."/".$hash[2].$hash[3]."/".$hash;
}

function doesFileExist($type, $hash, $cdndir = "wow"){
	if(strlen($hash) < 32){
		die("Empty hash! Hash: ".$hash." Type: ".$type);
	}

	if(file_exists($GLOBALS['basedir'] . "/" . generateURL($type, $hash, $cdndir))){
		return true;
	}else{
		return false;
	}
}
?>