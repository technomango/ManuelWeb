<?php
/**
 * Template for the User Photos
 *
 * Page: "Account", tab "My Photos"
 * Caller: UM()->Photos_API()->account()->um_account_content_hook_um_user_photos()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/account.php
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="um_user_photos_account">
	<p>
		<?php _e( 'Una vez que se eliminan las fotos y los álbumes, se eliminan de forma permanente y no se pueden recuperar.', 'um-user-photos' ); ?>
	</p>
	<p>
		<a id="um_user_photos_download_all"
			 class="um-button"
			 data-profile="<?php echo esc_attr( $user_id ); ?>"
			 data-wpnonce="<?php echo esc_attr( wp_create_nonce( 'um_user_photos_download_all' ) ); ?>"
			 href="<?php echo esc_url( admin_url( "admin-ajax.php?action=download_my_photos&profile_id=$user_id" ) ); ?>"><?php esc_html_e( 'Descargar Mis Fotos', 'um-user-photos' ); ?></a>

		<?php if ( $download_my_photos_notice ) : ?>
		<div class="um-field-error">
			<span class="um-field-arrow"><i class="um-faicon-caret-up"></i></span><?php echo esc_html( $download_my_photos_notice ); ?>
		</div>
	<?php endif; ?>

	<a id="um_user_photos_delete_all"
		 class="um-button danger"
		 data-profile="<?php echo esc_attr( $user_id ); ?>"
		 data-wpnonce="<?php echo esc_attr( wp_create_nonce( 'um_user_photos_delete_all' ) ); ?>"
		 data-alert_message="<?php esc_attr_e( '¿Estás seguro de eliminar todos tus álbumes y fotos?', 'um-user-photos' ); ?>"
		 href="<?php echo esc_url( admin_url( "admin-ajax.php?action=delete_my_albums_photos&profile_id=$user_id" ) ); ?>"><?php esc_html_e( 'Eliminar todos mis álbumes y fotos', 'um-user-photos' ); ?></a>
	</p>
</div>
<div class="um-clear"></div>
