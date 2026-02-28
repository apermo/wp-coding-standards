<?php
/**
 * Require explicit autoload parameter on option functions.
 *
 * @package Apermo\Sniffs\WordPress
 */

namespace Apermo\Sniffs\WordPress;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\PassedParameters;

/**
 * Flags add_option() and update_option() calls that omit the
 * autoload parameter. Missing autoload defaults to 'yes', which
 * silently loads the option on every page load — a common
 * performance footgun.
 *
 * Warning codes:
 * - MissingAutoload: autoload parameter not provided
 */
class RequireOptionAutoloadSniff implements Sniff {

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
			'%s() called without explicit autoload parameter; pass true/false as argument %d',
			$stackPtr,
			'MissingAutoload',
			[ $tokens[ $stackPtr ]['content'], $config['position'] ],
		);
	}

	/**
	 * Checks whether a T_STRING token is a genuine function call.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the T_STRING token.
	 *
	 * @return bool
	 */
	private function isFunctionCall( File $phpcsFile, int $stackPtr ): bool {
		$tokens = $phpcsFile->getTokens();

		$next = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );
		if ( $next === false || $tokens[ $next ]['code'] !== T_OPEN_PARENTHESIS ) {
			return false;
		}

		$prev = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );
		if ( $prev === false ) {
			return true;
		}

		$prevCode = $tokens[ $prev ]['code'];

		if ( in_array( $prevCode, [ T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_NULLSAFE_OBJECT_OPERATOR ], true ) ) {
			return false;
		}

		if ( $prevCode === T_FUNCTION ) {
			return false;
		}

		return true;
	}
}
