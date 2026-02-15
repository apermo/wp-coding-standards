<?php
/**
 * Require explained doc-blocks before commented-out code.
 *
 * @package Apermo\Sniffs\PHP
 */

namespace Apermo\Sniffs\PHP;

use PHP_CodeSniffer\Exceptions\TokenizerException;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Enforces that any commented-out PHP code (in // comments) is preceded by
 * a structured /** doc-block explanation using a recognized keyword.
 *
 * Supersedes Squiz.PHP.CommentedOutCode with a stricter, actionable rule.
 */
class ExplainCommentedOutCodeSniff implements Sniff {

	/**
	 * Code-token ratio threshold for detecting commented-out code.
	 *
	 * @var int
	 */
	public $maxPercentage = 35;

	/**
	 * Whether to report violations as errors (true) or warnings (false).
	 *
	 * @var bool
	 */
	public $error = true;

	/**
	 * Comma-separated list of accepted keywords for explanation doc-blocks.
	 *
	 * @var string
	 */
	public $keywords = 'Disabled,Kept,Debug,Review,WIP';

	/**
	 * Returns an array of tokens this sniff listens for.
	 *
	 * @return array<int>
	 */
	public function register() {
		return [ T_COMMENT ];
	}

	/**
	 * Processes a token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return int|void Stack pointer to skip to, or void.
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens  = $phpcsFile->getTokens();
		$content = ltrim( $tokens[ $stackPtr ]['content'] );

		// Only check // line comments.
		if ( strpos( $content, '//' ) !== 0 ) {
			return;
		}

		// Skip PHPCS //end markers.
		if ( strpos( $content, '//end ' ) === 0 ) {
			return;
		}

		// Collect consecutive // comment block.
		list( $block_content, $last_ptr ) = $this->collectCommentBlock( $phpcsFile, $stackPtr );

		// Check if this block is commented-out code.
		if ( ! $this->isCommentedOutCode( $phpcsFile, $block_content ) ) {
			return ( $last_ptr + 1 );
		}

		// Code detected - look for a preceding /** */ doc-block.
		$doc_content = $this->findExplanationDocBlock( $phpcsFile, $stackPtr );
		if ( $doc_content !== null && $this->isValidExplanation( $doc_content ) ) {
			return ( $last_ptr + 1 );
		}

		// No valid explanation found.
		$keyword_list = array_map( 'trim', explode( ',', $this->keywords ) );
		$message      = 'Commented-out code must be preceded by a /** */ doc-block starting with a recognized keyword (%s).';
		$data         = [ implode( ', ', $keyword_list ) ];

		if ( $this->error ) {
			$phpcsFile->addError( $message, $stackPtr, 'MissingExplanation', $data );
		} else {
			$phpcsFile->addWarning( $message, $stackPtr, 'MissingExplanation', $data );
		}

		return ( $last_ptr + 1 );
	}

	/**
	 * Collect consecutive // comment lines into a single block.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  Starting token position.
	 *
	 * @return array{0: string, 1: int} Block content and last token pointer.
	 */
	private function collectCommentBlock( File $phpcsFile, int $stackPtr ): array {
		$tokens    = $phpcsFile->getTokens();
		$content   = '';
		$last_ptr  = $stackPtr;
		$last_line = $tokens[ $stackPtr ]['line'];

		for ( $i = $stackPtr; $i < $phpcsFile->numTokens; $i++ ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				continue;
			}

			if ( $tokens[ $i ]['code'] !== T_COMMENT ) {
				break;
			}

			$trimmed = ltrim( $tokens[ $i ]['content'] );

			// Only collect // comments.
			if ( strpos( $trimmed, '//' ) !== 0 ) {
				break;
			}

			// Blank line breaks the block.
			if ( $tokens[ $i ]['line'] > ( $last_line + 1 ) ) {
				break;
			}

			$content  .= substr( $trimmed, 2 ) . $phpcsFile->eolChar;
			$last_line = $tokens[ $i ]['line'];
			$last_ptr  = $i;
		}

		return [ $content, $last_ptr ];
	}

	/**
	 * Search backwards for a doc-block within the allowed gap.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  Position of the first code comment token.
	 *
	 * @return string|null Doc-block text content, or null if not found.
	 */
	private function findExplanationDocBlock( File $phpcsFile, int $stackPtr ): ?string {
		$tokens    = $phpcsFile->getTokens();
		$code_line = $tokens[ $stackPtr ]['line'];

		for ( $i = ( $stackPtr - 1 ); $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['code'] === T_WHITESPACE ) {
				continue;
			}

			if ( $tokens[ $i ]['code'] === T_DOC_COMMENT_CLOSE_TAG ) {
				$close_line = $tokens[ $i ]['line'];

				// Allow at most 1 blank line gap.
				if ( ( $code_line - $close_line ) > 2 ) {
					return null;
				}

				// Walk back to the opening tag.
				$open_ptr = null;
				for ( $j = ( $i - 1 ); $j >= 0; $j-- ) {
					if ( $tokens[ $j ]['code'] === T_DOC_COMMENT_OPEN_TAG ) {
						$open_ptr = $j;
						break;
					}
				}

				if ( $open_ptr === null ) {
					return null;
				}

				// Extract T_DOC_COMMENT_STRING content.
				$doc_content = '';
				for ( $j = $open_ptr; $j <= $i; $j++ ) {
					if ( $tokens[ $j ]['code'] === T_DOC_COMMENT_STRING ) {
						$doc_content .= $tokens[ $j ]['content'] . "\n";
					}
				}

				return $doc_content;
			}

			// Hit a non-whitespace, non-doc-close token.
			return null;
		}

		return null;
	}

	/**
	 * Check if the doc-block content starts with a recognized keyword.
	 *
	 * @param string $doc_content The extracted doc-block text.
	 *
	 * @return bool
	 */
	private function isValidExplanation( string $doc_content ): bool {
		$keyword_list = array_map( 'trim', explode( ',', $this->keywords ) );
		$pattern      = implode( '|', array_map( 'preg_quote', $keyword_list ) );

		$lines      = explode( "\n", trim( $doc_content ) );
		$first_line = trim( $lines[0] );

		return preg_match( '/^(' . $pattern . ')(\s+\(\d{4}-\d{2}-\d{2}\))?:\s+\S/', $first_line ) === 1;
	}

	/**
	 * Determine if stripped comment content looks like PHP code.
	 *
	 * Uses the same heuristic as Squiz.PHP.CommentedOutCode.
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param string $content   The comment content with // prefixes removed.
	 *
	 * @return bool
	 */
	private function isCommentedOutCode( File $phpcsFile, string $content ): bool {
		// Ignore warning suppression annotations.
		if ( preg_match( '`^\s*@[A-Za-z()\._-]+\s*$`', $content ) === 1 ) {
			return false;
		}

		// Normalize decorative characters.
		$content = preg_replace( '/[-=#*]{2,}/', '-', $content );

		// Strip digits to avoid PHP 7+ literal parse errors.
		$content = preg_replace( '/\d+/', '', $content );

		$content = trim( $content );
		if ( $content === '' ) {
			return false;
		}

		$content = '<?php ' . $content . ' ?>';

		// Suppress tokenizer warnings.
		$old_errors = ini_get( 'error_reporting' );
		ini_set( 'error_reporting', 0 );

		try {
			$tokenizer_class = get_class( $phpcsFile->tokenizer );
			$tokenizer       = new $tokenizer_class( $content, $phpcsFile->config, $phpcsFile->eolChar );
			$string_tokens   = $tokenizer->getTokens();
		} catch ( TokenizerException $e ) {
			ini_set( 'error_reporting', $old_errors );
			return false;
		}

		ini_set( 'error_reporting', $old_errors );

		$num_tokens = count( $string_tokens );

		// Validate structure: first token must be opening tag.
		if ( $string_tokens[0]['code'] !== T_OPEN_TAG ) {
			return false;
		}

		array_shift( $string_tokens );
		--$num_tokens;

		// Last token must be closing tag.
		if ( ! isset( $string_tokens[ $num_tokens - 1 ] )
			|| $string_tokens[ $num_tokens - 1 ]['code'] !== T_CLOSE_TAG
		) {
			return false;
		}

		array_pop( $string_tokens );
		--$num_tokens;

		if ( $num_tokens === 0 ) {
			return false;
		}

		// Second-to-last must be an empty token.
		if ( isset( Tokens::$emptyTokens[ $string_tokens[ $num_tokens - 1 ]['code'] ] ) === false ) {
			return false;
		}

		if ( $string_tokens[ $num_tokens - 1 ]['code'] === T_WHITESPACE ) {
			array_pop( $string_tokens );
			--$num_tokens;
		}

		if ( $num_tokens === 0 ) {
			return false;
		}

		$empty_tokens = [
			T_WHITESPACE              => true,
			T_STRING                  => true,
			T_STRING_CONCAT           => true,
			T_ENCAPSED_AND_WHITESPACE => true,
			T_NONE                    => true,
			T_COMMENT                 => true,
		];
		$empty_tokens += Tokens::$phpcsCommentTokens;

		$num_code           = 0;
		$num_non_whitespace = 0;

		for ( $i = 0; $i < $num_tokens; $i++ ) {
			if ( ! isset( $empty_tokens[ $string_tokens[ $i ]['code'] ] )
				&& ! isset( Tokens::$comparisonTokens[ $string_tokens[ $i ]['code'] ] )
				&& ! isset( Tokens::$arithmeticTokens[ $string_tokens[ $i ]['code'] ] )
				&& $string_tokens[ $i ]['code'] !== T_GOTO_LABEL
			) {
				++$num_code;
			}

			if ( $string_tokens[ $i ]['code'] !== T_WHITESPACE ) {
				++$num_non_whitespace;
			}
		}

		// Too few tokens for reliable determination.
		if ( $num_non_whitespace <= 2 ) {
			return false;
		}

		$percent_code = ceil( ( $num_code / $num_tokens ) * 100 );

		return $percent_code > $this->maxPercentage;
	}
}
