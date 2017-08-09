<?php 
/*
	Plugin Name: Facebook Like and Send
	Plugin URI: http://andreapernici.com/wordpress/facebook-send/
	Description: Add Facebook Like + Send to Wordpress Posts.
	Version: 1.0.1
	Author: Andrea Pernici
	Author URI: http://www.andreapernici.com/
	
	Copyright 2009 Andrea Pernici (andreapernici@gmail.com)
	
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

	*/

define( 'FBSEND_VERSION', '1.0.1' );

$pluginurl = plugin_dir_url(__FILE__);
if ( preg_match( '/^https/', $pluginurl ) && !preg_match( '/^https/', get_bloginfo('url') ) )
	$pluginurl = preg_replace( '/^https/', 'http', $pluginurl );
define( 'FBSEND_FRONT_URL', $pluginurl );

define( 'FBSEND_URL', plugin_dir_url(__FILE__) );
define( 'FBSEND_PATH', plugin_dir_path(__FILE__) );
define( 'FBSEND_BASENAME', plugin_basename( __FILE__ ) );

if (!class_exists("AndreaFacebookSend")) {

	class AndreaFacebookSend {
		/**
		 * Class Constructor
		 */
		function AndreaFacebookSend(){
		
		}
		
		/**
		 * Enabled the AndreaFacebookSend plugin with registering all required hooks
		 */
		function Enable() {

			add_action('admin_menu', array("AndreaFacebookSend",'FacebookSendMenu'));
			//add_action("wp_insert_post",array("AndreaFacebookSend","SetFacebookSendCode"));
			$options_after = get_option( 'fb_send_after_content' );
			$options_before = get_option( 'fb_send_before_title' );
			if ($options_after) {
				add_filter("the_content", array("AndreaFacebookSend","SetFacebookSendCodeFilter"));
			}
			if ($options_before) {
				add_action("loop_start",array("AndreaFacebookSend","SetFacebookSendCode"));
			}	
			
		}
		
		/**
		 * Set the Admin editor to set options
		 */
		 
		function SetAdminConfiguration() {
			add_action('admin_menu', array("AndreaFacebookSend",'FacebookSendMenu'));
			return true;			
		}
		
		function FacebookSendMenu() {
			add_options_page('FB Send Options', 'FB Like/Send', 'manage_options', 'fb-send-options', array("AndreaFacebookSend",'FacebookSendOptions'));
		}
		
		function FacebookSendOptions() {
			if (!current_user_can('manage_options'))  {
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}
			
		    // variables for the field and option names 
		    $fb_send_before_title = 'fb_send_before_title';
		    $fb_send_after_content = 'fb_send_after_content';
		    $hidden_field_name = 'mt_submit_hidden';
		    $data_field_name_before = 'fb_send_before_title';
		    $data_field_name_after = 'fb_send_after_content';
		
		    // Read in existing option value from database
		    $opt_val_before = get_option( $fb_send_before_title );
		    $opt_val_after = get_option( $fb_send_after_content );
		
		    // See if the user has posted us some information
		    // If they did, this hidden field will be set to 'Y'
		    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
		        // Read their posted value
		        $opt_val_before = $_POST[ $data_field_name_before ];
		    	$opt_val_after = $_POST[ $data_field_name_after ];
		
		        // Save the posted value in the database
		        update_option( $fb_send_before_title, $opt_val_before );
		        update_option( $fb_send_after_content, $opt_val_after );
		
		        // Put an settings updated message on the screen
		
		?>
		<div class="updated"><p><strong><?php _e('settings saved.', 'menu-fb-send' ); ?></strong></p></div>
		<?php
		
		    }
		    // Now display the settings editing screen
		    echo '<div class="wrap">';
		    // header
		    echo "<h2>" . __( 'Facebook Send/Like Options', 'menu-fb-send' ) . "</h2>";
		    // settings form
		    
		    ?>
		
		<form name="form1" method="post" action="">
		<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
		
		<?php $options_after = get_option( 'fb_send_before_title' ); ?>
		<p><?php _e("Show Before Title:", 'menu-fb-send' ); ?> 
		<input type="checkbox" name="fb_send_before_title" value="1"<?php checked( 1 == $options_after ); ?> />
		
		<?php $options_before = get_option( 'fb_send_after_content' ); ?>
		<p><?php _e("Show After Content:", 'menu-fb-send' ); ?> 
		<input type="checkbox" name="fb_send_after_content" value="1"<?php checked( 1 == $options_before ); ?> />

		<p class="submit">
		<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
		</p>
		
		</form>
		<?php echo "<h2>" . __( 'Put Function in Your Theme', 'menu-fb-send' ) . "</h2>"; ?>
		<p>If you want to put the box anywhere in your theme or you have problem showing the box simply use this function:</p>
		<p>if (function_exists('andrea_fb_send')) { andrea_fb_send(); }</p>
		</div>
		
		<?php

		}
		
		/**
		 * Setup Iframe Buttons for actions
		 */
		
		function SetFacebookSendCode() {
			
			$permalink = get_permalink();
			
			$button = '<div id="fb_send_like">';
			$button.= '<iframe src="http://www.facebook.com/plugins/like.php?href='.urlencode($permalink).'&amp;send=true&amp;layout=standard&amp;width=450&amp;show_faces=true&amp;action=like&amp;colorscheme=light&amp;font=arial&amp;height=80" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:80px;" allowTransparency="true"></iframe>';
			//$button.= '<iframe src="http://www.facebook.com/plugins/like.php?href='.urlencode($permalink).'&amp;send=true&amp;layout=standard&amp;width=450&amp;show_faces=true&amp;action=like&amp;colorscheme=light&amp;font&amp;height=80" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:80px;" allowTransparency="true"></iframe>';
			$button.= '</div>';
			
			echo $button;
		}		
		
		/**
		 * Setup Iframe Buttons for Filter
		 */
		
		function SetFacebookSendCodeFilter($content) {
			
			$permalink = get_permalink();
			
			$content.= '<div id="fb_send_like">';
			$content.= '<iframe src="http://www.facebook.com/plugins/like.php?href='.urlencode($permalink).'&amp;send=true&amp;layout=standard&amp;width=450&amp;show_faces=true&amp;action=like&amp;colorscheme=light&amp;font=arial&amp;height=80" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:80px;" allowTransparency="true"></iframe>';
			//$content.= '<iframe src="http://www.facebook.com/plugins/like.php?href='.urlencode($permalink).'&amp;send=true&amp;layout=standard&amp;width=450&amp;show_faces=true&amp;action=like&amp;colorscheme=light&amp;font&amp;height=80" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:80px;" allowTransparency="true"></iframe>';
			$content.= '</div>';
			
			return $content;
		}	
		
		/**
		 * Returns the plugin version
		 *
		 * Uses the WP API to get the meta data from the top of this file (comment)
		 *
		 * @return string The version like 1.0.0
		 */
		function GetVersion() {
			if(!function_exists('get_plugin_data')) {
				if(file_exists(ABSPATH . 'wp-admin/includes/plugin.php')) require_once(ABSPATH . 'wp-admin/includes/plugin.php'); //2.3+
				else if(file_exists(ABSPATH . 'wp-admin/admin-functions.php')) require_once(ABSPATH . 'wp-admin/admin-functions.php'); //2.1
				else return "0.ERROR";
			}
			$data = get_plugin_data(__FILE__);
			return $data['Version'];
		}
	
	}
}

/*
 * Plugin activation
 */
 
if (class_exists("AndreaFacebookSend")) {
	$afs = new AndreaFacebookSend();
}


if (isset($afs)) {
	add_action("init",array("AndreaFacebookSend","Enable"),1000,0);
	//add_action("wp_insert_post",array("AndreaFacebookSend","SetFacebookSendCode"));
}

if (!function_exists('andrea_fb_send')) {
	function andrea_fb_send() {
		$fb_send = new AndreaFacebookSend();
		return $fb_send->SetFacebookSendCode();
	}	
}
