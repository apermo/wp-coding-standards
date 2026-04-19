<?php
declare(strict_types=1);

/**
 * Requires explicit autoload parameter on option functions.
 *
 * @package Apermo\Sniffs\WordPress
 */

namespace Apermo\Sniffs\WordPress;

use Apermo\Sniffs\Helpers\FunctionCallDetectorTrait;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\PassedParameters;

/**
 * Flags add_option() and update_option() calls that omit the
 * autoload parameter. Without an explicit value, WordPress
 * determines autoloading via heuristics (WP 6.6+) or defaults
 * to 'yes' (older versions) — either way, an explicit parameter
 * makes intent clear and avoids surprises.
 *
 * Warning codes:
 * - MissingAutoload: autoload parameter not provided
 */
class RequireOptionAutoloadSniff implements Sniff {

	use FunctionCallDetectorTrait;

	/**
	 * Functions to check and their autoload parameter position/name.
	 *
	 * @var array<string, array{position: int, name: string}>
	 */
	private const OPTION_FUNCTIONS = [
		'add_option'    => [
			'position' => 4,
			'name'     => 'autoload',
		],
		'update_option' => [
			'position' => 3,
			'name'     => 'autoload',
		],
	];

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
		$tokens   = $phpcsFile->getTokens();
		$funcName = strtolower( $tokens[ $stackPtr ]['content'] );

		if ( ! isset( self::OPTION_FUNCTIONS[ $funcName ] ) ) {
			return;
		}

		if ( ! $this->isFunctionCall( $phpcsFile, $stackPtr ) ) {
			return;
		}

		$config = self::OPTION_FUNCTIONS[ $funcName ];
		$params = PassedParameters::getParameters( $phpcsFile, $stackPtr );

		$autoloadParam = PassedParameters::getParameterFromStack(
			$params,
			$config['position'],
			$config['name'],
		);

		if ( $autoloadParam !== false ) {
			return;
		}

		$phpcsFile->addWarning(
			'%s() called without explicit autoload parameter; pass true/false as the \'autoload\' argument',
			$stackPtr,
			'MissingAutoload',
			[ $tokens[ $stackPtr ]['content'] ],
		);
	}
}
