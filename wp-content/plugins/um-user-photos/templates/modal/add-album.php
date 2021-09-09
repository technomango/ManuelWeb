<?php
/**
 * Template for the UM User Photos, the "New Album" modal content
 *
 * Page: "Profile", tab "Photos"
 * Caller: User_Photos_Ajax->get_um_ajax_gallery_view() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/modal/add-album.php
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
$disable_title = UM()->options()->get( 'um_user_photos_disable_title' );
$disable_cover = UM()->options()->get( 'um_user_photos_disable_cover' );
?>

<div class="um-form">
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=create_um_user_photos_album' ) ); ?>" enctype="multipart/form-data" class="um-user-photos-modal-form"  data-max_size_error="<?php _e( 'es demasiado largo. El archivo debe ser menor que ', 'um-user-photos' ); ?>" data-max_size="<?php echo esc_attr( wp_max_upload_size() ); ?>">

		<div class="um-galley-form-response"></div>

		<?php if($disable_title != 1): ?>
			<div class="um-field">
				<input type="text" name="title" placeholder="<?php _e( 'Nombre del Album', 'um-user-photos' ); ?>" required/>
			</div>
		<?php endif; ?>

		<div class="um-field">
			<?php if($disable_cover != 1): ?>
			<div class="text-center">
				<h1 class="album-poster-holder">
					<label class="album-poster-label">
						<i class="um-faicon-picture-o"></i><br/>
						<span><?php _e( 'Portada del álbum', 'um-user-photos' ); ?></span>
						<input id="um-user-photos-input-album-cover" style="display:none;" type="file" name="album_cover" accept="image/*" />
					</label>
				</h1>
			</div>
			<?php endif; ?>
			<div id="um-user-photos-images-uploaded"></div>
			<div class="clearfix"></div>
		</div>

		<div class="um-field um-user-photos-modal-footer text-right">
			<button type="button" class="um-modal-btn um-galley-modal-submit"><?php _e( 'Publicar', 'um-user-photos' ); ?></button>
			<label class="um-modal-btn alt">
				<i class="um-icon-plus"></i>
				<?php _e( 'Seleccionar fotos', 'um-user-photos' ); ?>
				<input id="um-user-photos-input-album-images" style="display:none;" type="file" name="album_images[]" accept="image/*" multiple />
			</label>
			<a href="javascript:void(0);" class="um-modal-btn alt um-user-photos-modal-close-link"><?php _e( 'Cancelar', 'um-user-photos' ); ?></a>
		</div>
		<?php wp_nonce_field( 'um_add_album' ); ?>
	</form>
</div>
