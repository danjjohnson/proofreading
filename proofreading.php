<?php
/*
Plugin Name: Proofreading Plugin
Plugin URI:  https://
Description: Allow users to suggest edits on a post
Version:     1.0
Author:      Dan Johnson
Author URI:  
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: 
Domain Path: /languages
*/

class Proofreading {

	function __construct() {

		add_filter( 'the_content', array( $this, 'output_suggestion_form' ) );
		add_action( 'init', array( $this , 'add_new_suggestion' ) );
		error_log(print_r($_POST, true));

	}

	function output_suggestion_form( $content ) {
		$postid = get_the_ID();
		$form = '<form method="POST" action="" class="typo-suggestion-form">
					<input type="text" name="suggester_name" placeholder="Your name" />
					<input type="text" name="suggester_email" placeholder="Your email" />
					<textarea name="suggestion" rows=4 maxlength=100 placeholder="Please enter a correction for this post"></textarea>
					<input type="hidden" name="postid" value="' . $postid . '" />' 
					. wp_nonce_field( 'add_suggestion', '_suggestion_nonce' ) 
					. '<input type="submit" text="Suggest" />
				</form>';
		$content .= $form;
		return $content;
	}

	function add_new_suggestion() {

		if( isset($_POST) && ! empty($_POST) ) {
			if ( wp_verify_nonce( $_POST["_suggestion_nonce"], 'add_suggestion' ) ) {

				$userid = get_current_user_id();
				$postid = $_POST["postid"];
				$name = $_POST["suggester_name"];
				$email = $_POST["suggester_email"];
				$suggestion = $_POST["suggestion"];

				$commentdata = array(
					'comment_post_ID' => $postid,
					'comment_author' => $name,
					'comment_author_email' => $email,
					'comment_author_url' => '',
					'comment_content' => $suggestion,
					'comment_type' => 'suggestion',
					'comment_parent' => 0,
					'user_id' => $userid
				);

				//Insert new comment and get the comment ID
				wp_new_comment( $commentdata );
			}
		}
	}
}

$proofreading = new Proofreading();