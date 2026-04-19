<?php
declare(strict_types=1);

/**
 * Discourages overly complex array structures.
 *
 * @package Apermo\Sniffs\DataStructures
 */

namespace Apermo\Sniffs\DataStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Flags deeply nested or wide associative arrays that would benefit
 * from being typed objects (DTOs, value objects).
 *
 * Three independent checks fire:
 * - Literal nesting depth: counts associative nesting levels in an
 *   outermost array literal (warning / error).
 * - Literal key count: counts top-level associative keys in an
 *   outermost array literal (warning / error).
 * - Parameter shape complexity: flags custom function/method/closure
 *   parameters whose default value or `@param array{...}` docblock
 *   shape exceeds `parameterMaxKeys` or `parameterMaxDepth` (error).
 *
 * For literals, the outermost-array-only limit means the canonical
 * fix for a `TooDeep` warning is to extract the complex sub-array
 * into its own variable, which splits one deep literal into two
 * shallower ones.
 */
class ArrayComplexitySniff implements Sniff {

	/**
	 * Associative nesting depth that triggers a warning.
	 *
	 * Depth 3 is the shape of a `WP_Query` call with a `meta_query` — idiomatic
	 * WordPress usage. The default stays silent on that pattern; deeper
	 * nesting is the author's own design and warrants a nudge.
	 *
	 * @var int
	 */
	public int $warnDepth = 3;

	/**
	 * Associative nesting depth that triggers an error.
	 *
	 * @var int
	 */
	public int $errorDepth = 5;

	/**
	 * Number of top-level keys that triggers a warning.
	 *
	 * Typical `WP_Query` arg sets land in the 5–8 range; 10 keeps normal
	 * WordPress API calls silent while flagging larger ad-hoc arrays.
	 *
	 * @var int
	 */
	public int $warnKeys = 10;

	/**
	 * Number of top-level keys that triggers an error.
	 *
	 * @var int
	 */
	public int $errorKeys = 20;

	/**
	 * Max associative nesting depth allowed in a parameter array shape.
	 *
	 * Stricter than the literal thresholds: the function author owns the
	 * signature and can refactor to a DTO. Exceeding this triggers an
	 * error on `ComplexParameterDepth`.
	 *
	 * @var int
	 */
	public int $parameterMaxDepth = 2;

	/**
	 * Max top-level keys allowed in a parameter array shape.
	 *
	 * Stricter than the literal thresholds: the function author owns the
	 * signature and can refactor to a DTO. Exceeding this triggers an
	 * error on `ComplexParameterKeys`.
	 *
	 * @var int
	 */
	public int $parameterMaxKeys = 5;

	/**
	 * Returns an array of tokens this sniff listens for.
	 *
	 * @return array<int|string>
	 */
	public function register() {
		return [ T_OPEN_SHORT_ARRAY, T_ARRAY, T_FUNCTION, T_CLOSURE ];
	}

	/**
	 * Processes a token.
	 *
	 * For array tokens, returns the position after the array closer so
	 * PHPCS skips nested arrays — only outermost arrays are analyzed.
	 *
	 * For function/closure tokens, inspects parameter default values and
	 * `@param array{...}` docblock shapes and reports parameter-signature
	 * complexity errors. No skip — the function body is still walked.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return int|void Next position to process, or void to continue normally.
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$code   = $tokens[ $stackPtr ]['code'];

		if ( $code === T_FUNCTION || $code === T_CLOSURE ) {
			$this->processFunctionSignature( $phpcsFile, $stackPtr );
			return;
		}

		if ( $code === T_OPEN_SHORT_ARRAY ) {
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
	 * Analyzes an array for associative nesting depth and key count.
	 *
	 * @param File                    $phpcsFile The file being scanned.
	 * @param int                     $stackPtr  The position of the array token (for reporting).
	 * @param array<int, array<mixed>> $tokens    Token stack.
	 * @param int                     $opener    Position of the array opener.
	 * @param int                     $closer    Position of the array closer.
	 */
	private function analyze( File $phpcsFile, int $stackPtr, array $tokens, int $opener, int $closer ): void {
		$result = $this->countArrayComplexity( $tokens, $opener, $closer );

		$this->checkDepth( $phpcsFile, $stackPtr, $result['depth'] );
		$this->checkKeys( $phpcsFile, $stackPtr, $result['keys'] );
	}

	/**
	 * Walks tokens between an array opener and closer in a single pass,
	 * tracking associative nesting depth and top-level key count.
	 *
	 * @param array<int, array<mixed>> $tokens Token stack.
	 * @param int                      $opener Position of the array opener.
	 * @param int                      $closer Position of the array closer.
	 *
	 * @return array{depth: int, keys: int}
	 */
	private function countArrayComplexity( array $tokens, int $opener, int $closer ): array {
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

		return [
			'depth' => $maxAssocCount,
			'keys'  => $topLevelKeys,
		];
	}

	/**
	 * Reports on associative nesting depth.
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
	 * Reports on top-level key count.
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

	/**
	 * Inspects a function/method/closure signature for complex parameter shapes.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  Position of the T_FUNCTION / T_CLOSURE token.
	 */
	private function processFunctionSignature( File $phpcsFile, int $stackPtr ): void {
		$this->checkParameterDefaults( $phpcsFile, $stackPtr );
		$this->checkParameterDocBlockShapes( $phpcsFile, $stackPtr );
	}

	/**
	 * Flags parameters whose default value is a complex array literal.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  Position of the T_FUNCTION / T_CLOSURE token.
	 */
	private function checkParameterDefaults( File $phpcsFile, int $stackPtr ): void {
		$tokens = $phpcsFile->getTokens();

		try {
			$params = $phpcsFile->getMethodParameters( $stackPtr );
		} catch ( \Exception $e ) {
			return;
		}

		foreach ( $params as $param ) {
			if ( ! isset( $param['default_token'] ) ) {
				continue;
			}

			$defaultPtr  = $param['default_token'];
			$defaultCode = $tokens[ $defaultPtr ]['code'] ?? null;

			if ( $defaultCode === T_OPEN_SHORT_ARRAY ) {
				$opener = $defaultPtr;
				$closer = $tokens[ $defaultPtr ]['bracket_closer'] ?? null;
			} elseif ( $defaultCode === T_ARRAY ) {
				$opener = $tokens[ $defaultPtr ]['parenthesis_opener'] ?? null;
				$closer = $tokens[ $defaultPtr ]['parenthesis_closer'] ?? null;
			} else {
				continue;
			}

			if ( $opener === null || $closer === null ) {
				continue;
			}

			$result = $this->countArrayComplexity( $tokens, $opener, $closer );
			$this->reportParameterComplexity( $phpcsFile, $opener, $param['name'], $result );
		}
	}

	/**
	 * Flags parameters whose `@param array{...}` docblock shape is complex.
	 *
	 * Parses PHPStan/Psalm shape syntax — splits top-level entries by
	 * comma (respecting nested braces) for the key count, and recurses
	 * into nested `array{...}` entries for the depth.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  Position of the T_FUNCTION / T_CLOSURE token.
	 */
	private function checkParameterDocBlockShapes( File $phpcsFile, int $stackPtr ): void {
		$tokens = $phpcsFile->getTokens();

		$skip   = Tokens::$methodPrefixes;
		$skip[] = T_WHITESPACE;

		$commentEnd = $phpcsFile->findPrevious( $skip, $stackPtr - 1, null, true );
		if ( $commentEnd === false
			|| ! isset( $tokens[ $commentEnd ]['code'] )
			|| $tokens[ $commentEnd ]['code'] !== T_DOC_COMMENT_CLOSE_TAG
		) {
			return;
		}

		$commentStart = $tokens[ $commentEnd ]['comment_opener'] ?? null;
		if ( $commentStart === null ) {
			return;
		}

		for ( $i = $commentStart; $i <= $commentEnd; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_DOC_COMMENT_TAG ) {
				continue;
			}
			if ( $tokens[ $i ]['content'] !== '@param' ) {
				continue;
			}

			$content = $this->extractTagContent( $tokens, $i, $commentEnd );
			if ( $content === '' ) {
				continue;
			}

			$shapeBody = $this->extractArrayShapeBody( $content );
			if ( $shapeBody === null ) {
				continue;
			}

			$paramName = $this->extractParamName( $content );
			if ( $paramName === '' ) {
				$paramName = '(unnamed)';
			}

			$result = $this->analyzeShapeBody( $shapeBody );
			$this->reportParameterComplexity( $phpcsFile, $i, $paramName, $result );
		}
	}

	/**
	 * Reports parameter-shape violations.
	 *
	 * @param File                          $phpcsFile The file being scanned.
	 * @param int                           $reportAt  Token position to attach the message to.
	 * @param string                        $paramName The parameter name (for the message).
	 * @param array{depth: int, keys: int}  $result    Complexity measurements.
	 */
	private function reportParameterComplexity( File $phpcsFile, int $reportAt, string $paramName, array $result ): void {
		if ( $result['keys'] > $this->parameterMaxKeys ) {
			$phpcsFile->addError(
				'Parameter %s declares an array shape with %s keys; consider a typed object (max: %s)',
				$reportAt,
				'ComplexParameterKeys',
				[ $paramName, $result['keys'], $this->parameterMaxKeys ]
			);
		}

		if ( $result['depth'] > $this->parameterMaxDepth ) {
			$phpcsFile->addError(
				'Parameter %s declares a shape nested %s levels deep; consider a typed object (max: %s)',
				$reportAt,
				'ComplexParameterDepth',
				[ $paramName, $result['depth'], $this->parameterMaxDepth ]
			);
		}
	}

	/**
	 * Concatenates all T_DOC_COMMENT_STRING tokens belonging to a single tag.
	 *
	 * Multi-line tag values (e.g. a shape split across lines) are joined
	 * with spaces, so `extractArrayShapeBody()` can operate on a single
	 * string regardless of source layout.
	 *
	 * @param array<int, array<mixed>> $tokens     Token stack.
	 * @param int                      $tagPtr     Position of the T_DOC_COMMENT_TAG.
	 * @param int                      $commentEnd Position of the T_DOC_COMMENT_CLOSE_TAG.
	 */
	private function extractTagContent( array $tokens, int $tagPtr, int $commentEnd ): string {
		$parts = [];
		for ( $i = $tagPtr + 1; $i <= $commentEnd; $i++ ) {
			$code = $tokens[ $i ]['code'];
			if ( $code === T_DOC_COMMENT_TAG || $code === T_DOC_COMMENT_CLOSE_TAG ) {
				break;
			}
			if ( $code === T_DOC_COMMENT_STRING ) {
				$parts[] = $tokens[ $i ]['content'];
			}
		}

		return trim( implode( ' ', $parts ) );
	}

	/**
	 * Extracts the body of the first `array{...}` or `list{...}` shape in $content.
	 *
	 * Returns the content between the outer `{` and its matching `}`,
	 * or null if no shape is present or the braces are unbalanced.
	 */
	private function extractArrayShapeBody( string $content ): ?string {
		if ( preg_match( '/\b(?:array|list)\s*\{/', $content, $match, PREG_OFFSET_CAPTURE ) !== 1 ) {
			return null;
		}

		$start = $match[0][1] + strlen( $match[0][0] );
		$depth = 1;
		$len   = strlen( $content );

		for ( $i = $start; $i < $len; $i++ ) {
			if ( $content[ $i ] === '{' ) {
				$depth++;
			} elseif ( $content[ $i ] === '}' ) {
				$depth--;
				if ( $depth === 0 ) {
					return substr( $content, $start, $i - $start );
				}
			}
		}

		return null;
	}

	/**
	 * Extracts the first `$name` token from $content.
	 */
	private function extractParamName( string $content ): string {
		if ( preg_match( '/\$(\w+)/', $content, $match ) === 1 ) {
			return '$' . $match[1];
		}

		return '';
	}

	/**
	 * Counts top-level entries and max nesting depth of an array-shape body.
	 *
	 * Walks $body character by character, tracking brace depth. Commas at
	 * depth 0 separate top-level entries. Nested `{...}` blocks are
	 * analyzed recursively and contribute to the reported depth.
	 *
	 * @return array{depth: int, keys: int}
	 */
	private function analyzeShapeBody( string $body ): array {
		$len         = strlen( $body );
		$keys        = 0;
		$hasContent  = false;
		$nestedMax   = 0;
		$braceDepth  = 0;
		$nestedStart = null;

		for ( $i = 0; $i < $len; $i++ ) {
			$ch = $body[ $i ];

			if ( $ch === '{' ) {
				if ( $braceDepth === 0 ) {
					$nestedStart = $i + 1;
				}
				$braceDepth++;
				continue;
			}

			if ( $ch === '}' ) {
				$braceDepth--;
				if ( $braceDepth === 0 && $nestedStart !== null ) {
					$nested      = $this->analyzeShapeBody( substr( $body, $nestedStart, $i - $nestedStart ) );
					$nestedMax   = max( $nestedMax, $nested['depth'] );
					$nestedStart = null;
				}
				continue;
			}

			if ( $braceDepth > 0 ) {
				continue;
			}

			if ( $ch === ',' ) {
				if ( $hasContent ) {
					$keys++;
					$hasContent = false;
				}
				continue;
			}

			if ( ! ctype_space( $ch ) ) {
				$hasContent = true;
			}
		}

		if ( $hasContent ) {
			$keys++;
		}

		return [
			'depth' => 1 + $nestedMax,
			'keys'  => $keys,
		];
	}
}
