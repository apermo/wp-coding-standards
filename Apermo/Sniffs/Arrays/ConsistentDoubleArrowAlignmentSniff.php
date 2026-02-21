<?php
/**
 * Enforce consistent => alignment within arrays.
 *
 * @package Apermo\Sniffs\Arrays
 */

namespace Apermo\Sniffs\Arrays;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures that multi-line associative arrays use either single-space or
 * aligned spacing before the `=>` operator — but not a mixture of both.
 *
 * Only outermost arrays are checked; nested arrays are skipped.
 */
class ConsistentDoubleArrowAlignmentSniff implements Sniff {

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
			$opener = $tokens[ $stackPtr ]['parenthesis_opener'] ?? null;
			$closer = $tokens[ $stackPtr ]['parenthesis_closer'] ?? null;
		}

		if ( $opener === null || $closer === null ) {
			return $stackPtr + 1;
		}

		// Only check multi-line arrays.
		if ( $tokens[ $opener ]['line'] === $tokens[ $closer ]['line'] ) {
			return $closer + 1;
		}

		$arrows = $this->collectTopLevelArrows( $phpcsFile, $opener, $closer );

		if ( count( $arrows ) < 2 ) {
			return $closer + 1;
		}

		$this->checkConsistency( $phpcsFile, $arrows );

		return $closer + 1;
	}

	/**
	 * Collect top-level T_DOUBLE_ARROW tokens and their spacing info.
	 *
	 * Skips nested arrays, closures, and anonymous classes.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $opener    Array opener position.
	 * @param int  $closer    Array closer position.
	 *
	 * @return array<int, array{ptr: int, spaces: int, column: int, lhs_end: int}> Arrow info.
	 */
	private function collectTopLevelArrows( File $phpcsFile, int $opener, int $closer ): array {
		$tokens = $phpcsFile->getTokens();
		$arrows = [];

		for ( $i = $opener + 1; $i < $closer; $i++ ) {
			$code = $tokens[ $i ]['code'];

			// Skip nested short arrays.
			if ( $code === T_OPEN_SHORT_ARRAY && isset( $tokens[ $i ]['bracket_closer'] ) ) {
				$i = $tokens[ $i ]['bracket_closer'];
				continue;
			}

			// Skip nested long arrays.
			if ( $code === T_ARRAY && isset( $tokens[ $i ]['parenthesis_closer'] ) ) {
				$i = $tokens[ $i ]['parenthesis_closer'];
				continue;
			}

			// Skip closures and anonymous classes.
			if ( in_array( $code, [ T_CLOSURE, T_ANON_CLASS, T_FN ], true )
				&& isset( $tokens[ $i ]['scope_closer'] )
			) {
				$i = $tokens[ $i ]['scope_closer'];
				continue;
			}

			if ( $code !== T_DOUBLE_ARROW ) {
				continue;
			}

			$spaces = $this->measureSpacesBefore( $phpcsFile, $i );

			$prev   = $phpcsFile->findPrevious( T_WHITESPACE, $i - 1, null, true );
			$lhsEnd = ( $prev !== false && $tokens[ $prev ]['line'] === $tokens[ $i ]['line'] )
				? $tokens[ $prev ]['column'] + $tokens[ $prev ]['length']
				: $tokens[ $i ]['column'];

			$arrows[] = [
				'ptr'     => $i,
				'spaces'  => $spaces,
				'column'  => $tokens[ $i ]['column'],
				'lhs_end' => $lhsEnd,
			];
		}

		return $arrows;
	}

	/**
	 * Measure spaces between end of key and the => operator.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $arrowPtr  The T_DOUBLE_ARROW token position.
	 *
	 * @return int Number of spaces before =>.
	 */
	private function measureSpacesBefore( File $phpcsFile, int $arrowPtr ): int {
		$tokens = $phpcsFile->getTokens();

		$prev = $phpcsFile->findPrevious( T_WHITESPACE, $arrowPtr - 1, null, true );
		if ( $prev === false ) {
			return 0;
		}

		if ( $tokens[ $prev ]['line'] !== $tokens[ $arrowPtr ]['line'] ) {
			return 0;
		}

		$endOfKey = $tokens[ $prev ]['column'] + $tokens[ $prev ]['length'];
		$arrowCol = $tokens[ $arrowPtr ]['column'];

		return max( 0, $arrowCol - $endOfKey );
	}

	/**
	 * Check consistency within collected arrows and report violations.
	 *
	 * Two valid styles:
	 * - Single-space: all `=>` have exactly 1 space before them
	 * - Aligned: all `=>` are at the same column
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $arrows    The collected arrow info.
	 *
	 * @return void
	 */
	private function checkConsistency( File $phpcsFile, array $arrows ): void {
		$tokens         = $phpcsFile->getTokens();
		$allSingleSpace = true;
		$allSameColumn  = true;
		$firstColumn    = $arrows[0]['column'];
		$longestLhsEnd  = 0;

		foreach ( $arrows as $arrow ) {
			if ( $arrow['spaces'] !== 1 ) {
				$allSingleSpace = false;
			}

			if ( $arrow['column'] !== $firstColumn ) {
				$allSameColumn = false;
			}

			if ( $arrow['lhs_end'] > $longestLhsEnd ) {
				$longestLhsEnd = $arrow['lhs_end'];
			}
		}

		if ( $allSingleSpace ) {
			return;
		}

		// All operators aligned — check for over-alignment.
		if ( $allSameColumn ) {
			$minColumn = $longestLhsEnd + 1;
			if ( $firstColumn > $minColumn ) {
				foreach ( $arrows as $arrow ) {
					$phpcsFile->addError(
						'Double arrows are over-aligned; reduce to single space after the longest key',
						$arrow['ptr'],
						'OverAligned'
					);
				}
			}

			return;
		}

		// Determine majority style.
		$singleCount  = 0;
		$columnCounts = [];

		foreach ( $arrows as $arrow ) {
			if ( $arrow['spaces'] === 1 ) {
				$singleCount++;
			}

			$col = $arrow['column'];
			$columnCounts[ $col ] = ( $columnCounts[ $col ] ?? 0 ) + 1;
		}

		$maxColumnCount = max( $columnCounts );
		$alignedColumn  = array_search( $maxColumnCount, $columnCounts, true );
		$alignedCount   = $maxColumnCount;

		$useAligned = ( $alignedCount >= $singleCount );

		if ( $useAligned ) {
			$minColumn = $longestLhsEnd + 1;
			if ( $alignedColumn > $minColumn ) {
				foreach ( $arrows as $arrow ) {
					$phpcsFile->addError(
						'Double arrows are over-aligned; reduce to single space after the longest key',
						$arrow['ptr'],
						'OverAligned'
					);
				}

				return;
			}

			foreach ( $arrows as $arrow ) {
				if ( $arrow['column'] === $alignedColumn ) {
					continue;
				}

				$targetSpaces = $alignedColumn - $arrow['lhs_end'];
				if ( $targetSpaces < 1 ) {
					$phpcsFile->addWarning(
						'Double arrow alignment is inconsistent within this array; either align all or use single spaces',
						$arrow['ptr'],
						'InconsistentAlignment'
					);
				} else {
					$fix = $phpcsFile->addFixableWarning(
						'Double arrow alignment is inconsistent within this array; either align all or use single spaces',
						$arrow['ptr'],
						'InconsistentAlignment'
					);

					if ( $fix === true ) {
						$wsPtr = $arrow['ptr'] - 1;
						if ( $tokens[ $wsPtr ]['code'] === T_WHITESPACE ) {
							$phpcsFile->fixer->replaceToken( $wsPtr, str_repeat( ' ', $targetSpaces ) );
						} else {
							$phpcsFile->fixer->addContentBefore( $arrow['ptr'], str_repeat( ' ', $targetSpaces ) );
						}
					}
				}
			}
		} else {
			foreach ( $arrows as $arrow ) {
				if ( $arrow['spaces'] === 1 ) {
					continue;
				}

				$fix = $phpcsFile->addFixableWarning(
					'Double arrow alignment is inconsistent within this array; either align all or use single spaces',
					$arrow['ptr'],
					'InconsistentAlignment'
				);

				if ( $fix === true ) {
					$wsPtr = $arrow['ptr'] - 1;
					if ( $tokens[ $wsPtr ]['code'] === T_WHITESPACE ) {
						$phpcsFile->fixer->replaceToken( $wsPtr, ' ' );
					}
				}
			}
		}
	}
}
