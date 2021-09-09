<?php
namespace um_ext\um_user_photos\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class User_Photos_Shortcodes
 * @package um_ext\um_user_photos\core
 */
class User_Photos_Shortcodes {


	/**
	 * User_Photos_Shortcodes constructor.
	 */
	function __construct() {

		add_action( 'wp_enqueue_scripts', array( &$this, 'wp_enqueue_scripts' ), 9999 );

		add_shortcode( 'ultimatemember_gallery', array( $this, 'get_gallery_content' ) );
		add_shortcode( 'ultimatemember_gallery_photos', array( $this, 'gallery_photos_content' ) );

		if ( ! shortcode_exists( 'ultimatemember_albums' ) ) {
			add_shortcode( 'ultimatemember_albums', array( $this, 'get_albums_content' ) );
		}
	}


	function wp_enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';

		wp_register_style( 'um-images-grid', um_user_photos_url . 'assets/css/images-grid' . $suffix . '.css', array(),um_user_photos_version );
		wp_register_style( 'um-user-photos', um_user_photos_url . 'assets/css/um-user-photos' . $suffix . '.css', array( 'um-images-grid' ), um_user_photos_version );
		wp_register_script( 'um-images-grid', um_user_photos_url . 'assets/js/images-grid' . $suffix . '.js', array( 'jquery' ), um_user_photos_version, true );
		wp_register_script( 'um-user-photos', um_user_photos_url . 'assets/js/um-user-photos' . $suffix . '.js', array( 'wp-util', 'um-images-grid' ), um_user_photos_version, true );

		wp_localize_script('um-images-grid','user_photos_settings',[
			'disabled_comments' => UM()->options()->get( 'um_user_photos_disable_comments' )
		]);

		wp_enqueue_script( 'um-images-grid' );
		wp_enqueue_script( 'um-user-photos' );
		wp_enqueue_style( 'um-user-photos' );
		wp_enqueue_style( 'um-images-grid' );
	}


	/**
	 * Display common "Albums" block
	 *
	 * @param array $atts
	 * @return string
	 */
	function get_albums_content( $atts = array() ) {
		$output = '';

		$args = shortcode_atts( array(
			'column'    => 2,
			'page'      => 1,
			'per_page'  => 12
		), $atts, 'ultimatemember_albums' );

		$albums = new \WP_Query( array(
			'paged'             => $args['page'],
			'posts_per_page'    => $args['per_page'],
			'post_type'         => 'um_user_photos',
			'post_status'       => 'publish'
		) );

		if ( empty( $albums ) || ! $albums->have_posts() ) {
			return $output;
		}

		$sizes = UM()->options()->get( 'photo_thumb_sizes' );
		$args['size'] = isset( $sizes[2] ) ? intval( $sizes[2] ) : 190;
		$args['pages'] = (int) ceil( $albums->found_posts / $args['per_page'] );

		$args_t = compact( 'albums', 'args' );
		$output .= UM()->get_template( 'albums.php', um_user_photos_plugin, $args_t );

		wp_enqueue_script( 'um-user-photos' );
		wp_enqueue_style( 'um-user-photos' );

		return $output;
	}


	/**
	 * Display the "Albums" block
	 *
	 * @param array $atts
	 * @return string
	 */
	function get_gallery_content( $atts = array() ) {

		if ( ! empty( $atts ) ) {
			extract( $atts );
		}

		if ( empty( $user_id ) ) {
			$user_id = um_profile_id();
		}
		if ( isset( $_POST['user_id'] ) ) {
			$user_id = absint( $_POST['user_id'] );
		}

		$is_my_profile = is_user_logged_in() && get_current_user_id() == $user_id;

		$albums = new \WP_Query( array(
			'post_type'         => 'um_user_photos',
			'author__in'        => array( $user_id ),
			'posts_per_page'    => -1,
			'post_status'       => 'publish'
		) );

		$output = '';
		if( um_is_myprofile() || $is_my_profile ) {
			$args_t = compact( 'is_my_profile', 'user_id' );
			$output .= UM()->get_template( 'gallery-head.php', um_user_photos_plugin, $args_t );
			$output .= UM()->Photos_API()->shortcodes()->modal_template();
		}

		if ( $albums && $albums->have_posts() ) {
			$args_t = compact( 'albums', 'user_id' );
			$output .= UM()->get_template( 'gallery.php', um_user_photos_plugin, $args_t );
		}

		wp_enqueue_script( 'um-user-photos' );
		wp_enqueue_style( 'um-user-photos' );

		return $output;
	}


	/**
	 * Display the "Photos" block
	 *
	 * @param array $atts
	 * @return string
	 */
	function gallery_photos_content( $atts = array() ) {

		if ( ! empty( $atts ) ) {
			extract( $atts );
		}

		if ( empty( $user_id ) ) {
			$user_id = um_profile_id();
		}
		if ( isset( $_POST['user_id'] ) ) {
			$user_id = absint( $_POST['user_id'] );
		}

		$is_my_profile = is_user_logged_in() && get_current_user_id() == $user_id;

		$images_column = UM()->options()->get( 'um_user_photos_images_column' );
		if ( ! $images_column ) {
			$images_column = 'um-user-photos-col-3';
		}
		$images_row = UM()->options()->get( 'um_user_photos_images_row' );
		if ( ! $images_row ) {
			$images_row = 2;
		}
		$columns = intval( substr( $images_column, -1 ) );
		$rows = intval( $images_row );
		$per_page = $columns * $rows;

		// Disable posts query filter by the taxonomy 'language'. Integration with the plugin 'Polylang'.
		add_action( 'pre_get_posts', array( UM()->Photos_API()->common(), 'remove_language_filter' ), 9 );

		$latest_photos = new \WP_Query( array(
			'post_type'         => 'attachment',
			'author__in'        => array( $user_id ),
			'post_status'       => 'inherit',
			'post_mime_type'    => 'image',
			'posts_per_page'    => $per_page,
			'meta_query'        => array(
				array(
					'key'       => '_part_of_gallery',
					'value'     => 'yes',
					'compare'   => '=',
				)
			)
		) );

		if ( empty( $latest_photos ) || ! $latest_photos->have_posts() ) {
			return '';
		}

		$count = $latest_photos->found_posts;

		$photos = array();
		foreach ( $latest_photos->posts as $photo ) {
			$photos[] = $photo->ID;
		}

		$output = '';
		if( um_is_myprofile() || $is_my_profile ) {
			$output .= UM()->Photos_API()->shortcodes()->modal_template();
		}

		$args_t = compact( 'columns', 'count', 'is_my_profile', 'per_page', 'photos', 'user_id' );
		$output .= UM()->get_template( 'photos.php', um_user_photos_plugin, $args_t );

		wp_enqueue_script( 'um-user-photos' );
		wp_enqueue_style( 'um-user-photos' );

		return $output;
	}


	/**
	 * Print modal block once
	 *
	 * @since 2.0.6
	 *
	 * @staticvar boolean $is_printed
	 */
	public function modal_template() {
		static $is_printed = false;
		if( !$is_printed ){
			$is_printed = true;
			return UM()->get_template( 'modal/modal.php', um_user_photos_plugin, array(), true );
		}
	}

}
