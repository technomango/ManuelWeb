<?php
/**
 * Template for the UM User Photos, Pagination block
 *
 * Parent template: albums.php
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/pagination.php
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="um-pagi">
	<span class="pagi pagi-arrow <?php echo $page === 1 ? 'disabled' : ''; ?>" data-page="1"><i class="um-faicon-angle-double-left"></i></span>
	<span class="pagi pagi-arrow <?php echo $page === 1 ? 'disabled' : ''; ?>" data-page="<?php echo esc_attr( max( array( $page - 1, 1 ) ) ); ?>"><i class="um-faicon-angle-left"></i></span>
	<span class="pagi current" data-page="<?php echo esc_attr( $page ); ?>"><?php echo esc_html( $page ); ?></span>
	<span class="pagi pagi-arrow <?php echo $page === $pages ? 'disabled' : ''; ?>" data-page="<?php echo esc_attr( min( array( $page + 1, $pages ) ) ); ?>"><i class="um-faicon-angle-right"></i></span>
	<span class="pagi pagi-arrow <?php echo $page === $pages ? 'disabled' : ''; ?>" data-page="<?php echo esc_attr( $pages ); ?>"><i class="um-faicon-angle-double-right"></i></span>
</div>