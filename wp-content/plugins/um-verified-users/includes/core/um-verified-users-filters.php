<?php
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Add bulk actions
 *
 * @param $actions
 *
 * @return mixed
 */
function um_verified_extend_bulk_actions( $actions ){
	$actions['um_verify_accounts'] = array( 'label' => __( 'Mark accounts as verified', 'um-verified' ) );
	$actions['um_unverify_accounts'] = array( 'label' => __( 'Mark accounts as unverified', 'um-verified' ) );
	return $actions;
}
add_filter('um_admin_bulk_user_actions_hook', 'um_verified_extend_bulk_actions', 100 );

/**
 * Extend settings
 *
 * @param $settings
 *
 * @return mixed
 */
function um_verified_users_settings( $settings ) {
	$settings['licenses']['fields'][] = array(
		'id'        => 'um_verified_license_key',
		'label'     => __( 'Verified Users License Key', 'um-verified' ),
		'item_name' => 'Verified Users',
		'author'    => 'Ultimate Member',
		'version'   => um_verified_users_version,
	);


	$settings_fields = array(
		array(
			'id'        => 'verified_redirect',
			'type'      => 'text',
			'label'     => __( 'Content Lock Redirect', 'um-verified' ),
			'tooltip'   => __( 'Unverified users who access verified areas will be redirected to that URL.', 'um-verified' ),
			'size'      => 'medium',
		)
	);
	$settings_fields = apply_filters( 'um_verified_users_settings_fields', $settings_fields );

	$key = ! empty( $settings['extensions']['sections'] ) ? 'verified' : '';
	$settings['extensions']['sections'][ $key ] = array(
		'title'     => __( 'Verified Users', 'um-verified' ),
		'fields'    => $settings_fields,
	);

	return $settings;
}
add_filter( 'um_settings_structure', 'um_verified_users_settings', 10, 1 );


/**
 * Quick actions in users list
 *
 * @param $actions
 * @param $user_id
 *
 * @return mixed
 */
function um_verified_user_row_actions( $actions, $user_id ) {
	$status = UM()->Verified_Users_API()->api()->verified_status( $user_id );
	$verify_url = UM()->Verified_Users_API()->api()->verify_user_url( $user_id );
	$unverify_url = UM()->Verified_Users_API()->api()->unverify_user_url( $user_id );

	if ( $status == 'unverified' ) {
		$actions['verify'] = "<a class='' href='" . $verify_url . "'>" . __( 'Verify','um-verified') . "</a>";
	} elseif ( $status == 'pending' ) {
		$actions['verify'] = "<a class='' href='" . $verify_url . "'>" . __( 'Approve verification request','um-verified') . "</a>";
		$actions['unverify'] = "<a class='' href='" . $unverify_url . "'>" . __( 'Reject verification','um-verified') . "</a>";
	} elseif ( $status == 'verified' ) {
		$actions['unverify'] = "<a class='' href='" . $unverify_url . "'>" . __( 'Unverify','um-verified') . "</a>";
	}
	
	return $actions;
}
add_filter( 'um_admin_user_row_actions', 'um_verified_user_row_actions', 10, 2 );


/**
 * Add badge to display name
 *
 * @param $name
 * @param $user_id
 * @param $html
 *
 * @return string
 */
function um_verified_add_badge( $name, $user_id, $html ) {
	if ( ! $html )
		return $name;

	if ( UM()->Verified_Users_API()->api()->is_verified( $user_id ) ) {
		$name = $name . UM()->Verified_Users_API()->api()->verified_badge();
	}

	return $name;
}
add_filter( 'um_user_display_name_filter', 'um_verified_add_badge', 50, 3 );


/**
 * New tag for activity
 *
 * @param $args
 *
 * @return array
 */
function um_verified_search_tpl( $args ) {
	$args[] = '{verified}';
	return $args;
}
add_filter( 'um_activity_search_tpl', 'um_verified_search_tpl' );


/**
 * New tag replace for activity
 *
 * @param $args
 * @param $array
 *
 * @return array
 */
function um_verified_replace_tpl( $args, $array ) {
	$args[] = isset( $array['verified'] ) ? $array['verified'] : '';
	return $args;
}
add_filter( 'um_activity_replace_tpl', 'um_verified_replace_tpl', 10, 2 );


/**
 * Add new activity action
 *
 * @param $actions
 *
 * @return mixed
 */
function um_verified_activity_action( $actions ) {
	$actions['verified-account'] = __( 'Account Verifications', 'um-verified' );
	return $actions;
}
add_filter( 'um_activity_global_actions', 'um_verified_activity_action' );


/**
 * Modify pending users queue
 *
 * @param $args
 *
 * @return mixed
 */
function um_verified_admin_queue_extend( $args ) {
	$args['meta_query'][] = array(
		'key'       => '_um_verified',
		'value'     => 'pending',
		'compare'   => '=',
	);
	return $args;
}
add_filter( 'um_admin_pending_queue_filter', 'um_verified_admin_queue_extend' );


/**
 * @param $views
 *
 * @return mixed
 */
function um_verified_admin_views_users( $views ) {

	if ( isset( $_REQUEST['um_status'] ) && sanitize_key( $_REQUEST['um_status'] ) == 'needs-verification' ) {
		$current = 'class="current"';
	} else {
		$current = '';
	}

	$views['needs-verification'] = '<a href="' . add_query_arg( [ 'um_status' => 'needs-verification' ], admin_url( 'users.php' ) ) . '" ' . $current . '>'. __( 'Request Verification', 'um-verified' ) . ' <span class="count">(' . UM()->Verified_Users_API()->api()->verified_requests_count() . ')</span></a>';
	return $views;
}
add_filter( 'um_admin_views_users', 'um_verified_admin_views_users' );


/**
 * Adds a notification type
 *
 * @param array $logs
 *
 * @return array
 */
function um_verified_add_notification_type( $logs ) {
	$logs['account_verified'] = array(
		'title'         => __( 'User account is verified', 'um-verified' ),
		'account_desc'  => __( 'When my account gets verified', 'um-verified' ),
	);
	return $logs;
}
add_filter( 'um_notifications_core_log_types', 'um_verified_add_notification_type', 200 );


/**
 * Adds a notification icon
 *
 * @param $output
 * @param $type
 *
 * @return string
 */
function um_verified_add_notification_icon( $output, $type ) {
	if ( $type == 'account_verified' ) {
		$output = '<i class="um-icon-ios-checkmark" style="color: #5EA5E7"></i>';
	}

	return $output;
}
add_filter( 'um_notifications_get_icon', 'um_verified_add_notification_icon', 10, 2 );


/**
 * Adds a notification icon
 *
 * @param $fields
 * @param $role
 *
 * @return array
 */
function um_verified_profile_completeness_roles_metabox_fields( $fields, $role ) {

	$fields[] = array(
		'id'		    => '_um_profilec_verify',
		'type'		    => 'select',
		'label'		    => __( 'Verify user account', 'um-verified' ),
		'tooltip'	=> __( 'Verify the user\'s account when he/she completes his/her profile.', 'um-verified' ),
		'value'		    => ! empty( $role['_um_profilec_verify'] ) ? $role['_um_profilec_verify'] : 0,
		'conditional'	=> array( '_um_profilec', '=', '1' ),
		'options'		=> array(
			0	=> __( 'No', 'um-verified' ),
			1	=> __( 'Yes', 'um-verified' ),
		),
	);

	return $fields;
}
add_filter( 'um_profile_completeness_roles_metabox_fields', 'um_verified_profile_completeness_roles_metabox_fields', 10, 2 );


/**
 * Verify/unverify from backend profile
 *
 * @param string $content
 * @param $user
 *
 * @return string
 */
function um_verification_field( $content, $user ) {
	if ( empty( $user ) )
		return $content;

	if( ! isset( $user->ID ) )
		return $content;

	global $pagenow;
	if ( 'profile.php' == $pagenow ) {
		return $content;
	}

	if ( current_user_can( 'edit_users' ) && current_user_can( 'edit_user', $user->ID ) ) {
		$user = get_userdata( $user->ID );
		$is_verified = UM()->Verified_Users_API()->api()->is_verified( $user->ID );
		ob_start(); ?>

		<table class="form-table">
			<tbody>
			<tr>
				<th>
					<label for="um_set_verification"><?php _e( 'Account Verification', 'um-verified' ); ?></label>
				</th>
				<td>
					<select name="um_set_verification" id="um_set_verification">
						<option value='0' <?php selected( 0, $is_verified ); ?>><?php _e('Unverified Account','um-verified'); ?></option>
						<option value='1'  <?php selected( 1, $is_verified ); ?>><?php _e('Verified Account','um-verified'); ?></option>
					</select>
				</td>
			</tr>
			</tbody>
		</table>
	<?php }

	$content .= ob_get_clean();
	return $content;
}
add_filter( 'um_user_profile_additional_fields', 'um_verification_field', 2, 2 );


/**
 * Settings in access widget
 *
 * @param array $fields
 * @param array $data
 * @param object post_type_object
 *
 * @return array
 */
function um_admin_access_settings_fields( $fields, $data, $post_type_object ) {
	$fields[] = array(
		'id' 			=> '_um_locked_to_verified',
		'type'		    => 'checkbox',
		'label'    		=> __( 'Lock content to verified accounts only?', 'um-verified' ),
		'value' 		=> ! empty( $data['_um_locked_to_verified'] ) ? $data['_um_locked_to_verified'] : 0,
		'conditional'	=> array( '_um_accessible', '=', '2' )
	);

	return $fields;
}
add_filter( 'um_admin_access_settings_fields', 'um_admin_access_settings_fields', 10, 3 );


/**
 * Settings in access widget
 *
 * @param array $fields
 * @param $data
 * @param string $screen
 * @return array
 */
function um_admin_category_access_settings_fields( $fields, $data, $screen ) {

	if ( 'edit' == $screen ) {
		$fields[] = array(
			'id'		    => '_um_locked_to_verified',
			'type'		    => 'checkbox',
			'class'		    => 'form-field',
			'name'		    => '_um_locked_to_verified',
			'label'    		=> __( 'Lock content to verified accounts only?', 'um-verified' ),
			'value' 		=> ! empty( $data['_um_locked_to_verified'] ) ? $data['_um_locked_to_verified'] : 0,
			'conditional'	=> array( '_um_accessible', '=', '2' )
		);
	} elseif ( 'create' == $screen ) {
		$fields[] = array(
			'id'		    => '_um_locked_to_verified',
			'type'		    => 'checkbox',
			'name'		    => '_um_locked_to_verified',
			'label'    		=> __( 'Lock content to verified accounts only?', 'um-verified' ),
			'value' 		=> ! empty( $data['_um_locked_to_verified'] ) ? $data['_um_locked_to_verified'] : 0,
			'conditional'	=> array( '_um_accessible', '=', '2' )
		);
	}

	return $fields;
}
add_filter( 'um_admin_category_access_settings_fields', 'um_admin_category_access_settings_fields', 10, 3 );


/**
 * Settings in access widget
 *
 * @param bool $has_access
 * @param array $restriction
 * @return bool
 */
function um_verified_users_restriction( $has_access, $restriction ) {
	if  ( ! empty( $restriction['_um_locked_to_verified'] ) && ! UM()->Verified_Users_API()->api()->is_verified( get_current_user_id() ) ) {
		return false;
	}

	return $has_access;
}
add_filter( 'um_custom_restriction', 'um_verified_users_restriction', 10, 2 );


/**
 * Lock Gutenberg block to verified accounts only
 *
 * @param bool $display
 * @param array $block
 *
 * @return bool
 */
function um_verified_users_loggedin_block_restriction( $display, $block ) {
	if ( ! empty( $block['attrs']['um_locked_to_verified'] ) ) {
		$is_verified = UM()->Verified_Users_API()->api()->is_verified( get_current_user_id() );
		if ( ! $is_verified ) {
			$display = false;
		}
	}

	return $display;
}
add_filter( 'um_loggedin_block_restriction', 'um_verified_users_loggedin_block_restriction', 10, 2 );


/**
 * Creates options in role page
 *
 * @param $roles_metaboxes
 *
 * @return array
 */
function um_verified_add_role_metabox( $roles_metaboxes ) {

	$roles_metaboxes[] = array(
		'id'       => "um-admin-form-verified{" . um_verified_users_path . "}",
		'title'    => __( 'Verified Accounts', 'um-verified' ),
		'callback' => array( UM()->metabox(), 'load_metabox_role' ),
		'screen'   => 'um_role_meta',
		'context'  => 'side',
		'priority' => 'default'
	);

	return $roles_metaboxes;
}
add_filter( 'um_admin_role_metaboxes', 'um_verified_add_role_metabox', 10, 1 );


/**
 * Filter "Users" table, column "Status"
 *
 * @hooked	'manage_users_custom_column'
 * @since		2.0.5
 *
 * @param		string		$val
 * @param		string		$column_name
 * @param		int				$user_id
 * @return	string
 */
function um_verified_manage_users_custom_column( $val, $column_name, $user_id ) {
	if ( $column_name == 'account_status' ) {
		$account_verified_status = get_user_meta( $user_id, '_um_verified', true );
		if ( $account_verified_status ) {
			if ( $val ) {
				$val .= ',<br>';
			}
			$val .= ucfirst( $account_verified_status );
		}
	}
	return $val;
}
add_filter( 'manage_users_custom_column', 'um_verified_manage_users_custom_column', 20, 3 );