<?php
/**
 * Template for the UM User Photos, The "Photos" block
 *
 * Page: "Profile", tab "Photos"
 * Caller: User_Photos_Shortcodes->gallery_photos_content() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/photos.php
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! empty( $photos ) && is_array( $photos ) ) {
	?>
<div class="fusion-title title fusion-title-2 fusion-sep-none fusion-title-center fusion-title-highlight fusion-loop-off fusion-highlight-underline fusion-title-size-three" style="margin-top:30px;margin-right:0px;margin-bottom:50px;margin-left:0px;" data-highlight="underline" data-animationoffset="top-into-view"><h3 class="title-heading-center fusion-responsive-typography-calculated" style="margin:0;--fontSize:37;line-height:1.3;"><span class="fusion-highlighted-text-prefix"></span> <span class="fusion-highlighted-text-wrapper"><span class="fusion-highlighted-text">Fotos de <?php 
$display_name = um_user('display_name');
echo $display_name; // prints the user's display name 
?></span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="M8.1,146.2c0,0,240.6-55.6,479-13.8"></path></svg></span> <span class="fusion-highlighted-text-postfix"></span></h3></div>
	<div class="um-user-photos-albums">
		<div class="photos-container">

			<?php
			$args_t = compact( 'columns', 'is_my_profile', 'photos' );
			UM()->get_template( 'single-album.php', um_user_photos_plugin, $args_t, true );
			?>

		</div>

		<?php if ( $count > $per_page ) { ?>
			<div class="um-load-more">
				<div class="um-clear">
					<hr/>
				</div>
				<p class="text-center">
				<button id="um-user-photos-toggle-view-photos-load-more" data-href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=um_user_photos_load_more' ) ); ?>" class="um-modal-btn alt" data-current_page="1" data-per_page="<?php echo esc_attr( $per_page ); ?>" data-profile="<?php echo esc_attr( $user_id ); ?>"><?php _e( 'Load more', 'um-user-photos' ); ?></button>
				</p>
			</div>
		<?php } ?>

		<div class="um-clear"></div>
	</div>

	<?php } else { ?>
	<p class="text-center"><?php _e( 'Nothing to display', 'um-user-photos' ) ?></p>
	<?php
}