<?php
/**
 * Template for the UM User Photos. The comments block
 *
 * Page: "Profile", tab "Photos", the image popup
 * Parent template: caption.php
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/comments.php
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="um-user-photos-comments-loop">
	<?php
		$comments = get_comments([
			'post_id' => $image_id,
			'type'	  => 'um-user-photos',
			'orderby' => 'comment_ID',
			'order'	  => 'DESC',
			'parent' => 0
		]);
	if ( ! empty( $comments ) ) {
		foreach ( $comments as $comment ) {

			$is_url = filter_var( $comment->comment_content, FILTER_VALIDATE_URL );
			$content = $is_url ? '<a href="' . esc_url( $comment->comment_content ) . '" target="_blank">' . esc_html( $comment->comment_content ) . '</a>' : esc_html( $comment->comment_content );

			UM()->get_template( 'comment.php', um_user_photos_plugin, array(
				'user_id'   => $comment->user_id,
				'content'   => $content,
				'date'      => $comment->comment_date,
				'id'        => $comment->comment_ID,
				'image_id'  => $image_id,
				'comment'	  => $comment,
			), true );
		}
	} ?>
</div>