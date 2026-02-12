<?php
/**
 * Image Dimension Auto-Injection
 *
 * Improves CLS by ensuring all images have explicit dimensions.
 * Source: theme inc/hooks/content-filters.php
 *
 * @package Labomaison_Perf_Core
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add dimensions to images missing width/height attributes
 */
function lm_perf_add_image_dimensions( $content ) {

	$content = preg_replace_callback(
		'/<img\b([^>]*?)\bsrc=["\']([^"\']+)["\']([^>]*)>/i',
		function ( $matches ) {

			$before = $matches[1];
			$src    = $matches[2];
			$after  = $matches[3];

			$attrs = trim( $before . ' ' . $after );

			// If width/height already present, keep as-is (but rebuild tag cleanly).
			if ( preg_match( '/\bwidth\s*=\s*["\']\d+["\']/i', $attrs ) && preg_match( '/\bheight\s*=\s*["\']\d+["\']/i', $attrs ) ) {
				return '<img ' . trim( $attrs ) . ' src="' . esc_url( $src ) . '">';
			}

			// Compute local filesystem path for images hosted on this site.
			$upload_dir = wp_upload_dir();

			$parsed = wp_parse_url( $src );
			$path   = $parsed['path'] ?? '';

			$image_path = '';
			if ( $path ) {
				// Prefer uploads mapping when possible.
				if ( ! empty( $upload_dir['baseurl'] ) && str_starts_with( $src, $upload_dir['baseurl'] ) ) {
					$image_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $src );
				} elseif ( ! empty( $upload_dir['basedir'] ) ) {
					// Fallback: map absolute URL path to ABSPATH.
					$image_path = rtrim( ABSPATH, '/' ) . $path;
				}
			}

			if ( $image_path && file_exists( $image_path ) && @exif_imagetype( $image_path ) ) {
				$image_size = @getimagesize( $image_path );
				if ( $image_size && ! empty( $image_size[0] ) && ! empty( $image_size[1] ) ) {
					$attrs .= ' width="' . esc_attr( $image_size[0] ) . '" height="' . esc_attr( $image_size[1] ) . '"';
				}
			}

			return '<img ' . trim( $attrs ) . ' src="' . esc_url( $src ) . '">';
		},
		$content
	);

	return $content;
}
add_filter( 'the_content', 'lm_perf_add_image_dimensions', 20 );


/**
 * Add dimensions to Affilizz images (affilizz-icon)
 */
function lm_perf_add_dimensions_to_affilizz_images( $content ) {

	$content = preg_replace_callback(
		'/<img\b([^>]*?)\bclass=["\'][^"\']*\baffilizz-icon\b[^"\']*["\']([^>]*?)\bsrc=["\']([^"\']+)["\']([^>]*)>/i',
		function ( $matches ) {

			$before = $matches[1]; // attributes before class
			$mid    = $matches[2]; // attributes between class and src
			$src    = $matches[3];
			$after  = $matches[4]; // attributes after src

			$attrs = trim( $before . ' ' . $mid . ' ' . $after );

			// Ensure class contains affilizz-icon (keep other classes if present).
			if ( preg_match( '/\bclass\s*=\s*["\']([^"\']*)["\']/i', $before . ' ' . $mid . ' ' . $after, $mClass ) ) {
				$classes = preg_split( '/\s+/', trim( $mClass[1] ) );
				if ( ! in_array( 'affilizz-icon', $classes, true ) ) {
					$classes[] = 'affilizz-icon';
				}
				// Remove existing class attr then re-add normalized.
				$attrs = preg_replace( '/\bclass\s*=\s*["\'][^"\']*["\']/i', '', $attrs );
				$attrs = trim( $attrs ) . ' class="' . esc_attr( implode( ' ', array_filter( $classes ) ) ) . '"';
			} else {
				$attrs .= ' class="affilizz-icon"';
			}

			// If width/height already present, keep as-is.
			if ( preg_match( '/\bwidth\s*=\s*["\']\d+["\']/i', $attrs ) && preg_match( '/\bheight\s*=\s*["\']\d+["\']/i', $attrs ) ) {
				return '<img ' . trim( $attrs ) . ' src="' . esc_url( $src ) . '">';
			}

			$upload_dir = wp_upload_dir();

			$parsed = wp_parse_url( $src );
			$path   = $parsed['path'] ?? '';

			$image_path = '';
			if ( $path ) {
				if ( ! empty( $upload_dir['baseurl'] ) && str_starts_with( $src, $upload_dir['baseurl'] ) ) {
					$image_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $src );
				} elseif ( ! empty( $upload_dir['basedir'] ) ) {
					$image_path = rtrim( ABSPATH, '/' ) . $path;
				}
			}

			if ( $image_path && file_exists( $image_path ) && @exif_imagetype( $image_path ) ) {
				$image_size = @getimagesize( $image_path );
				if ( $image_size && ! empty( $image_size[0] ) && ! empty( $image_size[1] ) ) {
					$attrs .= ' width="' . esc_attr( $image_size[0] ) . '" height="' . esc_attr( $image_size[1] ) . '"';
				}
			}

			return '<img ' . trim( $attrs ) . ' src="' . esc_url( $src ) . '">';
		},
		$content
	);

	return $content;
}
add_filter( 'the_content', 'lm_perf_add_dimensions_to_affilizz_images', 25 );
