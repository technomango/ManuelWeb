<?php
/**
 * Template for the UM User Photos. Display members, who like this photo.
 *
 * Page: "Profile", tab "Photos", the image popup
 * Call: UM()->Photos_API()->ajax()->get_um_user_photo_likes()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/modal/likes.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( empty( $likes ) ) {
	_e( 'Nobody has liked this photo yet.', 'um-user-photos' );
} else {
	foreach ( $likes as $like ) {
		um_fetch_user( $like );
		?>
		<p class="um-user-photos-like-list-item">
			<a  target="_blank" href="<?php echo esc_url( um_user_profile_url( $like ) ); ?>">
				<?php echo get_avatar( esc_attr( $like ), 40 ); ?>
				<strong style="margin-left:10px;"><?php echo esc_html( um_user( 'display_name' ) ); ?></strong>
			</a>
		</p>
		<?php
	}
	um_reset_user();
}
?>

<div class="um-user-photos-modal-footer text-right">
	<a href="javascript:void(0);" class="um-modal-btn alt um-user-photos-modal-close-link">
		<?php _e( 'Close', 'um-user-photos' ); ?>
	</a>
</div>