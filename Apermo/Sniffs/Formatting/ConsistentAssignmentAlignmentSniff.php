<?php
/**
 * Enforce consistent alignment within assignment groups.
 *
 * @package Apermo\Sniffs\Formatting
 */

namespace Apermo\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Ensures that consecutive assignment statements at the same nesting level
 * use either single-space or aligned spacing before the `=` operator —
 * but not a mixture of both styles within the same group.
 *
 * A group breaks on: blank line, non-assignment statement, nesting
 * level change, scope opener, or EOF.
 */
class ConsistentAssignmentAlignmentSniff implements Sniff {

	/**
	 * Returns an array of tokens this sniff listens for.
	 *
	 * @return array<int>
	 */
	public function register() {
		$tokens = Tokens::$assignmentTokens;
		unset( $tokens[ T_DOUBLE_ARROW ] );

		return array_keys( $tokens );
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

		// Skip assignments inside for() parentheses.
		if ( ! empty( $tokens[ $stackPtr ]['nested_parenthesis'] ) ) {
			return $stackPtr + 1;
		}

		// Only process the first assignment on this line.
		if ( $this->hasEarlierAssignmentOnLine( $phpcsFile, $stackPtr ) ) {
			return $stackPtr + 1;
		}

		// Collect the group of consecutive assignments.
		$group = $this->collectGroup( $phpcsFile, $stackPtr );

		if ( count( $group ) < 2 ) {
			return $this->skipPast( $group, $stackPtr );
		}

		$this->checkConsistency( $phpcsFile, $group );

		return $this->skipPast( $group, $stackPtr );
	}

	/**
	 * Checks whether an earlier assignment token exists on the same line.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The current assignment token position.
	 *
	 * @return bool
	 */
	private function hasEarlierAssignmentOnLine( File $phpcsFile, int $stackPtr ): bool {
		$tokens = $phpcsFile->getTokens();
		$line   = $tokens[ $stackPtr ]['line'];

		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['line'] < $line ) {
				return false;
			}

			if ( isset( Tokens::$assignmentTokens[ $tokens[ $i ]['code'] ] )
				&& $tokens[ $i ]['code'] !== T_DOUBLE_ARROW
				&& empty( $tokens[ $i ]['nested_parenthesis'] )
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Find the first assignment token on a given line, searching from a position.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $line      The line number.
	 * @param int  $from      Position to start searching from.
	 *
	 * @return int|false Token position or false.
	 */
	private function findAssignmentOnLine( File $phpcsFile, int $line, int $from ) {
		$tokens = $phpcsFile->getTokens();

		for ( $i = $from; $i < $phpcsFile->numTokens; $i++ ) {
			if ( $tokens[ $i ]['line'] > $line ) {
				break;
			}

			if ( $tokens[ $i ]['line'] < $line ) {
				continue;
			}

			if ( isset( Tokens::$assignmentTokens[ $tokens[ $i ]['code'] ] )
				&& $tokens[ $i ]['code'] !== T_DOUBLE_ARROW
				&& empty( $tokens[ $i ]['nested_parenthesis'] )
			) {
				return $i;
			}
		}

		return false;
	}

	/**
	 * Collect consecutive assignment statements into a group.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The first assignment token.
	 *
	 * @return array<int, array{ptr: int, spaces: int, column: int, line: int, lhs_end: int}> Group members.
	 */
	private function collectGroup( File $phpcsFile, int $stackPtr ): array {
		$tokens  = $phpcsFile->getTokens();
		$group   = [];
		$current = $stackPtr;

		while ( $current !== false && $current < $phpcsFile->numTokens ) {
			$spaces = $this->measureSpacesBefore( $phpcsFile, $current );
			$line   = $tokens[ $current ]['line'];
			$column = $tokens[ $current ]['column'];

			$prev   = $phpcsFile->findPrevious( T_WHITESPACE, $current - 1, null, true );
			$lhsEnd = ( $prev !== false && $tokens[ $prev ]['line'] === $line )
				? $tokens[ $prev ]['column'] + $tokens[ $prev ]['length']
				: $column;

			$group[] = [
				'ptr'     => $current,
				'spaces'  => $spaces,
				'column'  => $column,
				'line'    => $line,
				'lhs_end' => $lhsEnd,
			];

			// Find the next line's assignment.
			$nextLine = $this->findNextAssignmentOnNextLine( $phpcsFile, $current, $line );
			if ( $nextLine === false ) {
				break;
			}

			$current = $nextLine;
		}

		return $group;
	}

	/**
	 * Find an assignment token on the next consecutive line.
	 *
	 * Returns false if the next line is blank, has no assignment,
	 * or is inside parentheses (for loops etc).
	 *
	 * @param File $phpcsFile   The file being scanned.
	 * @param int  $currentPtr  Current assignment token position.
	 * @param int  $currentLine Current line number.
	 *
	 * @return int|false Next assignment token position or false.
	 */
	private function findNextAssignmentOnNextLine( File $phpcsFile, int $currentPtr, int $currentLine ) {
		$tokens   = $phpcsFile->getTokens();
		$nextLine = $currentLine + 1;

		// Find the first non-whitespace token on the next line.
		$found = false;
		for ( $i = $currentPtr + 1; $i < $phpcsFile->numTokens; $i++ ) {
			if ( $tokens[ $i ]['line'] < $nextLine ) {
				continue;
			}

			// Gap — blank line breaks the group.
			if ( $tokens[ $i ]['line'] > $nextLine ) {
				// Allow one line gap only if it's the next line (already checked).
				if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
					continue;
				}
				if ( $tokens[ $i ]['line'] > $nextLine ) {
					return false;
				}
			}

			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				continue;
			}

			$found = true;
			break;
		}

		if ( ! $found ) {
			return false;
		}

		// Check if the next non-blank line is exactly nextLine.
		if ( $tokens[ $i ]['line'] !== $nextLine ) {
			return false;
		}

		// Find an assignment on that line.
		return $this->findAssignmentOnLine( $phpcsFile, $nextLine, $i ) ?: false;
	}

	/**
	 * Measure spaces between end of LHS and the assignment operator.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $assignPtr The assignment token position.
	 *
	 * @return int Number of spaces before the operator.
	 */
	private function measureSpacesBefore( File $phpcsFile, int $assignPtr ): int {
		$tokens = $phpcsFile->getTokens();

		$prev = $phpcsFile->findPrevious( T_WHITESPACE, $assignPtr - 1, null, true );
		if ( $prev === false ) {
			return 0;
		}

		// If the previous non-whitespace token is on a different line, skip.
		if ( $tokens[ $prev ]['line'] !== $tokens[ $assignPtr ]['line'] ) {
			return 0;
		}

		// Calculate column distance.
		$endOfLhs  = $tokens[ $prev ]['column'] + $tokens[ $prev ]['length'];
		$assignCol = $tokens[ $assignPtr ]['column'];

		return max( 0, $assignCol - $endOfLhs );
	}

	/**
	 * Check consistency within a group and report violations.
	 *
	 * Two valid styles:
	 * - Single-space: all assignments have exactly 1 space before `=`
	 * - Aligned: all `=` operators are at the same column
	 *
	 * If either style is satisfied, the group is consistent. Otherwise,
	 * the minority deviators from the more popular style are flagged.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $group     The collected group of assignments.
	 *
	 * @return void
	 */
	private function checkConsistency( File $phpcsFile, array $group ): void {
		$tokens         = $phpcsFile->getTokens();
		$allSingleSpace = true;
		$allSameColumn  = true;
		$firstColumn    = $group[0]['column'];
		$longestLhsEnd  = 0;

		foreach ( $group as $member ) {
			if ( $member['spaces'] !== 1 ) {
				$allSingleSpace = false;
			}

			if ( $member['column'] !== $firstColumn ) {
				$allSameColumn = false;
			}

			if ( $member['lhs_end'] > $longestLhsEnd ) {
				$longestLhsEnd = $member['lhs_end'];
			}
		}

		if ( $allSingleSpace ) {
			return;
		}

		// All operators aligned — check for over-alignment.
		if ( $allSameColumn ) {
			$minColumn = $longestLhsEnd + 1;
			if ( $firstColumn > $minColumn ) {
				foreach ( $group as $member ) {
					$phpcsFile->addError(
						'Assignment operators are over-aligned; reduce to single space after the longest variable',
						$member['ptr'],
						'OverAligned'
					);
				}
			}

			return;
		}

		// Mixed: determine which style has more adherents.
		$singleCount  = 0;
		$columnCounts = [];

		foreach ( $group as $member ) {
			if ( $member['spaces'] === 1 ) {
				$singleCount++;
			}

			$col = $member['column'];
			$columnCounts[ $col ] = ( $columnCounts[ $col ] ?? 0 ) + 1;
		}

		$maxColumnCount = max( $columnCounts );
		$alignedColumn  = array_search( $maxColumnCount, $columnCounts, true );
		$alignedCount   = $maxColumnCount;

		// Choose majority style: aligned (same column) vs single-space.
		$useAligned = ( $alignedCount >= $singleCount );

		if ( $useAligned ) {
			$minColumn = $longestLhsEnd + 1;
			if ( $alignedColumn > $minColumn ) {
				foreach ( $group as $member ) {
					$phpcsFile->addError(
						'Assignment operators are over-aligned; reduce to single space after the longest variable',
						$member['ptr'],
						'OverAligned'
					);
				}

				return;
			}

			foreach ( $group as $member ) {
				if ( $member['column'] === $alignedColumn ) {
					continue;
				}

				$targetSpaces = $alignedColumn - $member['lhs_end'];
				if ( $targetSpaces < 1 ) {
					$phpcsFile->addWarning(
						'Assignment alignment is inconsistent with the rest of this group; either align all or use single spaces',
						$member['ptr'],
						'InconsistentAlignment'
					);
				} else {
					$fix = $phpcsFile->addFixableWarning(
						'Assignment alignment is inconsistent with the rest of this group; either align all or use single spaces',
						$member['ptr'],
						'InconsistentAlignment'
					);

					if ( $fix === true ) {
						$wsPtr = $member['ptr'] - 1;
						if ( $tokens[ $wsPtr ]['code'] === T_WHITESPACE ) {
							$phpcsFile->fixer->replaceToken( $wsPtr, str_repeat( ' ', $targetSpaces ) );
						} else {
							$phpcsFile->fixer->addContentBefore( $member['ptr'], str_repeat( ' ', $targetSpaces ) );
						}
					}
				}
			}
		} else {
			foreach ( $group as $member ) {
				if ( $member['spaces'] === 1 ) {
					continue;
				}

				$fix = $phpcsFile->addFixableWarning(
					'Assignment alignment is inconsistent with the rest of this group; either align all or use single spaces',
					$member['ptr'],
					'InconsistentAlignment'
				);

				if ( $fix === true ) {
					$wsPtr = $member['ptr'] - 1;
					if ( $tokens[ $wsPtr ]['code'] === T_WHITESPACE ) {
						$phpcsFile->fixer->replaceToken( $wsPtr, ' ' );
					}
				}
			}
		}
	}

	/**
	 * Get the stack pointer past the last member of a group.
	 *
	 * @param array $group    The collected group.
	 * @param int   $stackPtr Fallback position.
	 *
	 * @return int Next position to process.
	 */
	private function skipPast( array $group, int $stackPtr ): int {
		if ( empty( $group ) ) {
			return $stackPtr + 1;
		}

		$last = end( $group );

		return $last['ptr'] + 1;
	}
}
