<?php
/**
 * Template for the UM User Photos. The "Edit Image" modal content
 *
 * Call: UM()->Photos_API()->ajax()->get_um_ajax_gallery_view()
 * Page: "Profile", tab "Photos", modal "Edit Image"
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/modal/edit-image.php
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

$disable_comment = get_post_meta( $photo->ID, '_disable_comment', true );
?>

<div class="um-form">
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=update_um_user_photos_image' ) ); ?>" enctype="multipart/form-data">

		<div class="um-galley-form-response"></div>

		<div class="um-field">
			<label for="image-title-<?php echo esc_attr( $photo->ID ); ?>"><?php _e( 'Titulo de la Foto', 'um-user-photos' ); ?></label>
			<input id="image-title-<?php echo esc_attr( $photo->ID ); ?>" type="text" name="title" value="<?php echo esc_attr( $photo->post_title ); ?>" placeholder="<?php esc_attr_e( 'Titulo de la Foto', 'um-user-photos' ); ?>" title="<?php esc_attr_e( 'Titulo de la Foto', 'um-user-photos' ); ?>" required="required" />
		</div>

		<div class="um-field">
			<label for="image-caption-<?php echo esc_attr( $photo->ID ); ?>"><?php _e( 'Pie de Foto', 'um-user-photos' ); ?></label>
			<textarea id="image-caption-<?php echo esc_attr( $photo->ID ); ?>" name="caption" placeholder="<?php esc_attr_e( 'Pie de Foto', 'um-user-photos' ); ?>" title="<?php esc_attr_e( 'Pie de Foto', 'um-user-photos' ); ?>"><?php echo esc_html( $photo->post_excerpt ); ?></textarea>
		</div>

		<div class="um-field">
			<label for="image-link-<?php echo esc_attr( $photo->ID ); ?>"><?php _e( 'URL Relacionada', 'um-user-photos' ); ?></label>
			<input id="image-link-<?php echo esc_attr( $photo->ID ); ?>" type="text" name="link" value="<?php echo esc_attr( $photo->_link ); ?>" placeholder="<?php esc_attr_e( 'URL Relacionada', 'um-user-photos' ); ?>" title="<?php esc_attr_e( 'URL Relacionada', 'um-user-photos' ); ?>" />
		</div>

		<div class="um-field">
			<label>
				<input type="checkbox" name="disable_comments" value="1" <?php checked( $disable_comment ); ?> />
				<?php _e( 'Desactivar Comentarios', 'um-user-photos' ); ?>
			</label>
		</div>

		<div class="um-field um-user-photos-modal-footer text-right">
			<button type="button" id="um-user-photos-image-update-btn" class="um-modal-btn um-galley-modal-update"><?php _e( 'Actualizar', 'um-user-photos' ); ?></button>
			<a href="javascript:void(0);" class="um-modal-btn alt um-user-photos-modal-close-link"><?php _e( 'Cancelar', 'um-user-photos' ); ?></a>
		</div>

		<input type="hidden" name="id" value="<?php echo esc_attr( $photo->ID ); ?>"/>
		<input type="hidden" name="album" value="<?php echo esc_attr( $album->ID ); ?>"/>
		<?php wp_nonce_field( 'um_edit_image' ); ?>
	</form>
</div>
