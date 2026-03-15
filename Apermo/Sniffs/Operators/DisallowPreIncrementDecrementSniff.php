<?php
declare(strict_types=1);

/**
 * Disallow pre-increment/pre-decrement in favor of post-increment/post-decrement.
 *
 * @package Apermo\Sniffs\Operators
 */

namespace Apermo\Sniffs\Operators;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Flags ++$var and --$var. Auto-fixable to $var++ and $var--.
 */
class DisallowPreIncrementDecrementSniff implements Sniff {

	/**
	 * Returns an array of tokens this sniff listens for.
	 *
	 * @return array<int>
	 */
	public function register() {
		return [ T_INC, T_DEC ];
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

		$next = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );
		if ( $next === false ) {
			return;
		}

		$next_code = $tokens[ $next ]['code'];

		// Pre-increment/decrement: operator followed by a variable or self/static/parent.
		if ( $next_code !== T_VARIABLE
			&& $next_code !== T_SELF
			&& $next_code !== T_STATIC
			&& $next_code !== T_PARENT
		) {
			return;
		}

		$operator  = $tokens[ $stackPtr ]['content'];
		$is_inc    = $operator === '++';
		$error_code = $is_inc ? 'PreIncrementFound' : 'PreDecrementFound';
		$message   = $is_inc
			? 'Use post-increment ($var++) instead of pre-increment (++$var)'
			: 'Use post-decrement ($var--) instead of pre-decrement (--$var)';

		$fix = $phpcsFile->addFixableError( $message, $stackPtr, $error_code );
		if ( ! $fix ) {
			return;
		}

		// Find the end of the identifier (handles $var, $obj->prop, self::$var, $arr['key']).
		$end = $this->findIdentifierEnd( $phpcsFile, $next );

		$phpcsFile->fixer->beginChangeset();

		// Remove the operator token and any whitespace between it and the identifier.
		$phpcsFile->fixer->replaceToken( $stackPtr, '' );
		for ( $i = $stackPtr + 1; $i < $next; $i++ ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
		}

		// Append operator after the identifier.
		$phpcsFile->fixer->addContent( $end, $operator );

		$phpcsFile->fixer->endChangeset();
	}

	/**
	 * Find the last token of a complex identifier.
	 *
	 * Walks through object operators (->), double colons (::),
	 * property/method names, and array brackets.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The start of the identifier.
	 *
	 * @return int The position of the last token in the identifier.
	 */
	private function findIdentifierEnd( File $phpcsFile, int $stackPtr ): int {
		$tokens = $phpcsFile->getTokens();
		$end    = $stackPtr;

		while ( true ) {
			$next = $phpcsFile->findNext( T_WHITESPACE, $end + 1, null, true );
			if ( $next === false ) {
				break;
			}

			$code = $tokens[ $next ]['code'];

			// Follow -> or :: chains.
			if ( $code === T_OBJECT_OPERATOR || $code === T_DOUBLE_COLON ) {
				$member = $phpcsFile->findNext( T_WHITESPACE, $next + 1, null, true );
				if ( $member === false ) {
					$end = $next;
					break;
				}
				$end = $member;
				continue;
			}

			// Follow array access [].
			if ( $code === T_OPEN_SQUARE_BRACKET && isset( $tokens[ $next ]['bracket_closer'] ) ) {
				$end = $tokens[ $next ]['bracket_closer'];
				continue;
			}

			break;
		}

		return $end;
	}
}
