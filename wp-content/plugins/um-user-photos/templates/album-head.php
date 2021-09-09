<?php
/**
 * Template for the UM User Photos, The "Album header" block
 *
 * Page: "Profile", tab "Photos"
 * Caller: User_Photos_Ajax->get_um_user_photos_single_album_view() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/album-head.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
$disable_title = UM()->options()->get( 'um_user_photos_disable_title' );
$disable_cover = UM()->options()->get( 'um_user_photos_disable_cover' );
?>

<div class="um-user-photos-album-head">
	<div class="col-back">
		<a href="javascript:void(0);"
			 class="back-to-um-user-photos"
			 data-action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=get_um_user_photos_view' ) ); ?>"
			 data-template="gallery"
			 data-profile="<?php echo esc_attr( $album->post_author ); ?>"
			 >
			<span class="um-icon-arrow-left-c"></span> <?php _e( 'Regresar', 'um-user-photos' ); ?>
		</a>
	</div>

	<div class="col-title">
		<div class="fusion-title title fusion-title-2 fusion-sep-none fusion-title-center fusion-title-highlight fusion-loop-off fusion-highlight-underline fusion-title-size-three" style="margin-top:30px;margin-right:0px;margin-bottom:50px;margin-left:0px;" data-highlight="underline" data-animationoffset="top-into-view"><h3 class="title-heading-center fusion-responsive-typography-calculated" style="margin:0;--fontSize:37;line-height:1.3;"><span class="fusion-highlighted-text-prefix"></span> <span class="fusion-highlighted-text-wrapper"><span class="fusion-highlighted-text"><?php
		if($disable_title != 1):
			echo esc_html( $album->post_title );
		endif; 
			?></span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="M8.1,146.2c0,0,240.6-55.6,479-13.8"></path></svg></span> <span class="fusion-highlighted-text-postfix"></span></h3></div>
	</div>

	<div class="col-delete">
		<?php if( $is_my_profile ) { ?>
			<a href="" class="um-user-photos-album-options"><i class="um-faicon-cog"></i></a>
			<div class="um-dropdown">
				<div class="um-dropdown-b">
					<div class="um-dropdown-arr"><i class="um-icon-arrow-up-b"></i></div>
					<ul>
						<li>
							<a href="javascript:void(0);"
								 data-trigger="um-user-photos-modal"
								 data-modal_title="<?php esc_attr_e( 'Editar album', 'um-user-photos' ); ?>"
								 data-modal_view="album-edit"
								 data-original-title="<?php esc_attr_e( 'Editar album', 'um-user-photos' ); ?>"
								 data-action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=get_um_user_photos_view' ) ); ?>"
								 data-template="modal/edit-album"
								 data-scope="edit"
								 data-edit="album"
								 data-id="<?php echo esc_attr( $album_id ); ?>"
								 >
									 <?php _e( 'Editar album', 'um-user-photos' ); ?>
							</a>
						</li>
						<li>
							<a href="javascript:void(0);"
								 data-trigger="um-user-photos-modal"
								 data-modal_title="<?php esc_attr_e( 'Eliminar album', 'um-user-photos' ); ?>"
								 data-modal_view="album-delete"
								 data-original-title="<?php esc_attr_e( 'Eliminar album', 'um-user-photos' ); ?>"
								 data-action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=get_um_user_photos_view' ) ); ?>"
								 data-template="modal/delete-album"
								 data-scope="edit"
								 data-edit="album"
								 data-id="<?php echo esc_attr( $album_id ); ?>"
								 >
									 <?php esc_html_e( 'Eliminar album', 'um-user-photos' ); ?>
							</a>
						</li>
						<li><a href="javascript:void(0);" class="um-dropdown-hide"><?php esc_html_e( 'Cancelar', 'um-user-photos' ); ?></a></li>
					</ul>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
