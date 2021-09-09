<?php
/**
 * Template for the UM User Photos. The image caption and comments block.
 *
 * Call: UM()->Photos_API()->ajax()->um_user_photos_get_comment_section()
 * Page: "Profile", tab "Photos", the image popup
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/caption.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$image = get_post( $image_id );
um_fetch_user( $image->post_author );

$likes = get_post_meta( $image_id, '_liked', true );
if ( empty( $likes ) ) {
	$likes = array();
}

$img_link = get_post_meta( $image_id, '_link', true );
$disable_comments = get_post_meta( $image_id, '_disable_comment', true );
$comment_count = $disable_comments ? 0 : $image->comment_count;
$disable_caption = UM()->options()->get( 'um_user_photos_disable_comments' );
$count_likes = count( $likes );
if( $disable_caption != 1):
?>
<div class="um-user-photos-widget" id="postid-<?php echo esc_attr( $image_id ); ?>">

	<div class="um-user-photos-head">

		<div class="um-user-photos-right">
		<?php if ( $img_link && esc_url( $img_link ) ) : ?>
			<div class="um-user-photos-link">
				<a href="<?php echo esc_url( $img_link ); ?>" title="<?php esc_attr_e( 'Related link', 'um-user-photos' ); ?>" target="_blank"><?php _e( 'Related link', 'um-user-photos' ); ?> <i class="um-faicon-link"></i></a>
			</div>
		<?php endif; ?>
		</div>

		<div class="um-user-photos-left um-user-photos-author">
			<div class="um-user-photos-ava">
				<a href="<?php echo esc_url( um_user_profile_url() ); ?>">
					<?php echo get_avatar( $image->post_author, 80 ); ?>
				</a>
			</div>
			<div class="um-user-photos-author-meta">
				<div class="um-user-photos-author-url">
					<a href="<?php echo esc_url( um_user_profile_url() ); ?>" class="um-link">
						<?php echo um_user( 'display_name', 'html' ); ?>
					</a>
				</div>
				<span class="um-user-photos-metadata">
					<a href="#" title="<?php echo $image->post_date; ?>">
						<?php echo UM()->Photos_API()->human_time_diff( get_the_time( 'U', $image->ID ), current_time( 'timestamp' ) ); ?>
					</a>
				</span>
			</div>
		</div>

		<div class="um-clear"></div>
	</div>


	<div class="um-user-photos-body">
		<div class="um-user-photos-bodyinner">
			<div class="um-user-photos-bodyinner-txt">
				<?php echo wp_get_attachment_caption( $image_id ); ?>
			</div>

			<div class="um-user-photos-bodyinner-photo"></div>
			<div class="um-user-photos-bodyinner-video"></div>
		</div>

		<div class="um-user-photos-disp">
			<div class="um-user-photos-left">

				<div class="um-user-photos-disp-likes">
					<a data-template="modal/photo-likes"
						 data-modal_title="<?php esc_attr_e( 'Likes', 'um-user-photos' ); ?>"
						 href="javascript:void(0);"
						 class="um-user-photos-show-likes um-link"
						 data-id="<?php echo esc_attr( $image_id ); ?>">
						<span class="um-user-photos-post-likes"><?php echo $count_likes; ?></span>
						<span class="um-user-photos-disp-span"><?php esc_html_e( 'Likes', 'um-user-photos' ); ?></span>
					</a>
				</div>

				<div class="um-user-photos-disp-comments">
					<a href="javascript:void(0);" class="um-link">
						<span class="um-user-photos-post-comments"><?php echo esc_html( $comment_count ); ?></span>
						<span class="um-user-photos-disp-span"><?php esc_html_e( 'Comments', 'um-user-photos' ); ?></span>
					</a>
				</div>

			</div>
			<div class="um-clear"></div>
		</div>

		<div class="um-clear"></div>
	</div>

	<?php
	if ( is_user_logged_in() ):
		$user_id = get_current_user_id();
		$has_liked = in_array( $user_id, $likes );
		?>
		<div class="um-user-photos-foot status" id="photoid-<?php echo esc_attr( $image_id ); ?>">

			<div class="um-user-photos-left um-user-photos-actions">
				<div class="um-user-photos-like <?php if ( $has_liked ) { echo 'active'; } ?>" data-like_text="<?php _e( 'Like', 'um-user-photos' ); ?>" data-unlike_text="<?php _e( 'Unlike', 'um-user-photos' ); ?>">

					<a href="javascript:void(0);">
						<?php if ( !$has_liked ): ?>
							<i class="um-faicon-thumbs-up"></i>
							<span class=""><?php _e( 'Like', 'um-user-photos' ); ?></span>
						<?php else: ?>
							<i class="um-faicon-thumbs-up um-effect-pop um-active-color"></i>
							<span class=""><?php _e( 'Unlike', 'um-user-photos' ); ?></span>
						<?php endif; ?>
					</a>
				</div>

				<?php if ( UM()->Photos_API()->can_comment() && !$disable_comments ) { ?>
					<div class="um-user-photos-comment"><a href="javascript:void(0);"><i class="um-faicon-comment"></i><span class=""><?php _e( 'Comment', 'um-user-photos' ); ?></span></a></div>
				<?php } ?>
			</div>

			<div class="um-clear"></div>
		</div>
	<?php endif; ?>


	<?php if ( !$disable_comments ) { ?>
		<div class="um-user-photos-comments">
			<?php
			UM()->get_template( 'comment-form.php', um_user_photos_plugin, array(
				'image_id' => $image_id,
				), true );

			UM()->get_template( 'comments.php', um_user_photos_plugin, array(
				'image_id' => $image_id,
				), true );
			?>
		</div>
	<?php } ?>

</div>

<?php
um_reset_user();
endif;
