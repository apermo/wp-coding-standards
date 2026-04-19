<?php
declare(strict_types=1);

/**
 * Flags strpos/strstr comparison patterns replaceable by modern PHP 8 functions.
 *
 * @package Apermo\Sniffs\PHP
 */

namespace Apermo\Sniffs\PHP;

use Apermo\Sniffs\Helpers\FunctionCallDetectorTrait;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Flags strpos()/strstr() comparison patterns that can be replaced by
 * str_contains() or str_starts_with() (PHP 8.0+).
 *
 * Detected patterns:
 * - strpos($h, $n) === false  / !== false  -> str_contains()
 * - strpos($h, $n) === 0     / !== 0      -> str_starts_with()
 * - false === strpos($h, $n) / false !== strpos($h, $n)
 * - 0 === strpos($h, $n)     / 0 !== strpos($h, $n)
 * - strstr($h, $n) === false / !== false   -> str_contains()
 * - false === strstr($h, $n) / false !== strstr($h, $n)
 *
 * Error codes:
 * - StrContains:   pattern replaceable by str_contains()
 * - StrStartsWith: pattern replaceable by str_starts_with()
 */
class PreferModernStringFunctionsSniff implements Sniff {

	use FunctionCallDetectorTrait;

	/**
	 * Whether to report violations as errors (true) or warnings (false).
	 *
	 * @var bool
	 */
	public $error = true;

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

		if ( $funcName !== 'strpos' && $funcName !== 'strstr' ) {
			return;
		}

		if ( ! $this->isFunctionCall( $phpcsFile, $stackPtr ) ) {
			return;
		}

		// isFunctionCall() already confirmed the open paren; find the closer.
		$open  = $phpcsFile->findNext( T_WHITESPACE, $stackPtr + 1, null, true );
		$close = $tokens[ $open ]['parenthesis_closer'];

		// Try forward pattern: func(...) === false / === 0.
		if ( $this->checkForwardPattern( $phpcsFile, $stackPtr, $funcName, $close ) ) {
			return;
		}

		// Try reversed pattern: false === func(...) / 0 === func(...).
		$this->checkReversedPattern( $phpcsFile, $stackPtr, $funcName );
	}

	/**
	 * Checks for pattern: strpos/strstr(...) === false|0.
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  Position of the function name token.
	 * @param string $funcName  Lowercase function name (strpos or strstr).
	 * @param int    $closePtr  Position of the closing parenthesis.
	 *
	 * @return bool Whether a violation was reported.
	 */
	private function checkForwardPattern( File $phpcsFile, int $stackPtr, string $funcName, int $closePtr ): bool {
		$tokens = $phpcsFile->getTokens();

		// Look for === or !== after the closing paren.
		$operator = $phpcsFile->findNext( T_WHITESPACE, $closePtr + 1, null, true );
		$opCode   = $operator !== false ? $tokens[ $operator ]['code'] : null;

		if ( $opCode !== T_IS_IDENTICAL && $opCode !== T_IS_NOT_IDENTICAL ) {
			return false;
		}

		// Look for the operand after the operator.
		$operand = $phpcsFile->findNext( T_WHITESPACE, $operator + 1, null, true );
		if ( $operand === false ) {
			return false;
		}

		return $this->reportIfMatch( $phpcsFile, $stackPtr, $funcName, $operand );
	}

	/**
	 * Checks for reversed pattern: false|0 === strpos/strstr(...).
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  Position of the function name token.
	 * @param string $funcName  Lowercase function name (strpos or strstr).
	 *
	 * @return bool Whether a violation was reported.
	 */
	private function checkReversedPattern( File $phpcsFile, int $stackPtr, string $funcName ): bool {
		$tokens = $phpcsFile->getTokens();

		// Look back for === or !==.
		$operator = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );
		$opCode   = $operator !== false ? $tokens[ $operator ]['code'] : null;

		if ( $opCode !== T_IS_IDENTICAL && $opCode !== T_IS_NOT_IDENTICAL ) {
			return false;
		}

		// Look back for the operand before the operator.
		$operand = $phpcsFile->findPrevious( T_WHITESPACE, $operator - 1, null, true );
		if ( $operand === false ) {
			return false;
		}

		return $this->reportIfMatch( $phpcsFile, $stackPtr, $funcName, $operand );
	}

	/**
	 * Checks the operand and report a violation if it matches false or 0.
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  Position of the function name token.
	 * @param string $funcName  Lowercase function name.
	 * @param int    $operand   Position of the comparison operand.
	 *
	 * @return bool Whether a violation was reported.
	 */
	private function reportIfMatch( File $phpcsFile, int $stackPtr, string $funcName, int $operand ): bool {
		$tokens       = $phpcsFile->getTokens();
		$operandCode  = $tokens[ $operand ]['code'];
		$operandValue = strtolower( $tokens[ $operand ]['content'] );

		// Check for false comparison -> str_contains().
		if ( $operandCode === T_FALSE || $operandValue === 'false' ) {
			$this->report(
				$phpcsFile,
				$stackPtr,
				'Use str_contains() instead of %s() comparison with false',
				'StrContains',
				$funcName,
			);
			return true;
		}

		// Check for 0 comparison -> str_starts_with() (strpos only).
		if ( $funcName === 'strpos' && $operandCode === T_LNUMBER && $operandValue === '0' ) {
			$this->report(
				$phpcsFile,
				$stackPtr,
				'Use str_starts_with() instead of %s() comparison with 0',
				'StrStartsWith',
				$funcName,
			);
			return true;
		}

		return false;
	}

	/**
	 * Reports a violation as error or warning based on configuration.
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  Position to report on.
	 * @param string $message   Error message with %s placeholder.
	 * @param string $code      Error code.
	 * @param string $funcName  Function name for the message.
	 *
	 * @return void
	 */
	private function report( File $phpcsFile, int $stackPtr, string $message, string $code, string $funcName ): void {
		if ( $this->error ) {
			$phpcsFile->addError( $message, $stackPtr, $code, [ $funcName ] );
		} else {
			$phpcsFile->addWarning( $message, $stackPtr, $code, [ $funcName ] );
		}
	}
}
