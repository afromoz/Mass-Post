<?php
/*
Plugin Name: Anime Mass Post Plugin
Plugin URI: http://www.animemp4.net/
Description: Plugin to post anime episodes VERY quickly.
Version: 1.4.7
Author: MozTycoon
Author URI: http://www.animemp4.net/
*/
/* Copyright MozTycoon 2017
 * This plugin may only be used by those who have purchased a license
 * for it. Licenses apply to only 1 site and may not be transfered to
 * another party.
 *  If you'd like to purchase another license, contact
 *  MozTycoon at moztycoon@gmail.com
 *  */


//Create new option array if it doesn't exist already
if ( !get_option('mass_post_options') ){
	$blankarray = array();
	add_option('mass_post_options', $blankarray, '', 'no');
}
if ( !get_option('mass_post_options_backend') ){
	$blankarray = array();
	$blankarray['maxposts'] = '100'; // Setting default maxposts
	add_option('mass_post_options_backend', $blankarray, '', 'no');
}

//Clear Usage/Options If Requested
if( $_GET['usage_refresh']=='true' ){
	delete_option('mass_post_options');
	$blankarray = array();
	add_option('mass_post_options', $blankarray, '', 'no');
}
if( $_GET['options_refresh']=='true' ){
	delete_option('mass_post_options_backend');
	$blankarray = array();
	$blankarray['maxposts'] = '100'; // Setting default maxposts
	add_option('mass_post_options_backend', $blankarray, '', 'no');
}

//Clean up after options above
if($blankarray)
	unset($blankarray);

//Add a new page to Post menu
add_action('admin_menu', 'ba_mass_post_menu');

function ba_mass_post_menu() {
  $plugin_page = add_posts_page( 'Mass Post', 'Mass Post', 'mass_post', __FILE__, 'ba_mass_post_page');
  add_options_page( 'Mass Post Options', 'Mass Post Options', 10, __FILE__, 'ba_mass_post_options_page');
	add_action( 'load-'. $plugin_page, 'ba_mass_post_header' );
	add_action('admin_print_scripts-'. $plugin_page, 'ba_mass_post_page_javascript');
}

//Function added to page header, used to post after submit
function ba_mass_post_header(){
  $hidden_field_name = 'mt_submit_hidden';
  $data_field_name = 'mt_favorite_food';
  if( $_POST[ $hidden_field_name ] == 'Y' ) {
  $returned_array = mp_addposts($_POST);
   $added = '';
    $added .= '&queries='.$returned_array['queries'];
    $added .= '&time='.$returned_array['time'];
    $added .= '&name='.$returned_array['name'];
    $added .= '&count='.$returned_array['count'];
    if($returned_array['note']){$added .= '&note';}

    $self = str_replace( '%7E', '~', $_SERVER['REQUEST_URI']);
    header("Location: $self&done=1$added");
    exit(0);
  }
}

//Mass Post Page Javascript
function ba_mass_post_page_javascript(){
$site_url = get_settings('siteurl');
$backend_array = get_option('mass_post_options_backend');
?>
<script type="text/javascript" src="<?php echo $site_url;?>/wp-content/plugins/ba-mass-post/js/mass-post-page.js"></script>
<script type="text/javascript" src="<?php echo $site_url;?>/wp-admin/js/post.js"></script>
<link rel="stylesheet" href="<?php echo $site_url;?>/wp-content/plugins/ba-mass-post/css/ex.css" type="text/css">
<style type="text/css">
div#tipDiv {
    color:#000; font-size:11px; line-height:1.2;
    background-color:#E1E5F1; border:1px solid #667295; 
    width:210px; padding:4px;
}
.underlineit { border-bottom: dotted 1px #666666; }
</style>
<script src="<?php echo $site_url;?>/wp-content/plugins/ba-mass-post/js/dw_event.js" type="text/javascript"></script>
<script src="<?php echo $site_url;?>/wp-content/plugins/ba-mass-post/js/dw_viewport.js" type="text/javascript"></script>
<script src="<?php echo $site_url;?>/wp-content/plugins/ba-mass-post/js/dw_tooltip.js" type="text/javascript"></script>
<script src="<?php echo $site_url;?>/wp-content/plugins/ba-mass-post/js/dw_tooltip_aux.js" type="text/javascript"></script>
<script type="text/javascript">

dw_Tooltip.content_vars = {
    SeriesName: 'Format: "<i>Series Name</i>"',
    SubDub: 'Choose any of the defaults or make your own by selecting "Other"<br/>Explanation: Subbed- Subtitled, Dubbed- English Voices',
    Episodes: 'Choose the number of episodes you wish to add.<br/>The form will create a row for each episode.',
    Offset: '<b>Important!</b> - This must be a whole number, Default:1<br/><b>Usage</b>: Set to 10 and your first post will be Episode 10',
    Category: 'Choose a category from the list. Remember:<br/>* 1 Category Per Post<br/>* Main Categories are NOT to be used as categories for your posts<br/>Default: <i>Uncategoried</i>',
    MoreCode: 'The "More-Code" =><br/>"<b>< !--more-- ></b>"<br/>Auto More-Code adds this to the beginning of every post.',
    Player: 'Decides how the content will be added to the post.<br/>The content of the Post Content field is inserted where "*" is shown.<br/> Example: <b>[player video="</b><i>*</i><b>"]</b>',
	FinalTag: 'If enabled, "[Final]" will be added to the last post title added.<br/>Format: "Series Name Episode # [Final] Sub/Dub"'
}
</script>
<script type="text/javascript">

function makeChoice(){
  var val = 0;
  //var subdub = document.getElementById('subdub');
  //var othervalue = document.getElementById('othervalue');

  for( i = 0; i < document.postform.subdub.length; i++ ){
  	if( document.postform.subdub[i].checked == true ){
  		val = document.postform.subdub[i].value;
  		if(val=='other'){
  			document.postform.othervalue.disabled=false;
  			document.postform.othervalue.focus();
  		} else {
  			document.postform.othervalue.disabled=true;
  		}
  	}
  }
}

var numSel = <?php echo $backend_array['maxposts']; ?>;

</script>
<?php
}

//Mass Post Page
function ba_mass_post_page(){

//Redefined, just in case
$hidden_field_name = 'mt_submit_hidden';
$data_field_name = 'mt_favorite_food';

//Were posts added?
if ($_GET['done']=='1') {
	?>
	<div class="updated"><p><strong><?php echo $_GET['count'].' \''.$_GET['name'].'\' Posts Created.'; ?></strong>
	<?php
	
	if($_GET['queries']&&$_GET['time']){
	echo "<br/>\n<i>Process Used ".$_GET['queries']." queries and took ".$_GET['time']." seconds to finish.</i>";
	}
	echo "</p></div><br/>\n";
	
	if($_GET['note'] == 'true'){
	echo "<div class=\"error\"><p><strong>Note: Posts were scheduled for the future because you are using Mass Post too fast.</strong></p></div>\n";
	}
}

//Dump options into an array
$mass_post_options_array = get_option('mass_post_options');
	global $current_user;
	get_currentuserinfo();
	$current_author = $current_user->ID;
	$current_author_name = $current_user->user_login;

//Get last used (count) of current author
if($mass_post_options_array["{$current_author_name}"]['last_used_count']){
	$last_used_count_check = $mass_post_options_array["{$current_author_name}"]['last_used_count'];
}else{
	$last_used_count_check = 0;
}

//Compair current time to last used (time)
$time_check = time();
if($mass_post_options_array["{$current_author_name}"]['last_used_time']){
	$last_used_time_check = $mass_post_options_array["{$current_author_name}"]['last_used_time'];
	$last_used_time_check = $last_used_time_check + $last_used_count_check + 5;
}else{
	$last_used_time_check = 0;
}
if ($time_check < $last_used_time_check){
	$wait_time = $last_used_time_check-$time_check;
}else{
	$wait_time = 0;
}

//We no longer need the array so we unset it to save resources
unset($mass_post_options_array);

//BEGIN FORM
?>
<link rel="stylesheet" type="text/css" href="<?php echo get_settings('siteurl');?>/wp-content/plugins/ba-mass-post/css/mass-post.css" />
<div class="wrap">

<h2>Anime Mass Post</h2><small>V1.4.7</small>
<p>Please fill out <b>ALL</b> the following information about the Anime you wish to mass post.<br/>
<b>Warning:</b> Leaving this page at any time clears all fields. You've been warned.</p>
<p>Note: <b>Add the category you are posting in before filling out this form.</b> The page must be refreshed for the category to be added to the list bellow.</p>
<form action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post" name="postform" id="postform">

  <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
  <input type="text" name="anime-form" value="animemp4" style="display:none;">
  <div class='showTip SeriesName underlineit'><b>Anime Series Name:</b></div> <input type="text" size="23" id="anime-name" name="anime-name"><br/>
  <div class='showTip SubDub underlineit'><b>Sub/Dub:</b></div> <input type="radio" id="subdub" name="subdub" value="none" onclick="makeChoice();"> <i>None</i> <input type="radio" id="subdub" name="subdub" value="sub" onclick="makeChoice();"> "(Sub)" <input type="radio" id="subdub" name="subdub" value="dub" onclick="makeChoice();"> "(Dub)" <input type="radio" id="subdub" name="subdub" value="subbed" onclick="makeChoice();"> "Subbed" <input type="radio" id="subdub" name="subdub" value="dubbed" onclick="makeChoice();"> "Dubbed" <input type="radio" id="subdub" name="subdub" value="other" onclick="makeChoice();"> Other: <input type="text" size="10" id="othervalue" name="othervalue" disabled="true"><br/>
  <div class='showTip Offset underlineit'><b>Begin on Episode #:</b></div> <input type="text" size="3" id="ep_offset" name="ep_offset" value="1" onchange='updateNumbers()'> (Default: 1, must be a number)<br/>
  <div class='showTip Episodes underlineit'><b># of Episodes:</b></div> <select id='MySel' name='MySel' onchange='populate(this,"MyTable")'>
    <option>Pick...</option>
  </select><br/>
  <div class='showTip Category underlineit'><b>Category:</b></div>
<div id="categorydiv">
<div id="categories-all" class="tabs-panel">
	<ul id="categorychecklist" class="list:category categorychecklist form-no-clear">
<?php wp_category_checklist($post->ID, false, false, $popular_ids) ?>
	</ul>
</div>

<?php if ( current_user_can('manage_categories') ) : ?>
<div id="category-adder" class="wp-hidden-children" style="display:none;">
	<h4><a id="category-add-toggle" href="#category-add" class="hide-if-no-js" tabindex="3"><?php _e( '+ Add New Category' ); ?></a></h4>
	<p id="category-add" class="wp-hidden-child">
	<label class="screen-reader-text" for="newcat"><?php _e( 'Add New Category' ); ?></label><input type="text" name="newcat" id="newcat" class="form-required form-input-tip" value="<?php esc_attr_e( 'New category name' ); ?>" tabindex="3" aria-required="true"/>
	<label class="screen-reader-text" for="newcat_parent"><?php _e('Parent category'); ?>:</label><?php wp_dropdown_categories( array( 'hide_empty' => 0, 'name' => 'newcat_parent', 'orderby' => 'name', 'hierarchical' => 1, 'show_option_none' => __('Parent category'), 'tab_index' => 3 ) ); ?>
	<input type="button" id="category-add-sumbit" class="add:categorychecklist:category-add button" value="<?php esc_attr_e( 'Add' ); ?>" tabindex="3" />
<?php	wp_nonce_field( 'add-category', '_ajax_nonce', false ); ?>
	<span id="category-ajax-response"></span></p>
</div>
<a href="edit-tags.php?taxonomy=category" target="_blank">Add New Category</a>(Opens in a new window)<br/>
</div>
<?php
endif;

//add_meta_box('categorydiv', __('Categories'), 'post_categories_meta_box', 'post', 'side', 'core');

?>
  <br/>
  Once you are done filling in all the info above you can start adding episode info below.<br/>
  Note: The More code will be added automatically if the check box bellow is selected.<br/>
  <br/>
  <div class='showTip MoreCode underlineit'><b>'Auto More-Code'?:</b></div>Enabled: <input type="checkbox" name='auto-more' id='auto-more'/><br/>
  <div class='showTip FinalTag underlineit'><b>Final Tag?:</b></div>Enabled: <input type="checkbox" name='finaltag' id='finaltag' /><br/>
  <table id='MyTable'>
    <thead align='center'>
      <tr>
        <th style='width:53px;'>Ep #</th>
        <th style='width:1069px;'>Post Content (Embed Code)</th>
	 	<!--<th style='width:13px;' class='showTip Player underlineit'>Content Type</th>-->
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><input type='text' name='EP' id='EP' size='4' align='middle' readonly disabled></td>
        <td><textarea name='EOC' id='EOC' size='175px' style="width:1070px;height:23px;" onfocus="expandTextBox(this);" onblur="restoreTextBox(this);" rows="20" cols="15"></textarea></td>
        <!--<td align='center'><select name='PLAYER' id='PLAYER'>
        	<option value="default">*</option>
        	<option SELECTED value="flv">[player video="*"]</option>
        </select></td>-->
      </tr>
    </tbody>
  </table>
  <br/>
  The entire form must be filled out before submitting it. If you can't find all the<br/>
  episodes please wait till you have all of them before completing this form<br/>
  Thank you.<br/>
<br/>
<b>Once you are ready:</b> (No going back after)<br/>
<input type='submit' value='Please wait...' onclick='return validate()' id='postformsubmit' disabled='true'/><div id='delaymessage'>(Disabled to prevent posts from overlaping. This will change in <?php echo $wait_time; ?> seconds. or <a href="#" onclick="enablesubmit(); return false;">Click Me If Timer Is Broken</a>)</div>
</form>

</div>

<script type="text/javascript">
var waittime = <?php echo $wait_time; ?>*1000; // X Seconds in Milliseconds
setTimeout("enablesubmit();",waittime);
// Enable Submit Button function
function enablesubmit()
{
document.getElementById("postformsubmit").disabled=false;
document.getElementById("postformsubmit").value="BEGIN POSTING";
document.getElementById("delaymessage").style.display="none";
}
</script>
<?php
//END FORM
} //End ba_mass_post_page function

//Add Posts Function
function mp_addposts($POST_array){
	//This function is called after a submit

// Security Check
	if(!current_user_can('mass_post')){
	return 'false';
	}

//Dump options into an array
$old_options_array = get_option('mass_post_options');

//Collect $_POST info (stored in $POST_array)
	$anime_name = $POST_array['anime-name'];
	switch ($POST_array['subdub']) {
		case 'none':
			$sub_dub = "";
			break;
		case 'subbed':
			$sub_dub = "Subbed";
			break;			
		case 'dubbed':
			$sub_dub = "Dubbed";
			break;
		case 'sub':
			$sub_dub = "(Sub)";
			break;
		case 'dub':
			$sub_dub = "(Dub)";
			break;
		case 'other':
			$sub_dub = " ".$POST_array['othervalue'];
			break;
	}
	$offset = $POST_array['ep_offset'];
	$post_count = $POST_array['MySel'];
	$category_array = $POST_array['post_category'];
	
	
	if($POST_array['auto-more']=='on'){
		$more_code = '<!--more--> ';
	}else{
		$more_code = '';
	}
	
	global $current_user;
	get_currentuserinfo();
	$current_author = $current_user->ID;
	$current_author_name = $current_user->user_login;

	$time = time();

//Compair current time to last used
	if($old_options_array["{$current_author_name}"]['last_used_time']){
		$compair_time_last = $old_options_array["{$current_author_name}"]['last_used_time'];
		if ($time-$post_count-5 <= $compair_time_last){
			$time = $compair_time_last + $post_count + 5;
			$note = 'true'; //Displays note at end
		}
	}

//A little time hack, goes back (5 + # of posts) Seconds
	$time = $time - $post_count - 5;

//Offset hack
$offset = $offset - 1;

//Creates $my_post array and fills with Post info, then posts using wp_insert_post()
	$my_post = array();
	
	//Define post values that are constant
	$my_post['post_status'] = 'publish';
	$my_post['post_author'] = $current_author;
	$my_post['post_category'] = $category_array;
	if($POST_array["finaltag"]=="on"){
		$finalTag = ' [Final]';
	}else{
		$finalTag = '';
	}
	
	//create posts
	for($i=1;$i <= $post_count; $i++){
		$eoc = 'EOC'.$i;
		$postcontent = $POST_array["{$eoc}"];
		$postnumber = $i + $offset;

		// Beginning of post title
		  $my_post['post_title'] = "{$anime_name} Episode {$postnumber}";
		  
		// Add finalTag to last post, $finalTag defined above
		  if($i == $post_count){
		  	$my_post['post_title'] .= $finalTag;
		  }
		  
		  $my_post['post_title'] .= " {$sub_dub}";
		  
			switch($POST_array['PLAYER'.$i]){
				case 'default':
					$my_post['post_content'] = $more_code.$postcontent;
					break;
				case 'flv':
					$my_post['post_content'] = $more_code.'[player video="'.$postcontent.'"]';
					break;
				default: //Shouldn't need to be here but just in case
					$my_post['post_content'] = $more_code.$postcontent;
					break;
			}

		  $time = $time + 1;
		  $my_post['post_date'] = date('Y-m-d H:i:s',$time);

		// Insert the post into the database
		  wp_insert_post( $my_post );	
	}
	
	//We no longer need the array so we unset it to save resources
	unset($my_post);

//Log usage in mass_post_options
	$newarray = array();
	$newarray["{$current_author_name}"]['last_used_date'] = date('m/d/y H:i:s',$time+5);
	$newarray["{$current_author_name}"]['last_used_count'] = $post_count;
	$newarray["{$current_author_name}"]['last_used_name'] = $anime_name;
	$newarray["{$current_author_name}"]['last_used_time'] = $time+5;
	$newarray["{$current_author_name}"]['total_times_used'] =

	$ba_options_array = array_merge($old_options_array,$newarray);
	update_option('mass_post_options', $ba_options_array);
	
	//We no longer need these arrays so we unset them to save resources
	unset($newarray);
	unset($old_options_array);
	unset($ba_options_array);

//Creates return array
	$return_array = array();
	$return_array['queries'] = get_num_queries();
	$return_array['time'] = timer_stop(0);
	$return_array['note'] = $note;
	$return_array['name'] = $anime_name;
	$return_array['count'] = $post_count;
	return $return_array;

} //End mp_addposts function


function ba_mass_post_options_page(){

$hidden_field_name = 'mt_submit_hidden2';

if( $_POST[ $hidden_field_name ] == 'Y' ) {
	//some function would go here to submit options
	$ba_options_backend_array = get_option('mass_post_options_backend');

	$newarray = array();
	$newarray['maxposts'] = $_POST['maxposts'];

	$ba_options_backend_array = array_merge($ba_options_backend_array,$newarray);
	update_option('mass_post_options_backend', $ba_options_backend_array);
?>

<div class="updated"><p><strong><?php _e('Options Updated.', 'mt_trans_domain'); ?></strong></p></div>

<?php
}

$backend_array = get_option('mass_post_options_backend');
?>

<div class="wrap">
<h2>Mass Post Options</h2>

<form action="<?php // echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post">

  <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<b>Max # of Posts:</b> <input type="text" size="23" id="maxposts" name="maxposts" value="<?php echo $backend_array['maxposts']; ?>"><br/>

  <input type="submit" value="Update Options">
</form>
<hr>
<h2>Usage</h2>
<pre>
Note: Only the last series posted by a user will show up here. Older usage is overwritten.
<?php
if(get_option('mass_post_options')){
	$optionsarray = get_option('mass_post_options');
}else{
$optionsarray = array();
}

if($_GET['debug']=='true'){print_r($optionsarray);}

foreach($optionsarray as $key => $user){
	$last_used_date = $user['last_used_date'];
	$last_used_count = $user['last_used_count'];
	$last_used_name = $user['last_used_name'];
	echo "{$key} || Added {$last_used_count} Posts of {$last_used_name} on {$last_used_date}.\n";
}
?>

</pre>
</div>
<?php
} //End ba_mass_post_options_page function
?>