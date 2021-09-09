<?php
/**
 * Template for the UM User Photos, The "New Album" button
 *
 * Page: "Profile", tab "Photos"
 * Hook: 'ultimatemember_gallery'
 * Caller: User_Photos_Shortcodes->get_gallery_content() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/gallery-head.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="fusion-title title fusion-title-2 fusion-sep-none fusion-title-center fusion-title-highlight fusion-loop-off fusion-highlight-underline fusion-title-size-three" style="margin-top:30px;margin-right:0px;margin-bottom:50px;margin-left:0px;" data-highlight="underline" data-animationoffset="top-into-view"><h3 class="title-heading-center fusion-responsive-typography-calculated" style="margin:0;--fontSize:37;line-height:1.3;"><span class="fusion-highlighted-text-prefix"></span> <span class="fusion-highlighted-text-wrapper"><span class="fusion-highlighted-text">Albums de <?php 
$display_name = um_user('display_name');
echo $display_name; // prints the user's display name 
?></span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="M8.1,146.2c0,0,240.6-55.6,479-13.8"></path></svg></span> <span class="fusion-highlighted-text-postfix"></span></h3></div>
<div class="text-center um-user-photos-add">
	<a href="javascript:void(0);"
		 data-trigger="um-user-photos-modal"
		 data-modal_title="<?php esc_attr_e( 'Nuevo Album', 'um-user-photos' ); ?>"
		 data-modal_view="album-create"
		 class="um-user-photos-add-link fusion-button fusion-button-default fusion-button-default-size"
		 data-action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=get_um_user_photos_view' ) ); ?>"
		 data-template="modal/add-album"
		 data-scope="new">
		<i class="um-icon-plus"></i> <?php echo esc_html__( 'Nuevo Album', 'um-user-photos' ); ?>
	</a>
</div>