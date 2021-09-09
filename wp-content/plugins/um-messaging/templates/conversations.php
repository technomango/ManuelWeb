<?php
/**
 * Template for the UM Private Messages.
 * Used on the "Profile" page, "Messages" tab.
 *
 * Shortcode: [ultimatemember_messages]
 * Caller: method Messaging_Shortcode->ultimatemember_messages()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-messaging/conversations.php
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>

<script type="text/template" id="tmpl-um_messages_convesations">
	<# _.each( data.conversations, function( conversation, key ) { #>
		<a href="{{{conversation.url}}}" class="um-message-conv-item" data-message_to="{{{conversation.user}}}" data-trigger_modal="conversation" data-conversation_id="{{{conversation.conversation_id}}}">

			<span class="um-message-conv-name">{{{conversation.user_name}}}</span>

			<span class="um-message-conv-pic">{{{conversation.avatar}}}</span>

			<# if ( conversation.new_conv ) { #>
				<span class="um-message-conv-new"><i class="um-faicon-circle"></i></span>
			<# } #>

			<?php do_action( 'um_messaging_conversation_list_name_js' ); ?>
		</a>
	<# }); #>
</script>

<?php if ( ! empty( $conversations ) ) { ?>
<div class="fusion-title title fusion-title-2 fusion-sep-none fusion-title-center fusion-title-highlight fusion-loop-off fusion-highlight-underline fusion-title-size-three" style="margin-top:30px;margin-right:0px;margin-bottom:40px;margin-left:0px;" data-highlight="underline" data-animationoffset="top-into-view"><h3 class="title-heading-center fusion-responsive-typography-calculated" style="margin:0;--fontSize:37;line-height:1.3;"><span class="fusion-highlighted-text-prefix"></span> <span class="fusion-highlighted-text-wrapper"><span class="fusion-highlighted-text">Centro de Mensajes</span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="M8.1,146.2c0,0,240.6-55.6,479-13.8"></path></svg></span> <span class="fusion-highlighted-text-postfix"></span></h3></div>
	<div class="um um-viewing">
		<div class="um-message-conv" data-user="<?php echo esc_attr( um_profile_id() ); ?>">

			<?php $i = 0;
			$profile_can_read = um_user( 'can_read_pm' );
			foreach ( $conversations as $conversation ) {

				if ( $conversation->user_a == um_profile_id() ) {
					$user = $conversation->user_b;
				} else {
					$user = $conversation->user_a;
				}

				$i++;

				um_fetch_user( $user );

				$user_name = ( um_user( 'display_name' ) ) ? um_user( 'display_name' ) : __( 'Deleted User', 'um-messaging' );

				$is_unread = UM()->Messaging_API()->api()->unread_conversation( $conversation->conversation_id, um_profile_id() ); ?>

				<a href="<?php echo esc_url( add_query_arg( 'conversation_id', $conversation->conversation_id ) ); ?>" class="um-message-conv-item" data-message_to="<?php echo esc_attr( $user ); ?>" data-trigger_modal="conversation" data-conversation_id="<?php echo esc_attr( $conversation->conversation_id ); ?>">

					<span class="um-message-conv-name"><?php echo esc_html( $user_name ); ?></span>

					<span class="um-message-conv-pic"><?php echo get_avatar( $user, 40 ); ?></span>

					<?php if ( $is_unread && $profile_can_read ) { ?>
						<span class="um-message-conv-new"><i class="um-faicon-circle"></i></span>
					<?php }

					do_action( 'um_messaging_conversation_list_name' ); ?>

				</a>

			<?php } ?>
			<div data-user="<?php echo um_profile_id(); ?>" class="um-message-conv-load-more"></div>
			
		</div>

		<div class="um-message-conv-view"></div>
		<div class="um-clear"></div>
	</div>

	<?php do_action( 'um_messaging_after_conversations_list' );

} else { ?>
<div class="fusion-title title fusion-title-2 fusion-sep-none fusion-title-center fusion-title-highlight fusion-loop-off fusion-highlight-underline fusion-title-size-three" style="margin-top:30px;margin-right:0px;margin-bottom:40px;margin-left:0px;" data-highlight="underline" data-animationoffset="top-into-view"><h3 class="title-heading-center fusion-responsive-typography-calculated" style="margin:0;--fontSize:37;line-height:1.3;"><span class="fusion-highlighted-text-prefix"></span> <span class="fusion-highlighted-text-wrapper"><span class="fusion-highlighted-text">Centro de Mensajes</span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="M8.1,146.2c0,0,240.6-55.6,479-13.8"></path></svg></span> <span class="fusion-highlighted-text-postfix"></span></h3></div>
	<div class="um-message-noconv">
		<i class="um-icon-android-chat"></i>
		<?php _e( 'No se encontraron chats aquí', 'um-messaging' ); ?>
	</div>

<?php } ?>
