<?php if ( ! defined( 'ABSPATH' ) ) exit;

if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	//Only for AJAX loading posts
	if ( ! empty( $posts ) ) {
		foreach ( $posts as $post ) {
			UM()->get_template( 'profile/posts-single.php', '', array( 'post' => $post ), true );
		}
	}
} else {
	if ( ! empty( $posts ) ) { ?>
<div class="fusion-title title fusion-title-2 fusion-sep-none fusion-title-center fusion-title-highlight fusion-loop-off fusion-highlight-underline fusion-title-size-three" style="margin-top:30px;margin-right:0px;margin-bottom:40px;margin-left:0px;" data-highlight="underline" data-animationoffset="top-into-view"><h3 class="title-heading-center fusion-responsive-typography-calculated" style="margin:0;--fontSize:37;line-height:1.3;"><span class="fusion-highlighted-text-prefix"></span> <span class="fusion-highlighted-text-wrapper"><span class="fusion-highlighted-text">Artículos Publicados</span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="M8.1,146.2c0,0,240.6-55.6,479-13.8"></path></svg></span> <span class="fusion-highlighted-text-postfix"></span></h3></div>
		<div class="um-ajax-items">

			<?php foreach ( $posts as $post ) {
				UM()->get_template( 'profile/posts-single.php', '', array( 'post' => $post ), true );
			}

			if ( $count_posts > 10 ) { ?>
				<div class="um-load-items">
					<a href="javascript:void(0);" class="um-ajax-paginate um-button" data-hook="um_load_posts"
					   data-author="<?php echo esc_attr( um_get_requested_user() ); ?>" data-page="1"
					   data-pages="<?php echo esc_attr( ceil( $count_posts / 10 ) ); ?>">
						<?php _e( 'load more posts', 'ultimate-member' ); ?>
					</a>
				</div>
			<?php } ?>

		</div>

	<?php } else { ?>
<div class="fusion-title title fusion-title-2 fusion-sep-none fusion-title-center fusion-title-highlight fusion-loop-off fusion-highlight-underline fusion-title-size-three" style="margin-top:30px;margin-right:0px;margin-bottom:40px;margin-left:0px;" data-highlight="underline" data-animationoffset="top-into-view"><h3 class="title-heading-center fusion-responsive-typography-calculated" style="margin:0;--fontSize:37;line-height:1.3;"><span class="fusion-highlighted-text-prefix"></span> <span class="fusion-highlighted-text-wrapper"><span class="fusion-highlighted-text">Artículos Publicados</span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="M8.1,146.2c0,0,240.6-55.6,479-13.8"></path></svg></span> <span class="fusion-highlighted-text-postfix"></span></h3></div>
		<div class="um-profile-note">
			<span>
				<?php if ( um_profile_id() == get_current_user_id() ) {
					_e( 'You have not created any posts.', 'ultimate-member' );
				} else {
					_e( 'This user has not created any posts.', 'ultimate-member' );
				} ?>
			</span>
		</div>

	<?php }
}