<?php
require_once( realpath( dirname(__FILE__) . "/../config.php" ) );
require_once( realpath( dirname(__FILE__) . "/../models/amplification.php" ) );
require_once( realpath( dirname(__FILE__) . "/../models/comments.php" ) );
require_once( realpath( dirname(__FILE__) . "/../models/users.php" ) );
require_once( realpath( dirname(__FILE__) . "/../models/rating.php" ) );

$amp_id = $_GET['amp_id'];

function get_gravatar( $email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array() ) {
    $url = 'http://www.gravatar.com/avatar/';
    $url .= md5( strtolower( trim( $email ) ) );
    $url .= "?s=$s&d=$d&r=$r";
    if ( $img ) {
        $url = '<img src="' . $url . '"';
        foreach ( $atts as $key => $val )
            $url .= ' ' . $key . '="' . $val . '"';
        $url .= ' />';
    }
    return $url;
}

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

$rating_class = new Rating(array());

$comm_class = new Comments($comm_array);

$comments = $comm_class->findByAmplification($amp_id);

//Build html display of comments

foreach($comments as $key => $value)
{
	$htmlout = "";
	$comment = $value['comment'];
	$timestamp = "" . date('F j, Y', strtotime($value['timestamp'])) . " at "  . date("g:i a", strtotime($value['timestamp'])) . "";
	$user = $user_class->findByid($value['idUser']);

	if($value['anonymous'] == 1)
	{
		$htmlout .= "<div id ='comment_display'>
		<div id='comment_image'>
		<img id='comment_img' src='".get_gravatar($user["email"])."'></div>
		<div id='comment_user'>Anonymous</div>
		<div id='comment_time'>".$timestamp."</div>
		<div class='comment_actions'>";
		if(isset($_SESSION['sess_username'])) 
		{
			if($user['username'] == $_SESSION['sess_username'])
			{
				$htmlout .= "
				<a onclick='delete_click(this);'>Delete</a> | 
				<a onclick='edit_click(this);'>Edit</a> | 
				<a onclick='reply_click(this);'>Reply</a>";

			}
			else
			{
				$htmlout .= "<a onclick='reply_click(this);'>Reply</a>";
			}
		}
?></div><?php
		/*RATING: creating new rating if not already created for that comment
		if(empty($rating_class->findByComment($value['idComment']))){
			$rating_array = array(
				"userID" => $value['idUser'],
				"media" => $value['idComment'],
				"amp_id" => $amp_id,
				"rating" => 0,
			);
			$rating_class = new Rating($rating_array);
			$rating_class->insert();
		}
		$rating = $rating_class->findByComment($value['idComment'])['rating'];
		$htmlout .= "<span id='comment_rating'>Rating: ".$rating."</span>*/
		$htmlout .= "<div id = 'comment'>".$comment."</div>";
		if(isset($value['idParent']))
		{
			$htmlout .= "<input class='parent_id' type='hidden' name='parent_id' value='".$value['idParent']."'>";
		}
		$htmlout .= "<input class='comment_id' type='hidden' name='comment_id' value='".$value['idComment']."'>
		</div>";
		echo $htmlout;	
	}
	else
	{
		$htmlout .= "<div id='comment_display'>
		<div id='comment_image'>
		<a href='".BASE_URL."/profile/?user=".$user['username']."'>";
		if(!isset($user['url_photo']))
		{
			$htmlout .= "<img id='comment_img' src='".get_gravatar($user["email"])."'></div>";
		}
		else
		{
			$pathinfo = pathinfo($user["url_photo"]);
			$userphoto125px = $pathinfo['filename'] . "125px." . $pathinfo['extension'];
			$htmlout .= "<img id='comment_img' src='".BASE_URL. "wp-content/themes/Flatter/upload/profilePics/" .$userphoto125px. "' alt=".$user["username"]."></div>";
		}
		$htmlout .= "<div id='comment_user'>".$user['username']."</div></a>
		<div id='comment_time'>".$timestamp."</div>
		<div class='comment_actions'>";
		if(isset($_SESSION['sess_username'])) {
			if($user['username'] == $_SESSION['sess_username'])
			{
			?><?php	
				$htmlout .= "
				<a onclick='delete_click(this);'>Delete</a> | 
				<a onclick='edit_click(this);'>Edit</a> | 
				<a onclick='reply_click(this);'>Reply</a>";
			}
			else
			{
				$htmlout .= "<a onclick='reply_click(this);'>Reply</a>";
			}
		}
		?>
        </div>
        <?php

		/*RATING: creating new rating if not already created for that comment
		if(empty($rating_class->findByComment($value['idComment']))){
			$rating_array = array(
				"userID" => $value['idUser'],
				"media" => $value['idComment'],
				"amp_id" => $amp_id,
				"rating" => 0,
			);
			$rating_class = new Rating($rating_array);
			$rating_class->insert();
		}
		$rating = $rating_class->findByComment($value['idComment'])['rating'];
		$htmlout .= "<span id='comment_rating'>Rating: ".$rating."</span>*/
		$htmlout .= "<div id = 'comment'>".$comment."</div>";
		if(isset($value['idParent']))
		{
			$htmlout .= "<input class='parent_id' type='hidden' name='parent_id' value='".$value['idParent']."'>";
		}
		$htmlout .= "<input class='comment_id' type='hidden' name='comment_id' value='".$value['idComment']."'>
		</div>";
		echo $htmlout;
	}

}
//echo $htmlout;
?>