<?php
/**
* Plugin Name: MarkTroll
* Plugin URI: http://h16free.com/plugin
* Description: A plugin for shadowbanning commenters
* Version: 0.1
* Author: h16
* Author URI: http://h16free.com
**/

/* 
 * For security as specified in
 * http://codex.wordpress.org/Writing_a_Plugin
 */
defined('ABSPATH') or die("No script kiddies please!");

/* 
 * Defs
 */
define( 'MARKTROLL_VERSION', '0.1' );
define( 'MARKTROLL_REQUIRED_WP_VERSION', '3.9' );
define( 'MARKTROLL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'MARKTROLL_PLUGIN_NAME', trim( dirname( MARKTROLL_PLUGIN_BASENAME ), '/' ) );
define( 'MARKTROLL_PLUGIN_URL', WP_PLUGIN_URL."/".dirname( plugin_basename( __FILE__ ) ) );
define( 'MARKTROLL_PLUGIN_DIR', WP_PLUGIN_DIR."/".dirname( plugin_basename( __FILE__ ) ) );
define( 'MARKTROLL_PLUGIN_MODULES_DIR', MARKTROLL_PLUGIN_DIR . '/modules' );

/***
 * Append the marktroll shadowban control at the end of the comment
 */
function marktroll_comment_is_no_troll(&$trolls,$comauthor,$comip,$comemail)
{	
	$cansee    = FALSE;
	$marked    = FALSE;
	$commenter = wp_get_current_commenter();

 	// sanitization
	if ($comip=="") $comip="999.999.999.999";
	if ($comemail=="") $comemail="--@--.--";

	// if user is logged in, can see
	$uri = $_SERVER['REQUEST_URI'] ;
	if (strpos($uri,"edit-comments.php")>0) $cansee=TRUE;
	
	// if cookie_comment_author = comment_author, can see
        if ($commenter['comment_author']==$comauthor) $cansee=TRUE;
	
	// if comment_author, comment_ip, comment_mail not in troll list, can see
	if (FALSE === strpos($trolls, $comauthor))
	{       
	   if (FALSE === strpos($trolls, $comip))
	   {
	      if (FALSE === strpos($trolls, $comemail)) $cansee=TRUE; else $marked=TRUE;
	   }
	   else
	   {
	      $marked = TRUE;
	   }
	}
	else 
	{
	   $marked = TRUE;
	}
	$val = 0;
	if ($marked) $val++;
	if ($cansee) $val |= 2;
	return $val;
}

function marktroll_comment_text($content) 
{
	$comauthor = get_comment_author();
	$comip     = get_comment_author_IP();
	$comemail  = get_comment_author_email();
	$options   = get_option('marktroll_options');
	$trolls    = $options['list'];

	$isnotroll = marktroll_comment_is_no_troll($trolls,$comauthor,$comip,$comemail);
	if (($isnotroll==0)||($isnotroll==1))
	{
	     return "";
	}
	if (($isnotroll==1)||($isnotroll==3))
	{
	   // add 'mark as troll' in comment's content
	   $uri = $_SERVER['REQUEST_URI'] ;
	   if (strpos($uri,"edit-comments.php")>0) $content = "<em>Marked as troll</em><br/>".$content;
	}
	return $content;
}

function marktroll_comment_filter($comments) 
{
	$options   = get_option('marktroll_options');
	$trolls    = $options['list'];

	$newcomments = array();
	foreach ($comments as $comment)
	{
	   $comauthor = $comment->comment_author;
	   $comip     = $comment->comment_author_IP;
	   $comemail  = $comment->comment_author_email;
	   $isnotroll = marktroll_comment_is_no_troll($trolls,$comauthor,$comip,$comemail);
	   if (($isnotroll!=0)&&($isnotroll!=1))
	   {
	      array_push($newcomments,$comment);
	   }
	}
	return $newcomments;
}
add_filter('comments_array', marktroll_comment_filter,10,1);
add_filter('comment_text', marktroll_comment_text);


/***
 * Creates a new action in the Comments row on the admin panel
 */

function mat_init()
{
   add_filter('comment_row_actions','mark_as_troll',10,2);
}
function mark_as_troll($actions, $comment) 
{
   $auth = $comment->comment_author;
   $cid  = $comment->comment_ID;
   $action = "<a title=\"Mark '{$auth}' as troll. Only him and admins will see this comment.\" href=\"".admin_url("admin-post.php?action=marktroll&cid={$cid}")."\">Mark as Troll</a>";
   $actions['mat'] = $action;
   return $actions;
}
add_action('admin_menu','mat_init');

add_action('admin_post_marktroll','marktroll',10,2);
function marktroll()
{
   status_header(200);
   $cid = $_GET['cid'];
  
   // load said comment
   $comment = get_comment($cid);

   // load previous trolls
   $options = get_option('marktroll_options');
   $trolls = $options['list'];

   // check if the user, ip and mail from current comment does not already exists in the list of previous trolls
   if (FALSE === strpos($trolls, $comment->comment_author))       $trolls .= $comment->comment_author."\n";
   if (FALSE === strpos($trolls, $comment->comment_author_email)) $trolls .= $comment->comment_author_email."\n";
   if (FALSE === strpos($trolls, $comment->comment_author_IP))    $trolls .= $comment->comment_author_IP."\n";

   // update trolls list
   $options['list'] = $trolls;
   update_option('marktroll_options',$options);

   // redirect to comments
   // die("Server received comment id {$cid} from browser.\n Troll list has been updated to : {$trolls}.");
   wp_redirect(admin_url('edit-comments.php'),302);
}

/***
 * Use Settings API to handle plugin settings
 */
add_action('admin_menu', 'marktroll_admin_add_page');
function marktroll_admin_add_page() {
	add_options_page(
		'Marktroll Settings',
		'Marktroll',
		'manage_options',
		'marktroll',
		'marktroll_options_page'
		);
}

function marktroll_options_page() {
	if(!current_user_can('manage_options')) {
		die('You do not have access to this page');
	}
	
	?>
	<div>
	<h2>MarkTroll</h2>
	<form action="options.php" method="post">
	<?php settings_fields('marktroll_options'); ?>
	<?php do_settings_sections('marktroll'); ?>
 
	<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
	</form></div>
 
	<?php
}

add_action('admin_init', 'marktroll_admin_init');
function marktroll_admin_init() {
	register_setting( 
		'marktroll_options',
		'marktroll_options',
		'marktroll_options_validate'
	);
	add_settings_section(
		'marktroll_main',
		'Settings',
		'marktroll_section_text',
		'marktroll'
	);
	add_settings_field(
		'marktroll_list',
		'Troll list',
		'marktroll_setting_list',
		'marktroll',
		'marktroll_main'
	);	
}

function marktroll_section_text() {
	echo '<p>List trolls below, using either IP, email or pseudo.</p>';
}

function marktroll_setting_list() {
	$options = get_option('marktroll_options');
	echo "<textarea id='marktroll_list' name='marktroll_options[list]' rows='20' cols='50' class='large-text-code'>{$options['list']}</textarea>";
	echo "<p class=\"description\">List of quarantined trolls. <strong>It is case sensitive</strong>.</p>";
}

function marktroll_options_validate($input) {
	$sanitized['list'] = trim($input['list']);
	return $sanitized;
} 
