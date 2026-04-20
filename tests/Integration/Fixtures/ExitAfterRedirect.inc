<?php
// phpcs:disable WordPress,Squiz,PSR1,SlevomatCodingStandard,Generic,PSR2,PEAR,Universal
// phpcs:disable Apermo.PHP,Apermo.WhiteSpace,Apermo.Formatting,Apermo.Arrays,Apermo.Hooks
// phpcs:disable Apermo.Functions,Apermo.DataStructures,Apermo.WordPress,Apermo.Commenting

// Missing exit after wp_safe_redirect (line 7).
wp_safe_redirect( 'https://example.tld/' );

// Correct: exit() immediately follows (line 10).
wp_safe_redirect( 'https://example.tld/' );
exit();

// Missing exit after wp_redirect (line 14).
wp_redirect( 'https://example.tld/' );

// Redirect inside conditional without exit (line 17).
if ( isset( $_GET['go'] ) ) {
	wp_safe_redirect( 'https://example.tld/' );
}

// Redirect inside conditional with exit — allowed (line 22).
if ( isset( $_GET['go'] ) ) {
	wp_safe_redirect( 'https://example.tld/' );
	exit();
}
