<?php
/*
Template Name: Members
*/

get_header();
require_once( realpath( dirname(__FILE__) . "/config.php" ) );
require_once( realpath( dirname(__FILE__) . "/models/roles.php") );
require_once( realpath( dirname(__FILE__) . "/models/users.php") );
require_once( realpath( dirname(__FILE__) . "/models/permissions.php") );
require_once('models/mapUserRole.php');
require_once('models/mapUserPerm.php');

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

wp_enqueue_script(
	"Members",
	get_template_directory_uri() . "/js/members.js",
	array('jquery')
	);

/*WORDPRESS ENQUEUEING NOT WORKING
wp_register_style('Member_style',get_template_directory_uri().'/css/members.css');
wp_enqueue_style('Member_style');
*/
?>

<section class="primary section" id="post-<?php echo get_the_ID(); ?>">
  <section class="page-title section  active-section" style="background-color: #18233E;text-align:left;padding-top:20px; padding-bottom:20px;">
    <div class="container" style="opacity: 1;">
      <div class="row">
        <div class="col-md-12">
          <div>
            <h1>
              <?php the_title(); ?>
            </h1>
          </div>
        </div>
      </div>
    </div>
  </section>
  <div class="container">
    <div class="col-md-12">
      <div class="row">
        <div class="<?php echo $content_class; ?>">
          <div class="content">
              <?php

$role_class = new Roles();
$user_class = new Users("");
$perm_class = new Permissions();
$user2role= new mapUserRole();
$user2perm= new mapUserPerm();

$roles=["Publisher","Editor","Contributor"];

function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
    $sort_col = array();
    foreach ($arr as $key=> $row) {
        $sort_col[$key] = $row[$col];
    }

    array_multisort($sort_col, $dir, $arr);
}



foreach ($roles as $role)
{	$res= $role_class->findByType($role);
	$usersID=$user2role->findbyRole($res['idRole']);
	$users=array();
	foreach ($usersID as $user){
		$row=$user_class->findByid($user);	
		array_push($users,$row);
		
	}
	array_sort_by_column($users, 'last_name');
	echo "<div class='".$role."s'><h3>".$role."s</h3>";
	foreach ($users as $row){
		//$row=$user_class->findByid($user);		
			echo '<div class="member">
			<div class="member_image">
			';
			if(isset($row['url_photo']))
			{
				$pathinfo = pathinfo($row['url_photo']);
				$user_photo = $pathinfo['filename'] . "125px." . $pathinfo['extension'];
				?>
              <div class="crop_container">
                <div> <a href="<?php echo''.BASE_URL.'/profile/?user='.$row["username"].''?>" class="kgfs_img crop" style="background-image: url(<?php echo''.BASE_URL . 'wp-content/themes/Flatter/upload/profilePics/' .$user_photo.''?>)"> </a> </div>
              </div>
              </div>
              <?php
			}
			else
			{
				echo '<a href="'.BASE_URL.'/profile/?user='.$row["username"].'"><div class="member_border"><img id="member_img" src='.get_gravatar($row["email"]).' alt='.$row["username"].'></a></div></div>';
			}
			echo '<div class="member_details">
			<div class = "member_name"><a href="'.BASE_URL.'/profile/?user='.$row["username"].'">'.$row["first_name"]. ' ' . $row["last_name"].'</a></div>';

			//Affiliation
			if(isset($row["affiliation"]))
			{
				echo '<div class="member_aff">'.$row["affiliation"].'</div>';
			}
			else
			{
				echo '<div class="member_aff">No Affiliation</div>';
			}

			
			//Permissions
			$permissions = $user2perm->findByUser($row['idUser']);
			
			foreach ($permissions as $permID)
			{	$perm=$perm_class->findById($permID);
				echo '<div class="member_perm">('.$perm['permission'].')</div>';
			}
			echo '</div></div>';
			$prev_role = $role;
		
		//echo "</div>";
	}
	}
?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php
get_sidebar();
get_footer();
?>
