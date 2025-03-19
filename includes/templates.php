<?php 
if ( ! function_exists( 'pdesign_load_template' ) ) {
	function pdesign_load_template( $template = null, $variables = array()) {
		$variables = (array) $variables;
		$variables = apply_filters( 'get_prodesign_load_template_variables', $variables );
		extract( $variables );

		$isLoad = apply_filters( 'should_filter_load_template', true, $template, $variables );
		if ( ! $isLoad ) {
			return;
		}

		$template_file = pdesign_get_template( $template);
		if ( file_exists( $template_file ) ) {
			include pdesign_get_template( $template);
		} else {
			do_action( 'prodesign_after_template_not_found', $template );
		}
		do_action( 'prodesign_load_template_after', $template, $variables );
	}
}

if ( ! function_exists( 'pdesign_get_template' ) ) {
	function pdesign_get_template( $template = null) {
		if ( ! $template ) {
			return false;
		}
		$template = str_replace( '.', DIRECTORY_SEPARATOR, $template );
        

		$template_location = trailingslashit( get_stylesheet_directory() ) . "prodesign/{$template}.php";
		if ( ! file_exists( $template_location ) ) {
			$template_location = trailingslashit( get_template_directory() ) . "prodesign/{$template}.php";
		}
		$file_in_theme = $template_location;
		if ( ! file_exists( $template_location ) ) {
			$template_location = trailingslashit( pdesign()->path ) . "templates/{$template}.php";

			if ( ! file_exists( $template_location ) ) {
				$warning_msg = __( 'The file you are trying to load does not exist in your theme or plugin location.', 'prodesign' );
				$warning_msg = $warning_msg . "<code>$file_in_theme</code>";
				$warning_msg = apply_filters( 'prodesign_not_found_template_warning_msg', $warning_msg );
				echo wp_kses( $warning_msg, array( 'code' => true ) );
				?>
				<?php
			}
		}

		return apply_filters( 'pdesign_get_template_path', $template_location);
	}
}

if ( ! function_exists( 'pdesign_get_template_path' ) ) {
	function pdesign_get_template_path( $template = null) {
		if ( ! $template ) {
			return false;
		}
		$template = str_replace( '.', DIRECTORY_SEPARATOR, $template );

		$template_location = trailingslashit( get_stylesheet_directory() ) . "prodesign/{$template}.php";
		if ( ! file_exists( $template_location ) ) {
			$template_location = trailingslashit( get_template_directory() ) . "prodesign/{$template}.php";
		}
		if ( ! file_exists( $template_location ) ) {
			$template_location = trailingslashit( pdesign()->path ) . "templates/{$template}.php";
		}
		return apply_filters( 'pdesign_get_template_path', $template_location);
	}
}
