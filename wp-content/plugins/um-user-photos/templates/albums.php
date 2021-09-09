<?php
/**
 * Template for the UM User Photos, common "Albums" block
 *
 * Shortcode: [ultimatemember_albums]
 * Call: UM()->Photos_API()->shortcodes()->get_albums_content()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/albums.php
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $albums ) || !$albums->have_posts() ) {
	return;
}
?>

<div class="um ultimatemember_albums" data-um-pagi-action="um_user_photos_get_albums_content" data-um-pagi-column="<?php echo esc_attr( $args['column'] ); ?>" data-um-pagi-per_page="<?php echo esc_attr( $args['per_page'] ); ?>">

	<?php do_action( 'ultimatemember_albums_before', $albums, $args ); ?>

	<div class="grid-row grid-row-<?php echo esc_attr( $args['column'] ); ?>">

		<?php
		foreach ( $albums->posts as $i => $album ) {
			$img = UM()->Photos_API()->common()->um_photos_get_album_cover( $album->ID );
			$photos = $album->_photos;
			$user = get_userdata( $album->post_author );
			$prifile_url = um_user_profile_url( $album->post_author );
			$disable_title = UM()->options()->get( 'um_user_photos_disable_title' );
			?>

			<div class="grid-item">
				<div class="um-user-photos-album">

					<?php do_action( 'ultimatemember_albums_item_before', $album, $args ); ?>

					<a href="<?php echo esc_url( add_query_arg( 'profiletab', 'photos', $prifile_url ) ); ?>" class="um-user-photos-album-link" original-title="<?php echo esc_attr( $album->post_title ); ?>">
						<div class="album-overlay">
							<p class="album-title">
								<strong><?php if( $disable_title != 1 ) echo esc_html( $album->post_title ); ?></strong>
								<?php if ( $photos ) { ?>
									<small> - <?php echo esc_html( sprintf( _n( '%s Foto', '%s Fotos', count( $photos ), 'um-user-photos' ), number_format_i18n( count( $photos ) ) ) ); ?></small>
								<?php } ?>
							</p>
						</div>
						<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $album->post_title ); ?>"/>
					</a>

					<div class="um-member-photo">
						<a href="<?php echo esc_url( $prifile_url ); ?>" title="<?php echo esc_attr( $user->display_name ); ?>"><?php echo get_avatar( $album->post_author, $args['size'] ); ?></a>
					</div>

					<div class="um-member-card ">
						<div class="um-member-name">
							<a href="<?php echo esc_url( $prifile_url ); ?>/" title="<?php echo esc_attr( $user->display_name ); ?>"><?php echo esc_html( $user->display_name ); ?></a>
						</div>
					</div>

					<?php do_action( 'ultimatemember_albums_item_after', $album, $args ); ?>

				</div>
			</div>
			<?php
		}
		?>

	</div>

	<?php
	if ( $albums->found_posts > $args['per_page'] ) {
		UM()->get_template( 'pagination.php', um_user_photos_plugin, $args, true );
	}
	?>

	<?php do_action( 'ultimatemember_albums_after', $albums, $args ); ?>

</div>
