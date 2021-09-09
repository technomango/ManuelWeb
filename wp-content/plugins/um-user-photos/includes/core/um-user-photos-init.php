<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_Photos_API
 */
class UM_Photos_API {


	/**
	 * @var
	 */
	private static $instance;


	/**
	 * @return UM_Photos_API
	 */
	static public function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * UM_Photos_API constructor.
	 */
	function __construct() {
		add_action( 'plugins_loaded', array( &$this, 'init' ), 0 );
		add_filter( 'um_call_object_Photos_API', array( &$this, 'get_this' ) );

		$this->admin();
		$this->shortcodes();
		$this->common();
		$this->profile();
		$this->account();
		$this->ajax();
		$this->activity();

		add_filter( 'um_settings_default_values', array( &$this, 'default_settings' ), 10, 1 );
	}


	/**
	 * Actions and filters
	 */
	function init() {

		require_once um_user_photos_path . 'includes/core/um-user-photos-notifications.php';
	}


	/**
	 * @param $defaults
	 *
	 * @return array
	 */
	function default_settings( $defaults ) {
		$defaults = array_merge( $defaults, $this->setup()->settings_defaults );
		return $defaults;
	}


	/**
	 * @return um_ext\um_user_photos\core\User_Photos_Setup()
	 */
	function setup() {
		if ( empty( UM()->classes['um_photos_setup'] ) ) {
			UM()->classes['um_photos_setup'] = new um_ext\um_user_photos\core\User_Photos_Setup();
		}
		return UM()->classes['um_photos_setup'];
	}


	/**
	 * @return um_ext\um_user_photos\admin\Admin()
	 */
	function admin() {
		if ( empty( UM()->classes['um_photos_admin'] ) ) {
			UM()->classes['um_photos_admin'] = new um_ext\um_user_photos\admin\Admin();
		}
		return UM()->classes['um_photos_admin'];
	}


	/**
	 * @return um_ext\um_user_photos\core\User_Photos_Shortcodes()
	 */
	function shortcodes() {
		if ( empty( UM()->classes['um_photos_shortcodes'] ) ) {
			UM()->classes['um_photos_shortcodes'] = new um_ext\um_user_photos\core\User_Photos_Shortcodes();
		}
		return UM()->classes['um_photos_shortcodes'];
	}


	/**
	 * @return um_ext\um_user_photos\core\User_Photos_Common()
	 */
	function common() {
		if ( empty( UM()->classes['um_photos_common'] ) ) {
			UM()->classes['um_photos_common'] = new um_ext\um_user_photos\core\User_Photos_Common();
		}
		return UM()->classes['um_photos_common'];
	}
	
	
	
	/**
	 * @return bool|um_ext\um_user_photos\core\User_Photos_Activity()
	 */
	function activity() {
		if ( ! class_exists('UM_Activity_API') ) {
			return false;
		}

		if ( empty( UM()->classes['um_photos_activity'] ) ) {
			UM()->classes['um_photos_activity'] = new um_ext\um_user_photos\core\User_Photos_Activity();
		}
		return UM()->classes['um_photos_activity'];
	}


	/**
	 * @return um_ext\um_user_photos\core\User_Photos_Profile()
	 */
	function profile() {
		if ( empty( UM()->classes['um_photos_profile'] ) ) {
			UM()->classes['um_photos_profile'] = new um_ext\um_user_photos\core\User_Photos_Profile();
		}
		return UM()->classes['um_photos_profile'];
	}


	/**
	 * @return um_ext\um_user_photos\core\User_Photos_Account()
	 */
	function account() {
		if ( empty( UM()->classes['um_photos_account'] ) ) {
			UM()->classes['um_photos_account'] = new um_ext\um_user_photos\core\User_Photos_Account();
		}
		return UM()->classes['um_photos_account'];
	}


	/**
	 * @return um_ext\um_user_photos\core\User_Photos_Ajax()
	 */
	function ajax() {
		if ( empty( UM()->classes['um_photos_ajax'] ) ) {
			UM()->classes['um_photos_ajax'] = new um_ext\um_user_photos\core\User_Photos_Ajax();
		}
		return UM()->classes['um_photos_ajax'];
	}


	/**
	 * @return $this
	 */
	function get_this() {
		return $this;
	}
	
	
	function user_liked_comment( $comment_id ) {
		return false;
	}
	
	
	
	function can_edit_comment( $comment_id , $user_id ) {
		$comment = get_comment($comment_id);
		
		if($comment->user_id == $user_id){
			return true;
		}
		
		$image = get_post($comment->comment_post_ID);
		if($image->post_author == $user_id){
			return true;
		}
		
		return false;
	}
	
	function is_comment_author( $comment_id , $user_id ){
		
		$comment = get_comment($comment_id);
		
		if($comment->user_id == $user_id){
			return true;
		}
		
		return false;
		
	}
	
	
	function can_comment(){
		return true;
	}
	
	
	
	
	/**
	 * @param int $from_time
	 * @param int $to_time
	 *
	 * @return string
	 */

	function human_time_diff( $from, $to = '' )
	{
		if (empty( $to )) {
			$to = time();
		}
		$diff = (int)abs( $to - $from );
		if ($diff < 60) {

			$since = __( 'Just now', 'um-activity' );

		} else if ($diff < HOUR_IN_SECONDS) {

			$mins = round( $diff / MINUTE_IN_SECONDS );
			if ($mins <= 1)
				$mins = 1;
			if ($mins == 1) {
				$since = sprintf( __( '%s min', 'um-user-photos' ), $mins );
			} else {
				$since = sprintf( __( '%s mins', 'um-user-photos' ), $mins );
			}

		} else if ($diff < DAY_IN_SECONDS && $diff >= HOUR_IN_SECONDS) {

			$hours = round( $diff / HOUR_IN_SECONDS );
			if ($hours <= 1)
				$hours = 1;
			if ($hours == 1) {
				$since = sprintf( __( '%s hr', 'um-user-photos' ), $hours );
			} else {
				$since = sprintf( __( '%s hrs', 'um-user-photos' ), $hours );
			}

		} else if ($diff < WEEK_IN_SECONDS && $diff >= DAY_IN_SECONDS) {

			$days = round( $diff / DAY_IN_SECONDS );
			if ($days <= 1)
				$days = 1;
			if ($days == 1) {
				$since = sprintf( __( 'Yesterday at %s', 'um-user-photos' ), date_i18n( 'g:ia', $from ) );
			} else {
				$since = sprintf( __( '%s at %s', 'um-user-photos' ), date_i18n( 'F d', $from ), date_i18n( 'g:ia', $from ) );
			}

		} else if ($diff < 30 * DAY_IN_SECONDS && $diff >= WEEK_IN_SECONDS) {

			$since = sprintf( __( '%s at %s', 'um-user-photos' ), date_i18n( 'F d', $from ), date_i18n( 'g:ia', $from ) );

		} else if ($diff < YEAR_IN_SECONDS && $diff >= 30 * DAY_IN_SECONDS) {

			$since = sprintf( __( '%s at %s', 'um-user-photos' ), date_i18n( 'F d', $from ), date_i18n( 'g:ia', $from ) );

		} else if ($diff >= YEAR_IN_SECONDS) {

			$since = sprintf( __( '%s at %s', 'um-user-photos' ), date_i18n( 'F d, Y', $from ), date_i18n( 'g:ia', $from ) );

		}

		return apply_filters( 'um_user_photos_human_time_diff', $since, $diff, $from, $to );
	}
	
}

//create class var
add_action( 'plugins_loaded', 'um_init_photos', -10, 1 );
function um_init_photos() {
	if ( function_exists( 'UM' ) ) {
		UM()->set_class( 'Photos_API', true );
	}
}
