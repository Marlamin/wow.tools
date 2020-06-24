<?php
require_once(__DIR__ . "/functions.php");
require_once(__DIR__ . "/CompareArrays.php");
require_once(__DIR__ . "/worldStateExpression.php");
require_once(__DIR__ . "/DBDReader.php");

if(empty($disableBugsnag)){
	require_once(__DIR__ . "/bugsnag/autoload.php");
}

$db = '';
$dbuser = '';
$dbpassword = '';

$dbOptions = [
  PDO::ATTR_EMULATE_PREPARES   => false,
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::MYSQL_ATTR_LOCAL_INFILE => true
];

try{
	$pdo = new PDO('mysql:host=localhost;dbname=' . $db, $dbuser, $dbpassword, $dbOptions);
}
catch(Exception $e){
	die("Unable to connect to database: " . $e->getMessage());
}

$allowedproducts = array(
	"wow" => array("name" => "World of Warcraft", "cdndir" => "wow", "ngdp" => array("wow", "wowt", "wow_beta", "wow_classic_beta", "wow_classic", "wowz")),
	"catalogs" => array("name" => "Catalog", "cdndir" => "catalogs", "ngdp" => array("catalogs")),
	"agent" => array("name" => "Agent", "cdndir" => "bnt001", "ngdp" => array("agent"), "loggedinonly" => true)
);

$previewTypes = array("ogg", "mp3", "blp", "wmo", "_xxxwmo", "adt", "m2");

$memcached = new Memcached('wowtools');
if(empty($memcached->getServerList()))
{
	$memcached->addServer( '/var/run/memcached/memcached.sock', 0 );
}

$github['oath'] = '';
$github['secret'] = '';

$discord['wow-retail']['url'] = "https://discordapp.com/api/webhooks/";
$discord['wow-retail']['products'] = ["wow"];

$discord['wow-classic']['url'] = "https://discordapp.com/api/webhooks/";
$discord['wow-classic']['products'] = ["wow_classic", "wow_classic_beta", "wowdemo"];

$discord['wow-ptr']['url'] = "https://discordapp.com/api/webhooks/";
$discord['wow-ptr']['products'] = ["wowt"];

$discord['wow-beta']['url'] = "https://discordapp.com/api/webhooks/";
$discord['wow-beta']['products'] = ["wow_beta"];

$discord['wow-other']['url'] = "https://discordapp.com/api/webhooks/";
$discord['wow-other']['products'] = ["wowdev", "wowe1", "wowe2", "wowe3", "wowv", "wowv2", "wowz"];

$discord['not-wow']['url'] = "https://discordapp.com/api/webhooks/";
$discord['not-wow']['products'] = ["catalogs", "bna", "agent"];

$discord['test']['url'] = "https://discordapp.com/api/webhooks/";
$discord['test']['products'] = ["test"];

$discordfilenames = "https://discordapp.com/api/webhooks/";

$telegram['chat_id'] = "";
$telegram['token'] = '';

$sendgrid['apikey'] = "";

$github['username'] = "";
$github['oauthkey'] = "";
if(php_sapi_name() != 'cli')
{
	if(!isset($_SESSION)){
		session_set_cookie_params(28800, '/', "wow.tools", true, true);
		session_name('wowtools');
		session_start();
	}
}
?>