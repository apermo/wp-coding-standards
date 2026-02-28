<?php
declare(strict_types=1);

/**
 * Flag SAPI-dependent PHP features.
 *
 * @package Apermo\Sniffs\PHP
 */

namespace Apermo\Sniffs\PHP;

use Apermo\Sniffs\Helpers\FunctionCallDetectorTrait;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\PassedParameters;

/**
 * Flags filter_input() calls with SAPI-dependent input types.
 *
 * - INPUT_REQUEST was never implemented and removed in PHP 8.0.
 * - INPUT_SERVER and INPUT_ENV return null on CGI/FPM due to
 *   PHP bug #49184. Use filter_var($_SERVER[...]) instead.
 *
 * Error codes:
 * - InputRequest: filter_input(INPUT_REQUEST) is not supported
 * - InputServer:  filter_input(INPUT_SERVER) is SAPI-dependent
 * - InputEnv:     filter_input(INPUT_ENV) is SAPI-dependent
 */
class SapiDependentFeaturesSniff implements Sniff {

	use FunctionCallDetectorTrait;

	/**
	 * Returns an array of tokens this sniff listens for.
	 *
	 * @return array<int>
	 */
	public function register() {
		return [ T_STRING ];
	}

	/**
	 * Processes a token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		if ( strtolower( $tokens[ $stackPtr ]['content'] ) !== 'filter_input' ) {
			return;
		}

		if ( ! $this->isFunctionCall( $phpcsFile, $stackPtr ) ) {
			return;
		}

		$params = PassedParameters::getParameters( $phpcsFile, $stackPtr );
		$type   = PassedParameters::getParameterFromStack( $params, 1, 'type' );

		if ( $type === false ) {
			return;
		}

		$typeValue = strtoupper( ltrim( trim( $type['clean'] ), '\\' ) );

		if ( $typeValue === 'INPUT_REQUEST' ) {
			$phpcsFile->addError(
				'filter_input(INPUT_REQUEST) was never implemented and was removed in PHP 8.0; check $_SERVER[\'REQUEST_METHOD\'] and use INPUT_GET/INPUT_POST instead',
				$stackPtr,
				'InputRequest',
			);
			return;
		}

		if ( $typeValue === 'INPUT_SERVER' ) {
			$phpcsFile->addWarning(
				'filter_input(INPUT_SERVER) returns null on CGI/FPM (PHP bug #49184); use filter_var($_SERVER[...]) instead',
				$stackPtr,
				'InputServer',
			);
			return;
		}

		if ( $typeValue === 'INPUT_ENV' ) {
			$phpcsFile->addWarning(
				'filter_input(INPUT_ENV) returns null on CGI/FPM (PHP bug #49184); use filter_var($_ENV[...]) instead',
				$stackPtr,
				'InputEnv',
			);
		}
	}
}
