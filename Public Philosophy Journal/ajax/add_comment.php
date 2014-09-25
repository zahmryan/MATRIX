<?php
require_once( realpath( dirname(__FILE__) . "/../config.php" ) );
require_once( realpath( dirname(__FILE__) . "/../models/comments.php" ) );
require_once( realpath( dirname(__FILE__) . "/../models/users.php" ) );
require_once( realpath( dirname(__FILE__) . "/../models/rating.php" ) );

$comment = $_POST["comment"];
$user = $_SESSION['sess_username'];

$user_class = new Users("");
$user_id = $user_class->findIdByUser($user)['idUser'];
$amp_id = $_POST["amp_id"];
$anon = $_POST["anon"];
$date = new DateTime();

if($anon == "anon_true"){
	$anon = 1;
}

else{
	$anon = 0;
}

$comm_array = array(
	"idUser" => $user_id,
	"ampl" => $amp_id,
	"comment" => $comment,
	"timestamp" => date("Y-m-d H:i:s", $date->getTimestamp()),
	"anon" => $anon,
	);

$comm_class = new Comments($comm_array);

$comm_class->insert();

echo json_encode(true);
?>