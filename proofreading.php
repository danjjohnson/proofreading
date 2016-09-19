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
Text Domain: proofreading
Domain Path: /languages
*/

class Proofreading {

	function __construct() {

		add_filter( 'the_content', array( $this, 'output_suggestion_form' ) );
		add_action( 'init', array( $this , 'add_new_suggestion' ) );
		add_action( 'add_meta_boxes', array( $this , 'add_suggestion_meta_box' ) );
		error_log(print_r($_POST, true));

	}

	function output_suggestion_form( $content ) {
		$postid = get_the_ID();
		$form_title = '<h3>' . esc_attr__( 'Spotted a typo? Let us know about it.', 'proofreading') . '</h3>';
		$form = '<form method="POST" action="" class="typo-suggestion-form">
					<input type="text" name="suggester_name" placeholder="' . esc_attr__( 'Your name', 'proofreading' ) . '" />
					<input type="text" name="suggester_email" placeholder="' . esc_attr__( 'Your email', 'proofreading' ) . '" />
					<textarea name="suggestion" rows=4 maxlength=100 placeholder="' . esc_attr__( 'Please enter a correction for this post', 'proofreading' ) . '"></textarea>
					<input type="hidden" name="postid" value="' . $postid . '" />' 
					. wp_nonce_field( 'add_suggestion_to' . $postid, '_suggestion_nonce' ) 
					. '<input type="submit" text="' . esc_attr__( 'Suggest', 'proofreading' ) . '" />
				</form>';
		$content .= $form_title;
		$content .= $form;
		return $content;
	}

	function add_new_suggestion() {

		if( isset($_POST) && ! empty($_POST) ) {
			if ( wp_verify_nonce( $_POST["_suggestion_nonce"], 'add_suggestion_to' . $_POST["postid"] ) ) {

				$userid = get_current_user_id();
				$postid = $_POST["postid"];
				$name = $_POST["suggester_name"];
				$email = $_POST["suggester_email"];
				$suggestion = $_POST["suggestion"];

				$commentdata = array(
					'comment_post_ID' => absint( $postid ),
					'comment_author' => esc_html( $name ),
					'comment_author_email' => esc_html( $email ),
					'comment_author_url' => '',
					'comment_content' => esc_html( $suggestion ),
					'comment_type' => 'suggestion',
					'comment_parent' => 0,
					'user_id' => absint( $userid ),
					'comment_approved' => 'suggested',
					'comment_status' => 'suggested'
				);

				//Insert new comment and get the comment ID
				wp_insert_comment( $commentdata );
			}
		}
	}

	function add_suggestion_meta_box() {
		global $post;
		$postid = $post->ID;
		add_meta_box(
	        'suggestions',
	        __( 'Suggestions', 'proofreading' ),
	        array( $this, 'display_suggestions_in_post_edit_screen'),
	        'post',
	        'normal',
	        'low',
	        array( 'id' => $postid )
	    );
	}

	function display_suggestions_in_post_edit_screen( $post, $metabox ) {
		$args = array(
			'status' => 'suggested',
			'post_id' => $metabox['args']['id'],
			'type' => 'suggestion'
		);
		$suggestions = get_comments($args);
		if( is_array($suggestions) && empty($suggestions) ) {
			esc_attr_e('No suggestions yet.', 'proofreading');
		} else { ?>

			<ul class="suggestion-list">

			<?php foreach($suggestions as $suggestion) :
				echo( '<li>' . sprintf( 'Name: %s', esc_html( $suggestion->comment_author ) ) . '<br />' . sprintf( 'Suggestion: %s', esc_html( $suggestion->comment_content ) ) . '</li>');
			endforeach; ?>
			
			</ul>

			<?php
		}
	}
}

new Proofreading();
