<?php
/**
 * Template for the UM User Photos. The comment edit form.
 *
 * Page: "Profile", tab "Photos", the image popup
 * Call: UM()->Photos_API()->ajax()->get_um_user_photos_comment_edit()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/modal/edit-comment.php
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="um-form">
	<form method="post" action="update_um_user_photos_comment" enctype="multipart/form-data">

		<div class="um-galley-form-response"></div>

		<div class="um-field">
			<textarea class="um-user-photos-comment-textarea" name="comment_text" placeholder="<?php esc_attr_e( 'Write a comment...', 'um-user-photos' ); ?>"><?php echo esc_html( $comment->comment_content ); ?></textarea>
		</div>

		<div class="um-field um-user-photos-modal-footer text-right">

			<button type="button" id="um-user-photos-comment-update-btn" class="um-modal-btn um-galley-modal-update" data-commentid="<?php echo esc_attr( $comment->comment_ID ); ?>" ><?php esc_html_e( 'Update', 'um-user-photos' ); ?></button>

			<a href="javascript:void(0);" class="um-modal-btn alt um-user-photos-modal-close-link"><?php esc_html_e( 'Cancel', 'um-user-photos' ); ?></a>
		</div>

		<?php wp_nonce_field( 'um_edit_comment' ); ?>
	</form>
</div>