<?php
// phpcs:disable WordPress,Squiz,PSR1,SlevomatCodingStandard,Apermo,Generic,PSR2,PEAR,Universal
// phpcs:enable WordPress.WP.I18n
// Wrong text domain should be flagged (line 5).
echo esc_html__( 'Hello', 'wrong-domain' );
// Correct text domain should be allowed (line 7).
echo esc_html__( 'Hello', 'test-domain' );
