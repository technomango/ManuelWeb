<?php

		// Exit if accessed directly
		if ( ! defined( 'ABSPATH' ) ) {
			exit;
		}

		function fusion_builder_add_architecture_demo( $demos ) {

		$demos['architecture'] = array (
);

			return $demos;
		}
		add_filter( 'fusion_builder_get_demo_pages', 'fusion_builder_add_architecture_demo' );