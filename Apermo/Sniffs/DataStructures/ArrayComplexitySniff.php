<?php
/**
 * Discourage overly complex array structures.
 *
 * @package Apermo\Sniffs\DataStructures
 */

namespace Apermo\Sniffs\DataStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Flags deeply nested or wide associative arrays that would benefit
 * from being typed objects (DTOs, value objects).
 *
 * Two independent checks fire per array:
 * - Nesting depth: counts levels of associative array nesting.
 * - Key count: counts top-level associative keys.
 *
 * Only outermost arrays are checked (inner arrays are skipped via
 * the return value from process()).
 */
class ArrayComplexitySniff implements Sniff {

	/**
	 * Associative nesting depth that triggers a warning.
	 *
	 * @var int
	 */
	public int $warnDepth = 2;

	/**
	 * Associative nesting depth that triggers an error.
	 *
	 * @var int
	 */
	public int $errorDepth = 3;

	/**
	 * Number of top-level keys that triggers a warning.
	 *
	 * @var int
	 */
	public int $warnKeys = 5;

	/**
	 * Number of top-level keys that triggers an error.
	 *
	 * @var int
	 */
	public int $errorKeys = 10;

	/**
	 * Returns an array of tokens this sniff listens for.
	 *
	 * @return array<int>
	 */
	public function register() {
		return [ T_OPEN_SHORT_ARRAY, T_ARRAY ];
	}

	/**
	 * Processes a token.
	 *
	 * Returns the position after the array closer so that PHPCS skips
	 * all nested arrays — only outermost arrays are analyzed.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return int Next position to process.
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		if ( $tokens[ $stackPtr ]['code'] === T_OPEN_SHORT_ARRAY ) {
			$opener = $stackPtr;
			$closer = $tokens[ $stackPtr ]['bracket_closer'] ?? null;
		} else {
			// T_ARRAY — long syntax array().
			$opener = $tokens[ $stackPtr ]['parenthesis_opener'] ?? null;
			$closer = $tokens[ $stackPtr ]['parenthesis_closer'] ?? null;
		}

		if ( $opener === null || $closer === null ) {
			return $stackPtr + 1;
		}

		$this->analyze( $phpcsFile, $stackPtr, $tokens, $opener, $closer );

		return $closer + 1;
	}

	/**
	 * Analyze an array for associative nesting depth and key count.
	 *
	 * Walks tokens between opener and closer in a single pass, tracking
	 * depth and whether each level is associative (contains T_DOUBLE_ARROW).
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param int   $stackPtr  The position of the array token (for reporting).
	 * @param array $tokens    Token stack.
	 * @param int   $opener    Position of the array opener.
	 * @param int   $closer    Position of the array closer.
	 */
	private function analyze( File $phpcsFile, int $stackPtr, array $tokens, int $opener, int $closer ): void {
		$depth         = 1;
		$depthIsAssoc  = [ 1 => false ];
		$assocCount    = 0;
		$maxAssocCount = 0;
		$topLevelKeys  = 0;

		for ( $i = $opener + 1; $i < $closer; $i++ ) {
			$code = $tokens[ $i ]['code'];

			// Enter nested short array.
			if ( $code === T_OPEN_SHORT_ARRAY ) {
				$depth++;
				$depthIsAssoc[ $depth ] = false;
				continue;
			}

			// Enter nested long array.
			if ( $code === T_ARRAY && isset( $tokens[ $i ]['parenthesis_opener'] ) ) {
				$depth++;
				$depthIsAssoc[ $depth ] = false;
				$i = $tokens[ $i ]['parenthesis_opener'];
				continue;
			}

			// Leave nested short array.
			if ( $code === T_CLOSE_SHORT_ARRAY ) {
				if ( $depthIsAssoc[ $depth ] ) {
					$assocCount--;
				}

				unset( $depthIsAssoc[ $depth ] );
				$depth--;
				continue;
			}

			// Leave nested long array (only for array() parentheses).
			if ( $code === T_CLOSE_PARENTHESIS ) {
				$openParen = $tokens[ $i ]['parenthesis_opener'] ?? null;
				if ( $openParen !== null ) {
					$owner = $tokens[ $openParen ]['parenthesis_owner'] ?? null;
					if ( $owner !== null && $tokens[ $owner ]['code'] === T_ARRAY ) {
						if ( $depthIsAssoc[ $depth ] ) {
							$assocCount--;
						}

						unset( $depthIsAssoc[ $depth ] );
						$depth--;
					}
				}

				continue;
			}

			// Track associative keys.
			if ( $code === T_DOUBLE_ARROW ) {
				if ( $depth === 1 ) {
					$topLevelKeys++;
				}

				if ( ! $depthIsAssoc[ $depth ] ) {
					$depthIsAssoc[ $depth ] = true;
					$assocCount++;
					$maxAssocCount = max( $maxAssocCount, $assocCount );
				}
			}
		}

		$this->checkDepth( $phpcsFile, $stackPtr, $maxAssocCount );
		$this->checkKeys( $phpcsFile, $stackPtr, $topLevelKeys );
	}

	/**
	 * Report on associative nesting depth.
	 *
	 * @param File $phpcsFile     The file being scanned.
	 * @param int  $stackPtr      The position to report at.
	 * @param int  $maxAssocCount Maximum associative nesting depth found.
	 */
	private function checkDepth( File $phpcsFile, int $stackPtr, int $maxAssocCount ): void {
		if ( $maxAssocCount > $this->errorDepth ) {
			$phpcsFile->addError(
				'Associative array nested %s levels deep; consider using typed objects (max: %s)',
				$stackPtr,
				'TooDeepError',
				[ $maxAssocCount, $this->errorDepth ]
			);
		} elseif ( $maxAssocCount > $this->warnDepth ) {
			$phpcsFile->addWarning(
				'Associative array nested %s levels deep; consider using typed objects (max: %s)',
				$stackPtr,
				'TooDeep',
				[ $maxAssocCount, $this->warnDepth ]
			);
		}
	}

	/**
	 * Report on top-level key count.
	 *
	 * @param File $phpcsFile    The file being scanned.
	 * @param int  $stackPtr     The position to report at.
	 * @param int  $topLevelKeys Number of top-level associative keys.
	 */
	private function checkKeys( File $phpcsFile, int $stackPtr, int $topLevelKeys ): void {
		if ( $topLevelKeys > $this->errorKeys ) {
			$phpcsFile->addError(
				'Associative array has %s keys; consider using a typed object (max: %s)',
				$stackPtr,
				'TooManyKeysError',
				[ $topLevelKeys, $this->errorKeys ]
			);
		} elseif ( $topLevelKeys > $this->warnKeys ) {
			$phpcsFile->addWarning(
				'Associative array has %s keys; consider using a typed object (max: %s)',
				$stackPtr,
				'TooManyKeys',
				[ $topLevelKeys, $this->warnKeys ]
			);
		}
	}
}
