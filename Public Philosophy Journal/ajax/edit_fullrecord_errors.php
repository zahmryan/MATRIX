<?php

require_once( realpath( dirname(__FILE__) . "/../config.php" ) );
require_once( realpath( dirname(__FILE__) . "/../models/amplification.php" ) );

$amp_id = $_GET['amp_id'];
$title = $_GET['title'];
$desc = $_GET['desc'];
$copyright = $_GET['copyright'];
$citation = $_GET['citation'];

//amp class
$amp_class = new Amplification("");
$amplification = $amp_class->edit_errors($amp_id,$title,$desc,$copyright,$citation);

return $amplification;
