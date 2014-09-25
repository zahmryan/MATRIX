<?php
require_once( realpath( dirname(__FILE__) . "/../config.php" ) );
require_once( realpath( dirname(__FILE__) . "/../models/amplification.php" ) );
require_once( realpath( dirname(__FILE__) . "/../models/rating.php" ) );
require_once( realpath( dirname(__FILE__) . "/../models/comments.php" ) );
require_once( realpath( dirname(__FILE__) . "/../models/users.php" ) );
require_once( realpath( dirname(__FILE__) . "/../models/keyword.php" ) );
require_once( realpath( dirname(__FILE__) . "/../models/reviews.php" ) );
require_once( realpath( dirname(__FILE__) . "/../models/mapkeyAmp.php" ) );

$amp_id = $_GET['amp_id'];

//amp class
$amp_class = new Amplification("");
$amplification = $amp_class->findByid($amp_id);
$user_id = $amplification["idUser"];

$key_id = $amplification["idkeyword"];

$user_class = new Users("");
$user = $user_class->findByid($user_id);

//comment class
$comm_array = array(
	"idUser" => $user_id,
	"ampl" => $amp_id,

	);

$comm_class = new Comments($comm_array);

$review_class = new Reviews(array());
$rating_class = new Rating(array());
$keyword_class = new Keyword("");

//display if a keyword is deprecated
$mapkeyAmp = new mapkeyAmp();
$map_results = $mapkeyAmp->findbyAmp($amp_id);
$deprecation = array();
$keywords = array();
foreach($map_results as $map_result){
	$keyword_array = $keyword_class->findId($map_result['keywordid']);
	$keyword = $keyword_array['keyword'];
	array_push($keywords, $keyword);
	if($keyword_array['logicalDel'] == '1'){
		$keyword_status = '(deprecated)';
		array_push($deprecation, $keyword_status); 
	}
	else{
		$keyword_status = '';
		array_push($deprecation, $keyword_status);
	}		
}

//create array
$toReturn = array(
	"title" => $amplification["title"],
	"author" => $amplification["article_author"],
	"media" => $amplification["media"],
	"url" => $amplification["url"],
	"desc" => $amplification["description"],
	"keyword" => $keywords,
	"rating" => intval($rating_class->findByampid($amp_id)["rating"]),
	"citation" => $amplification["citation"],
	"copyright" => $amplification["copyright"],
	"comments" => $comm_class->findByAmplification($amp_id),
	"user" => $user["first_name"] . ' ' . $user["last_name"],
	"timestamp" => "" . date('F j, Y', strtotime($amplification["timestamp"])) . " at "  . date("g:i a", strtotime($amplification["timestamp"])) . "",
	//"review" => $review_class->findByUser($user_id)["comments"],
	"tags" => $amplification['terms'],
	"username" => $user["username"],
	"text_sub" => $amplification["text_sub"],
	"deprecated" => $deprecation,
	);

foreach($toReturn as $key => $value){
	if(is_null($value)){
		$toReturn[$key] = "";
	}
}

echo json_encode($toReturn);

?>