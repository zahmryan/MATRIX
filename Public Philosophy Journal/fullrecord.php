<?php
/*
Template Name: Full Record Page
*/
get_header();

require_once( realpath( dirname(__FILE__) . "/config.php" ) );
require_once( realpath( dirname(__FILE__) . "/models/users.php" ) );
require_once( realpath( dirname(__FILE__) . "/models/amplification.php" ) );
require_once( realpath( dirname(__FILE__) . "/models/permissions.php" ) );
require_once( realpath( dirname(__FILE__) . "/models/mapUserRole.php") );
require_once( realpath( dirname(__FILE__) . "/models/mapUserPerm.php") );
require_once( realpath( dirname(__FILE__) . "/models/reviews.php") );

globaljsvars; print globaljsvars;

$user = $_SESSION['sess_username'];
$user_class = new Users("");
$perm_class = new Permissions();
$user_id = $user_class->findIdByUser($user);
$amp_class = new Amplification("");
$ampid = $_GET['amplificationid'];
$amp = $amp_class->findByid($ampid);



wp_enqueue_script(
	"fullrecord",//name of the script
	get_template_directory_uri() ."/js/fullrecord.js",//url never show explicit
	array('jquery')
);

//enqueue new script here for mediaelement
wp_enqueue_script(
  "mediaelement",
  get_template_directory_uri()."/assets/plugins/mediaelement/mediaelement-and-player.min.js",
  array('jquery')
);

wp_enqueue_style(
  "mediaelement_style",
  get_template_directory_uri()."/assets/plugins/mediaelement/mediaelementplayer.css"
);
//ui-jquery
wp_enqueue_script(
  "jquery-ui",
  get_template_directory_uri()."/js/jquery-ui.min.js",
  array('jquery')
);

wp_enqueue_style(
  "jquery-ui",
  get_template_directory_uri()."/css/jquery-ui.css"
);

class DocxConversion{
  private $filename;

  public function __construct($filePath) {
      $this->filename = $filePath;
  }

 function read_doc() {
      $fileHandle = fopen($this->filename, "r");
      $line = @fread($fileHandle, filesize($this->filename));   
      $lines = explode(chr(0x0D),$line);
      $outtext = "";
      foreach($lines as $thisline)
        {
          $pos = strpos($thisline, chr(0x00));
          if (($pos !== FALSE)||(strlen($thisline)==0))
            {
            } else {
              $outtext .= $thisline." ";
            }
        }
       $outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$outtext);

      return $outtext;
  }

  function read_docx(){

      $striped_content = '';
      $content = '';

      $zip = zip_open($this->filename);

      if (!$zip || is_numeric($zip)) return false;

      while ($zip_entry = zip_read($zip)) {

          if (zip_entry_open($zip, $zip_entry) == FALSE) continue;

          if (zip_entry_name($zip_entry) != "word/document.xml") continue;

          $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

          zip_entry_close($zip_entry);
      }// end while

      zip_close($zip);

      $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
      $content = str_replace('</w:r></w:p>', "\r\n", $content);
      $striped_content = strip_tags($content);

      return $striped_content;
  }

  function convertToText() {

        if(isset($this->filename) && !file_exists($this->filename)) {
            return "File Not exists";
        }

        $fileArray = pathinfo($this->filename);
        $file_ext  = $fileArray['extension'];
        if($file_ext == "doc" || $file_ext == "docx" || $file_ext == "xlsx" || $file_ext == "pptx")
        {
            if($file_ext == "doc") {
                return $this->read_doc();
            } elseif($file_ext == "docx") {
                return $this->read_docx();
            }
        } else {
            return "Invalid File Type";
        }
    }
}
//function to see if a peer-reviewer is making a review.
function isPeerReviewer($userID,$amplID){
	$isPR=false;
	
	$review=new Reviews();
	$row=$review->findByUserAmp($userID,$amplID);
	if($row!=null){
		$isPR=true;
	}
	return $isPR;
}
function isEditorPu(){
	//Code to check if a user is a publisher or editor
	$userUtil = new Users('');
	$user2role= new mapUserRole();
	$userRoles=array();
	$roles= new Roles();
	$session_username = $_SESSION['sess_username'];
	$userId = $userUtil->findIdByUser($_SESSION['sess_username']);		
	$rolesId=$user2role->findByUser($userId['idUser']);
	$isEditorPu=false;
	foreach($rolesId as $id){
		$type=$roles->findTypeById($id);
		if($type!='Contributor'){
			$isEditorPu=true;
			break;
		}
	}
	return $isEditorPu;
}

function isPublisher(){
	//Code to check if a user is a publisher
	$userUtil = new Users('');
	$user2role= new mapUserRole();
	$userRoles=array();
	$roles= new Roles();
	$session_username = $_SESSION['sess_username'];
	$userId = $userUtil->findIdByUser($_SESSION['sess_username']);		
	$rolesId=$user2role->findByUser($userId['idUser']);
	$isPublisher=false;
	foreach($rolesId as $id){
		$type=$roles->findTypeById($id);
		if($type=='Publisher'){
			$isPublisher=true;
			break;
		}
	}
	return $isPublisher;
}

function getReviews($ampid){
	$reviews= new Reviews();
	$user_reviews= $reviews->findByAmp($ampid);
	return $user_reviews;
}
function findUserName($userID){
	$user= new Users('');
	return $user->findByid($userID);
	
}
?>

<section class="primary section" id="post-<?php echo get_the_ID(); ?>">
  <section class="page-title section  active-section" style="background-color: #18233E;text-align:left;padding-top:20px; padding-bottom:20px;">
    <div class="container" style="opacity: 1;">
      <div class="row">
        <div class="col-md-12">
          <div> <a id="fr_titlelink">
            <h1 id="fr_title">
            </a>
            </h1>
            <div id="fr_author"></div>
            <div id="fr_timestamp"></div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <div class="container">
    <div class="back_to_current"><a href= "<?php echo BASE_URL ?>current/">&#8592; Return to <strong>The Current</strong></a></div>
    <div class="col-md-12">
      <div id="invitation_success" hidden="true"><br>
        <br>
        <br>
        Invitation sent successfully!<br>
        <br>
        <br>
      </div>
      <div class="content"> 
        
        <!-- Edit Errors Button -->
        <?php if((isset($_SESSION['sess_username'])) && (isEditorPu()))
          { ?>
        <!-- <input type="button" id="fr_edit_fields" value="Edit Errors"> -->
        <?php } ?>
        <div id="fullrecord_display">
          <div class="row">
            <div class="col-md-6">
              <div class="row">
                <div class="col-md-12 single-content fit-video">
                  <?php if(!empty($amp['url']) && empty($amp['file_name']))
            { ?>
                  <a href="<?php echo $amp['url'] ?>" target="_blank"> <img class="fr_image" src="http://api.webthumbnail.org?width=450&height=350&screen=1280&url=<?php echo $amp['url'] ?>"/> </a>
                  <?php }
           //if media type is image
           else if(!empty($amp['file_name']) && $amp['media'] == "Image")
           { 
            //WIP - images only so far ?>
                  <img class="fr_image" src="<?php echo get_template_directory_uri().'/upload/content/'. $amp['file_name'] ?>">
                  <?php }
           //if media type is text
           else if(!empty($amp['file_name']) && $amp['media'] == "Text")
           { //check whether txt or pdf
            $file = $amp['file_name'];
            //if file is a pdf, display first page 
            if((finfo_file(finfo_open(FILEINFO_MIME_TYPE), CONTENT_PATH . $file)) == "application/pdf")
            {
              $img = new imagick(CONTENT_PATH . $file.'[0]');
              $img->setImageFormat('jpg');
              $file_img = substr($file, 0, -4) . ".jpg";
              $img->writeImage(CONTENT_PATH . $file_img); ?>
                  <a href="<?php echo BASE_URL ?>/pdf-display/?pdf=<?php echo $file ?>"> <img class="fr_image" src="<?php echo get_template_directory_uri().'/upload/content/'.$file_img ?>"/> </a> <a href="<?php echo BASE_URL ?>/pdf-download/?file=<?php echo $file ?>">Download PDF</a>
                  <?php }
            else if((finfo_file(finfo_open(FILEINFO_MIME_TYPE), CONTENT_PATH . $file)) == "text/plain")
            {
               $file_text = file_get_contents(CONTENT_PATH . $file); ?>
                  <div id="fr_textblock"><?php echo nl2br($file_text) ?></div>
                  <?php }
            else if((finfo_file(finfo_open(FILEINFO_MIME_TYPE), CONTENT_PATH . $file)) == "application/msword")
            {
              //WIP: this is where ms docs should be loaded (.doc)
              $Docx_class = new DocxConversion(CONTENT_PATH . $file);
                //var_dump($Doc_class->read_doc());
                ?>
                  <div id = "fr_textblock"><?php echo nl2br($Docx_class->convertToText()) ?></div>
                  <?php }
            else if((finfo_file(finfo_open(FILEINFO_MIME_TYPE), CONTENT_PATH . $file)) == "application/vnd.openxmlformats-officedocument.wordprocessingml.document")
            {
                $Docx_class = new DocxConversion(CONTENT_PATH . $file);
                //var_dump($Docx_class->read_docx());

                ?>
                  <div id = "fr_textblock"><?php echo nl2br($Docx_class->convertToText()) ?></div>
                  <?php }
          }
          else if(!empty($amp['file_name']) && $amp['media'] == "Video")
          { 
            $file = $amp['file_name'];
            $file_type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), CONTENT_PATH . $file); 
            ?>
                  <video width="320" height="240" id="fr_video" preload="metadata" controls>
                    <source src="<?php echo BASE_URL.'wp-content/themes/Flatter/upload/content/'. $amp['file_name'] ?>" type="<?php echo $file_type ?>">
                  </video>
                  <?php }
          else if(!empty($amp['file_name']) && $amp['media'] == "Audio")
          { ?>
                  <audio id="fr_audio" src="<?php echo get_template_directory_uri().'/upload/content/'. $amp['file_name'] ?>" preload="auto" controls>
                    <p>Your browser does not support the audio element.</p>
                  </audio>
                  <?php } 
		  else if(empty($amp['file_name']) && empty($amp['media'] && !empty($amp['text_sub'])))
		  { ?>
                  <div id = "fr_textblock"><?php echo nl2br($amp['text_sub']); ?></div>
                  <?php } ?>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <input  type="hidden" id="amplificationid" value="<?php echo $_GET["amplificationid"] ?>">
              <div class="full-record">
                <div id="fr_title"></div>
                <div id="fr_media"></div>
                <div id="fr_url"></div>
                <div id="fr_desc"></div>
                <div id="fr_keyword"></div>
                <div id="fr_rating"></div>
                <div id="fr_citation"></div>
                <div id="fr_copyright"></div>
                <div id="fr_user"></div>
                <div id="fr_review"></div>
                <div id="fr_tags"></div>
		
                <?php
		if(!isEditorPu()){
			if(isPeerReviewer($user_id['idUser'],$ampid)){ 
				$peer_review_url = BASE_URL . 'peer-review-editor?amplification='.$ampid; ?>
                <input type="button" id="fr_peer_review_editor" onClick="window.location.href = '<?php echo $peer_review_url ?>';" value="Peer Review Editor">
             <?php
			}
		}
                
       if(($amp['display'] == "Accepted") || (isset($_SESSION['sess_username']) && (isEditorPu())))
		  { ?>
                <?php if((isset($_SESSION['sess_username'])) && (isEditorPu())){ ?>
                <div class="status_permissions">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="amp_status">Status
                        <select id="fr_options">
                          <?php if($amp['display'] == "Pending")
						{ ?>
                          <option value="fr_pending" selected>Pending</option>
                          <?php }
          else
          { ?>
                          <option value="fr_pending">Pending</option>
                          <?php }
           if($amp['display'] == "Accepted")
           { ?>
                          <option value="fr_accepted" selected>Accepted</option>
                          <?php }
           else
           { ?>
                          <option value="fr_accepted">Accepted</option>
                          <?php }
          if($amp['display'] == "Declined")
          { ?>
                          <option value="fr_declined" selected>Declined</option>
                          <?php }
          else
          { ?>
                          <option value="fr_declined">Declined</option>
                          <?php }
          if($amp['display'] == "Deleted")
          { ?>
                          <option value="fr_deleted" selected>Deleted</option>
                          <?php }
          else
          { ?>
                          <option value="fr_deleted">Delete</option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="submit_status">
                        <input type="submit" id="fr_change_display" value="Submit">
                      </div>
                    </div>
                    <?php
          $user_array = $user_class->findAllUsers();
          $num_of_users = count($user_array);
          
        
          if($amp['url']==null || $amp['url']==''){
          ?>
                    <div class="col-md-6">
                      <div class="permissions">Assign Permissions
                        <select id="permission_users">
                          <option><?php echo " "?></option>
                          <?php 
             foreach($user_array as $key => $user)
             {
                ?>
                          <option value="<?php echo $user['idUser'];?>"><?php echo $user['first_name']." ".$user['last_name'] ?></option>
                          <?php }
            ?>
                        </select>
                      </div>
                      <!--  <div id="Perm_selected_user">-->
                      <form id="perm_update" style="display:none">
                        <input type="radio" name="permission_type" value="Peer Reviewer">
                        Peer Reviewer<br>
                        <input type="radio" name="permission_type" value="Collaborator">
                        Mentor<br>
                        <input type="button" id="Perm_submit" value="Update Permissions">
                      </form>
                    </div>
                    <?php }?>
                  </div>
                  <div class="invite_export">
                    <?php
				  if(isset($_SESSION['sess_username']) && (isPublisher())){ ?>
                    <a id="fr_invite_to_official_publication"><strong>Invite</strong> to Publication</a> | <a id="fr_download"><strong>Export</strong> to Kora</a> | <a id="fr_published"><strong>Publish</strong> Record</a>
                    <?php }
                  
                  				if(isPeerReviewer($user_id['idUser'],$ampid)){ 
					$peer_review_url = BASE_URL . 'peer-review-editor?amplification='.$ampid; ?>
                    <a id="fr_peer_review_editor" onClick="window.location.href = '<?php echo $peer_review_url ?>';"><strong>Peer Review</strong> Editor</a>
                    <?php }
				 //Edit Button
         if(isset($_SESSION['sess_username']) && (isEditorPu())){ ?>
                    | <a id="fr_edit_errors"><strong>Edit</strong> Errors</a> <a id="fr_finish_edit" hidden="true"><strong>Finish Editing</strong></a>
                    <?php } ?>
                  </div>
                </div>
                <?php }?>
              </div>
            </div>
			<div id="reviews">
			<?php    
			/*	$review=getReviews($ampid);
				$i=1;
				foreach ($review as $re){
					$userID=$re['userID'];
					$user=findUserName($userID);
				?>
					<a href="href= "<?php echo BASE_URL.'review/?userID=$userID&amplID=$ampid'?>"> Peer Review <?php echo $i.": ".$amp['title']." by reviewer ".$user['first_name']." ".$user['last_name'];?></a>
					<?php $i++;
				}
				*/
			?></div>
          </div>
          <!--Div for comment display -->
          <div class="clearfix"></div>
          <div class="col-md-6">
            <h4>Comments</h4>
            <div id="comments"> </div>
            <div id="add_comment">
              <?php
  //Check if session is on, in order to add new comments
  if(isset($_SESSION['sess_username'])){ ?>
              <form id="new_comment" method="post" action="">
                <textarea name="comment_box" cols="25" rows="5" placeholder="Enter a comment here..." required></textarea>
                <div id="character_count">0 characters</div>
                <br>
                <input type="radio" name="comment_anon" value="anon_false" checked>
                Show username to other users<br>
                <input type="radio" name="comment_anon" value="anon_true">
                Post as an anonymous user<br>
                <input id="submit_comment" type="button"  value="Post Comment"/>
              </form>
              <div id = 'reply_form' style="display: none;">
                <form id='new_reply' method='post' action=''>
                  <textarea name = 'reply_box' cols='25' rows='5' placeholder="Enter a Reply Here..."></textarea>
                  <br>
                  <input type='radio' name='reply_anon' value='anon_false' checked>
                  Show username to other users<br>
                  <input type='radio' name='reply_anon' value='anon_true'>
                  Post as an anonymous user<br>
                  <input id='submit_reply' type='button'  value='Submit' />
                  <input id='cancel_reply' type='button' value='Cancel' />
                </form>
              </div>
              <?php }
  else{ ?>
              <h6>Log in to Comment on this Record</h6>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
  </div>
</section>
<?php
}
else
{ ?>
<div class="fr_norecord">This record was <?php echo $amp['display'] ?> and cannot be displayed. </div>
<?php }
	get_footer();
?>
