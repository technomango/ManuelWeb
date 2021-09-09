<?php
/**
 * Template for the UM User Photos. The "Album" block
 *
 * Call: UM()->Photos_API()->ajax()->um_user_photos_load_more()
 * Call: UM()->Photos_API()->ajax()->get_um_user_photos_single_album_view()
 * Page: "Profile", tab "Photos"
 * Parent template: photos.php
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/single-album.php
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! empty( $photos ) && is_array( $photos ) ) {
	?>

	<div class="um-user-photos-single-album um-up-grid <?php echo esc_attr( "um-up-grid-col-{$columns}" ); ?>">

		<?php
		for ( $i = 0; $i < count( $photos ); $i++ ) {
			$thumbnail_image = wp_get_attachment_image_src( $photos[ $i ], 'gallery_image' );
			if ( ! $thumbnail_image ) {
				continue;
			}
			$full_image = wp_get_attachment_image_src( $photos[ $i ], 'full' );
			$caption = wp_get_attachment_caption( $photos[ $i ] );
			$img_title = get_the_title( $photos[ $i ] );
			$img_link = get_post_meta( $photos[ $i ], '_link', true);

			if ( ! $is_my_profile ) { ?>

				<div class="um-user-photos-image-block um-up-cell">
					<?php if ( $img_link && esc_url( $img_link ) ) : ?>
						<div class="um-user-photos-image-block-buttons">
							<a href="<?php echo esc_url( $img_link ); ?>" title="<?php esc_attr_e( 'URL Relacionada', 'um-user-photos' ); ?>" target="_blank"><i class="um-faicon-link"></i></a>
						</div>
					<?php endif; ?>
					<div class="um-user-photos-image">
						<a data-caption="<?php echo esc_attr( $caption ); ?>" title="<?php echo esc_attr( $img_title ); ?>" href="<?php echo esc_url( $full_image[ 0 ] ); ?>" class="um-user-photos-image" data-id="<?php echo esc_attr( $photos[$i] ); ?>" data-umaction="open_modal">
							<img src="<?php echo esc_url( $thumbnail_image[ 0 ] ); ?>" alt="<?php echo esc_attr( $img_title ); ?>" />
						</a>
					</div>
				</div>

			<?php } else { ?>

				<div class="um-user-photos-image-block um-user-photos-image-block-editable um-up-cell">
					<div class="um-user-photos-image-block-buttons">
						<?php if ( $img_link && esc_url( $img_link ) ) : ?>
							<a href="<?php echo esc_url( $img_link ); ?>" title="<?php esc_attr_e( 'URL Relacionada', 'um-user-photos' ); ?>" target="_blank"><i class="um-faicon-link"></i></a>
						<?php endif; ?>
						<a href="javascript:void(0);"
						   data-trigger="um-user-photos-modal"
						   data-modal_title="<?php esc_attr_e( 'Editar Foto', 'um-user-photos' ); ?>"
						   data-modal_view="album-edit"
						   class="um-user-photos-add-link"
						   title="<?php esc_attr_e( 'Editar Foto', 'um-user-photos' ); ?>"
						   data-action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=get_um_user_photos_view' ) ); ?>"
						   data-template="modal/edit-image"
						   data-scope="edit" data-edit="image"
						   data-id="<?php echo esc_attr( $photos[ $i ] ); ?>"
						   <?php if ( ! empty( $album_id ) ) { ?>data-album="<?php echo esc_attr( $album_id ); ?>"<?php } ?> >
							<i class="um-faicon-pencil"></i>
						</a>
					</div>
					<div class="um-user-photos-image">
						<a data-caption="<?php echo esc_attr( $caption ); ?>"
						   data-id="<?php echo esc_attr( $photos[$i] ); ?>"
						   title="<?php echo esc_attr( $img_title ); ?>"
						   href="<?php echo esc_url( $full_image[ 0 ] ); ?>"
						   class="um-user-photos-image"
						   data-umaction="open_modal"
						   >
							<img src="<?php echo esc_url( $thumbnail_image[ 0 ] ); ?>" alt="<?php echo esc_attr( $img_title ); ?>" />
						</a>
					</div>
				</div>

				<?php
			}
		}
		?>

		<div class="um-clear"></div>
	</div>

	<?php } else { ?>
	<p class="text-center"><?php _e( 'No hay nada para mostrar', 'um-user-photos' ); ?></p>
	<?php
}