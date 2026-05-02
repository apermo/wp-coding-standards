<?php
// phpcs:disable WordPress,PSR1,SlevomatCodingStandard,PSR2,PEAR,Universal
// phpcs:disable Apermo.PHP,Apermo.WhiteSpace,Apermo.Formatting,Apermo.Arrays,Apermo.Hooks
// phpcs:disable Apermo.Functions,Apermo.DataStructures,Apermo.WordPress,Apermo.Commenting

$value = 'value';
?>

<p><?= esc_html( $value ) ?></p>

<p><?= esc_html( $value ); ?></p>
