<?php
declare(strict_types=1);

/**
 * Flag add_action() calls registering wp_ajax_ hooks.
 *
 * @package Apermo\Sniffs\WordPress
 */

namespace Apermo\Sniffs\WordPress;

use Apermo\Sniffs\Helpers\FunctionCallDetectorTrait;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\PassedParameters;

/**
 * Flags add_action() calls that register wp_ajax_* hooks.
 * Admin-ajax is slower and harder to debug than the REST API.
 * Use register_rest_route() instead.
 *
 * Error codes:
 * - Found: wp_ajax_ hook registration detected
 */
class NoAdminAjaxSniff implements Sniff {

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

		if ( strtolower( $tokens[ $stackPtr ]['content'] ) !== 'add_action' ) {
			return;
		}

		if ( ! $this->isFunctionCall( $phpcsFile, $stackPtr ) ) {
			return;
		}

		$params = PassedParameters::getParameters( $phpcsFile, $stackPtr );
		$hook   = PassedParameters::getParameterFromStack( $params, 1, 'hook_name' );

		if ( $hook === false ) {
			return;
		}

		$hookValue = $this->extractHookName( $phpcsFile, $hook );
		if ( $hookValue === null ) {
			return;
		}

		if ( strpos( $hookValue, 'wp_ajax_' ) !== 0 ) {
			return;
		}

		$phpcsFile->addError(
			'Admin-ajax hooks (wp_ajax_*) are discouraged; use register_rest_route() instead',
			$stackPtr,
			'Found',
		);
	}

	/**
	 * Extract the hook name string from the parameter tokens.
	 *
	 * Handles single-quoted and double-quoted strings. Returns null for
	 * variable or dynamic hook names that cannot be statically analyzed.
	 *
	 * @param File               $phpcsFile The file being scanned.
	 * @param array<string, int> $param     Parameter info from PassedParameters.
	 *
	 * @return string|null The hook name or null if not determinable.
	 */
	private function extractHookName( File $phpcsFile, array $param ): ?string {
		$tokens = $phpcsFile->getTokens();

		$first = $phpcsFile->findNext(
			T_WHITESPACE,
			$param['start'],
			$param['end'] + 1,
			true,
		);

		if ( $first === false ) {
			return null;
		}

		$code = $tokens[ $first ]['code'];

		// Single or double quoted string literal (not part of concatenation).
		if ( $code === T_CONSTANT_ENCAPSED_STRING ) {
			$next = $phpcsFile->findNext( T_WHITESPACE, $first + 1, $param['end'] + 1, true );
			if ( $next !== false ) {
				return null;
			}

			return $this->stripQuotes( $tokens[ $first ]['content'] );
		}

		return null;
	}

	/**
	 * Strip surrounding quotes from a string token.
	 *
	 * @param string $value The quoted string.
	 *
	 * @return string The unquoted string.
	 */
	private function stripQuotes( string $value ): string {
		if ( strlen( $value ) >= 2 ) {
			$first = $value[0];
			if ( ( $first === '\'' || $first === '"' ) && str_ends_with( $value, $first ) ) {
				return substr( $value, 1, -1 );
			}
		}

		return $value;
	}
}
