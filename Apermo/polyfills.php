<?php
/**
 * Polyfills for functions unavailable before PHP 8.0.
 *
 * @package Apermo
 */

if ( ! function_exists( 'str_ends_with' ) ) {
	/**
	 * Polyfill for str_ends_with() (PHP 8.0+).
	 *
	 * @param string $haystack The string to search in.
	 * @param string $needle   The substring to search for.
	 *
	 * @return bool
	 */
	function str_ends_with( string $haystack, string $needle ): bool {
		if ( $needle === '' ) {
			return true;
		}
		return substr( $haystack, -strlen( $needle ) ) === $needle;
	}
}
