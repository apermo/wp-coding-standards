<?php
declare(strict_types=1);

/**
 * Requires permission_callback in register_rest_route() calls.
 *
 * @package Apermo\Sniffs\WordPress
 */

namespace Apermo\Sniffs\WordPress;

use Apermo\Sniffs\Helpers\FunctionCallDetectorTrait;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\PassedParameters;

/**
 * Flags register_rest_route() calls that do not include a
 * permission_callback in the route arguments array.
 *
 * Missing permission callbacks are the #1 REST API security hole —
 * endpoints default to public access when omitted.
 *
 * Error codes:
 * - MissingArgs: no route arguments parameter provided at all
 * - Missing:     args present but no permission_callback key found
 */
class RequireRestPermissionCallbackSniff implements Sniff {

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
		$tokens   = $phpcsFile->getTokens();
		$funcName = strtolower( $tokens[ $stackPtr ]['content'] );

		if ( $funcName !== 'register_rest_route' ) {
			return;
		}

		if ( ! $this->isFunctionCall( $phpcsFile, $stackPtr ) ) {
			return;
		}

		$params = PassedParameters::getParameters( $phpcsFile, $stackPtr );

		// register_rest_route( $namespace, $route, $args, $override )
		// The args parameter is the 3rd positional argument.
		$argsParam = PassedParameters::getParameterFromStack( $params, 3, 'args' );
		if ( $argsParam === false ) {
			$phpcsFile->addError(
				'register_rest_route() called without route arguments; include a permission_callback',
				$stackPtr,
				'MissingArgs',
			);
			return;
		}

		if ( $this->containsPermissionCallback( $phpcsFile, $argsParam ) ) {
			return;
		}

		$phpcsFile->addError(
			'register_rest_route() args must include a permission_callback',
			$stackPtr,
			'Missing',
		);
	}

	/**
	 * Checks if the args parameter contains a 'permission_callback' key.
	 *
	 * Handles both single route definitions and nested multi-route arrays.
	 * For nested arrays, every sub-array must contain the key.
	 * Non-literal args (variables, function calls) are assumed correct.
	 *
	 * @param File                                    $phpcsFile The file being scanned.
	 * @param array{start: int, end: int, raw: string} $param     Parameter info from PassedParameters.
	 *
	 * @return bool
	 */
	private function containsPermissionCallback( File $phpcsFile, array $param ): bool {
		$tokens = $phpcsFile->getTokens();

		if ( ! $this->isArrayLiteral( $phpcsFile, $param ) ) {
			return true;
		}

		$bounds = $this->getArrayBounds( $phpcsFile, $param );
		if ( $bounds === null ) {
			return false;
		}

		[ $opener, $closer ] = $bounds;

		// Check if nested: first element inside the outer array is itself an array.
		$firstElement = $phpcsFile->findNext( T_WHITESPACE, $opener + 1, $closer, true );
		if ( $firstElement !== false
			&& in_array( $tokens[ $firstElement ]['code'], [ T_OPEN_SHORT_ARRAY, T_ARRAY ], true )
		) {
			return $this->allNestedRoutesHaveCallback( $phpcsFile, $opener, $closer );
		}

		return $this->scanForPermissionCallback( $phpcsFile, $param['start'], $param['end'] );
	}

	/**
	 * Checks that every inner array in a nested route definition has permission_callback.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $opener    The outer array opener token position.
	 * @param int  $closer    The outer array closer token position.
	 *
	 * @return bool True only if every inner array contains the key.
	 */
	private function allNestedRoutesHaveCallback( File $phpcsFile, int $opener, int $closer ): bool {
		$tokens = $phpcsFile->getTokens();
		$pos    = $opener + 1;

		while ( $pos < $closer ) {
			$next = $phpcsFile->findNext( T_WHITESPACE, $pos, $closer, true );
			if ( $next === false ) {
				break;
			}

			if ( $tokens[ $next ]['code'] === T_OPEN_SHORT_ARRAY ) {
				$innerCloser = $tokens[ $next ]['bracket_closer'];
				if ( ! $this->scanForPermissionCallback( $phpcsFile, $next, $innerCloser ) ) {
					return false;
				}

				$pos = $innerCloser + 1;
				continue;
			}

			if ( $tokens[ $next ]['code'] === T_ARRAY ) {
				$openParen   = $phpcsFile->findNext( T_WHITESPACE, $next + 1, null, true );
				$innerCloser = $tokens[ $openParen ]['parenthesis_closer'];
				if ( ! $this->scanForPermissionCallback( $phpcsFile, $next, $innerCloser ) ) {
					return false;
				}

				$pos = $innerCloser + 1;
				continue;
			}

			$pos = $next + 1;
		}

		return true;
	}

	/**
	 * Scans a token range for 'permission_callback' used as an array key.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $start     Start token position.
	 * @param int  $end       End token position (inclusive).
	 *
	 * @return bool
	 */
	private function scanForPermissionCallback( File $phpcsFile, int $start, int $end ): bool {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $start; $i <= $end; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_CONSTANT_ENCAPSED_STRING ) {
				continue;
			}

			$keyContent = $this->stripQuotes( $tokens[ $i ]['content'] );
			if ( $keyContent !== 'permission_callback' ) {
				continue;
			}

			$nextNonWs = $phpcsFile->findNext( T_WHITESPACE, $i + 1, $end + 1, true );
			if ( $nextNonWs !== false && $tokens[ $nextNonWs ]['code'] === T_DOUBLE_ARROW ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Gets the opener and closer positions of the array in a parameter.
	 *
	 * @param File                                    $phpcsFile The file being scanned.
	 * @param array{start: int, end: int, raw: string} $param     Parameter info.
	 *
	 * @return array{int, int}|null [opener, closer] or null if not found.
	 */
	private function getArrayBounds( File $phpcsFile, array $param ): ?array {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $param['start']; $i <= $param['end']; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_OPEN_SHORT_ARRAY ) {
				return [ $i, $tokens[ $i ]['bracket_closer'] ];
			}

			if ( $tokens[ $i ]['code'] === T_ARRAY ) {
				$openParen = $phpcsFile->findNext( T_WHITESPACE, $i + 1, null, true );
				return [ $openParen, $tokens[ $openParen ]['parenthesis_closer'] ];
			}
		}

		return null;
	}

	/**
	 * Checks if the parameter contains an array literal ([ or array().
	 *
	 * @param File                                    $phpcsFile The file being scanned.
	 * @param array{start: int, end: int, raw: string} $param     Parameter info.
	 *
	 * @return bool
	 */
	private function isArrayLiteral( File $phpcsFile, array $param ): bool {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $param['start']; $i <= $param['end']; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				continue;
			}

			return $tokens[ $i ]['code'] === T_OPEN_SHORT_ARRAY
				|| $tokens[ $i ]['code'] === T_ARRAY;
		}

		return false;
	}

	/**
	 * Strips surrounding quotes from a string token.
	 *
	 * @param string $content The token content.
	 *
	 * @return string The unquoted string.
	 */
	private function stripQuotes( string $content ): string {
		if ( strlen( $content ) >= 2
			&& ( $content[0] === '\'' || $content[0] === '"' )
		) {
			return substr( $content, 1, -1 );
		}

		return $content;
	}
}
