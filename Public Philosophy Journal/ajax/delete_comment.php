<?php
require_once( realpath( dirname(__FILE__) . "/../config.php" ) );
require_once( realpath( dirname(__FILE__) . "/../models/amplification.php" ) );
require_once( realpath( dirname(__FILE__) . "/../models/comments.php" ) );

$commentid = $_GET['id'];

$comm_array = array(
	"idComment" => $commentid,
	);

$comm_class = new Comments($comm_array);

$comm_class->delete();

echo json_encode(true);
?>