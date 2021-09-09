<?php
namespace um_ext\um_user_photos\admin;

if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'um_ext\um_user_photos\admin\Admin' ) ) {


	/**
	 * Class Admin
	 * @package um_ext\um_user_photos\admin
	 */
	class Admin {


		/**
		 * Admin constructor.
		 */
		function __construct() {

		add_filter( 'um_settings_structure', array( &$this, 'extend_settings' ), 10, 1 );
		add_filter( 'um_admin_role_metaboxes', array( &$this, 'um_user_photos_add_role_metabox' ), 10, 1 );

		}


		/**
		 * Additional Settings for Photos
		 *
		 * @param array $settings
		 *
		 * @return array
		 */
		function extend_settings( $settings ) {

			$settings['licenses']['fields'][] = array(
				'id'        => 'um_user_photos_license_key',
				'label'     => __( 'User Photos License Key', 'um-user-photos' ),
				'item_name' => 'User Photos',
				'author'    => 'ultimatemember',
				'version'   => um_user_photos_version,
			);

			$key = ! empty( $settings['extensions']['sections'] ) ? 'um-user-photos' : '';
			$settings['extensions']['sections'][ $key ] = array(
				'title'     => __( 'User Photos', 'um-user-photos' ),
				'fields'    => array(
					array(
						'id'            => 'um_user_photos_albums_column',
						'type'          => 'select',
						'placeholder'   => '',
						'options'       => array(
							''                      => __( 'No. of columns', 'um-user-photos' ),
							'um-user-photos-col-2'  => __( '2 columns', 'um-user-photos' ),
							'um-user-photos-col-3'  => __( '3 columns', 'um-user-photos' ),
							'um-user-photos-col-4'  => __( '4 columns', 'um-user-photos' ),
							'um-user-photos-col-5'  => __( '5 columns', 'um-user-photos' ),
							'um-user-photos-col-6'  => __( '6 columns', 'um-user-photos' ),
						),
						'label'         => __( 'Album columns', 'um-user-photos' ),
						'size'          => 'medium',
					),
					array(
						'id'            => 'um_user_photos_images_column',
						'type'          => 'select',
						'options'       => array(
							''                      => __( 'No. of columns', 'um-user-photos' ),
							'um-user-photos-col-2'  => __( '2 columns', 'um-user-photos' ),
							'um-user-photos-col-3'  => __( '3 columns', 'um-user-photos' ),
							'um-user-photos-col-4'  => __( '4 columns', 'um-user-photos' ),
							'um-user-photos-col-5'  => __( '5 columns', 'um-user-photos' ),
							'um-user-photos-col-6'  => __( '6 columns', 'um-user-photos' ),
						),
						'label'         => __( 'Photo columns', 'um-user-photos' ),
						'size'          => 'medium',
					),
					array(
						'id'            => 'um_user_photos_images_row',
						'type'          => 'select',
						'options'       => array(
							'1'  => __( 'Single row', 'um-user-photos' ),
							'2'  => __( '2 rows', 'um-user-photos' ),
							'3'  => __( '3 rows', 'um-user-photos' ),
							'4'  => __( '4 rows', 'um-user-photos' ),
							'5'  => __( '5 rows', 'um-user-photos' ),
							'6'  => __( '6 rows', 'um-user-photos' ),
						),
						'label'         => __( 'Photo rows', 'um-user-photos' ),
						'size'          => 'medium',
					),
					array(
						'id'            => 'um_user_photos_cover_size',
						'type'          => 'text',
						'placeholder'   => __( 'Default : 350 x 350', 'um-user-photos' ),
						'label'         => __( 'Album Cover size', 'um-user-photos' ),
						'tooltip'       => __( 'You will need to regenerate thumbnails once this value is changed', 'um-user-photos' ),
						'size'          => 'small',
					),
					array(
						'id'            => 'um_user_photos_image_size',
						'type'          => 'text',
						'placeholder'   => __( 'Default : 250 x 250', 'um-user-photos' ),
						'label'         => __( 'Photo thumbnail size', 'um-user-photos' ),
						'tooltip'       => __( 'You will need to regenerate thumbnails once this value is changed', 'um-user-photos' ),
						'size'          => 'small',
					),
					array(
						'id'            => 'um_user_photos_disable_cover',
						'type'          => 'checkbox',
						'label'         => __( 'Disable cover photo', 'um-user-photos' ),
						'tooltip'       => __( 'Album cover field will be hidden.', 'um-user-photos' ),
						'size'          => 'small',
					),
					array(
						'id'            => 'um_user_photos_disable_title',
						'type'          => 'checkbox',
						'label'         => __( 'Disable title', 'um-user-photos' ),
						'tooltip'       => __( 'Title field will be hidden.', 'um-user-photos' ),
						'size'          => 'small',
					),
					array(
						'id'            => 'um_user_photos_disable_comments',
						'type'          => 'checkbox',
						'placeholder'   => __( 'Disable comments', 'um-user-photos' ),
						'label'         => __( 'Disable comment & Like feature', 'um-user-photos' ),
						'tooltip'       => __( 'Disable comments and like features', 'um-user-photos' ),
						'size'          => 'small',
					),
					/*array(
						'id'            => 'um_user_photos_enable_upload_limit',
						'type'          => 'checkbox',
						'placeholder'   => __( 'Enable upload limit', 'um-user-photos' ),
						'label'         => __( 'Enable upload limit', 'um-user-photos' ),
						'tooltip'       => __( 'Lets admin set max number of photos user can upload per day.', 'um-user-photos' ),
						'size'          => 'small',
					),
					array(
						'id'            => 'um_user_photos_max_upload_num',
						'type'          => 'text',
						'placeholder'   => __( 'No. of photos', 'um-user-photos' ),
						'label'         => __( 'Max number of photos', 'um-user-photos' ),
						'tooltip'       => __( 'Max number of photos a user can upload per day', 'um-user-photos' ),
						'size'          => 'small',
						'conditional'   => array( 'um_user_photos_enable_upload_limit', '=', 1 ),
					)*/
				)
			);

			return $settings;
		}





		/**
		 * @param $roles_metaboxes
		 *
		 * @return array
		 */

		function um_user_photos_add_role_metabox( $roles_metaboxes ){


			$roles_metaboxes[] = array(
				'id'        => "um-admin-form-photos{" . um_user_photos_path . "}",
				'title'     => __( 'User Photos', 'um-user-photos' ),
				'callback'  => array( UM()->metabox(), 'load_metabox_role' ),
				'screen'    => 'um_role_meta',
				'context'   => 'normal',
				'priority'  => 'default'
			);

			return $roles_metaboxes;


		}

	}
}
