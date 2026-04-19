<?php
declare(strict_types=1);

/**
 * Enforces WordPress docblock summary style.
 *
 * @package Apermo\Sniffs\Commenting
 */

namespace Apermo\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Enforces third-person singular style for docblock summaries.
 *
 * Per the WordPress PHP Documentation Standards, summaries for functions,
 * hooks, classes, and methods must be written in the third-person singular —
 * prepending "It" to the summary should read grammatically. Applies a layered
 * check: whitelist → anti-patterns → bare-infinitive closers → default
 * (first word ends in "s").
 *
 * Error codes:
 * - AntiPattern: leading phrase matches a known bad opener (e.g. "Allows you to...").
 * - BareInfinitive: first word is a bare-infinitive verb whose third-person form adds -es.
 * - NotThirdPerson: first word does not end in "s".
 */
class DocSummaryStyleSniff implements Sniff {

	/**
	 * First words that bypass the summary-style check.
	 *
	 * Common noun-lead openings that WordPress style tolerates.
	 *
	 * @var array<string>
	 */
	public array $whitelist = [
		'Callback',
		'Wrapper',
		'Helper',
		'Utility',
		'Alias',
		'Shortcut',
	];

	/**
	 * Leading phrases that flag a summary as using a known anti-pattern.
	 *
	 * Matched as a case-insensitive prefix of the cleaned summary.
	 *
	 * @var array<string>
	 */
	public array $antiPatterns = [
		'Allows you to',
		'Lets you',
		'Allow you to',
		'Let you',
		'Used to',
		'This function',
		'This method',
		'This class',
	];

	/**
	 * Bare-infinitive verbs whose third-person singular form adds -es.
	 *
	 * These slip past the default "ends in s" check because the bare
	 * infinitive itself already ends in s/ss/x.
	 *
	 * @var array<string>
	 */
	public array $bareInfinitiveClosers = [
		'Process',
		'Pass',
		'Access',
		'Focus',
		'Fix',
		'Mix',
		'Cross',
		'Miss',
		'Dismiss',
		'Address',
		'Express',
		'Bypass',
		'Discuss',
	];

	/**
	 * Returns the tokens this sniff listens for.
	 *
	 * @return array<int|string>
	 */
	public function register(): array {
		return [ T_DOC_COMMENT_OPEN_TAG ];
	}

	/**
	 * Processes a token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ): void {
		$tokens     = $phpcsFile->getTokens();
		$commentEnd = $tokens[ $stackPtr ]['comment_closer'];
		$empty      = [ T_DOC_COMMENT_WHITESPACE, T_DOC_COMMENT_STAR ];

		// Properties, constants, and bare variables use noun-form summaries;
		// the third-person rule only applies to behavior-describing docblocks
		// (functions, methods, classes, interfaces, traits, enums).
		if ( $this->describesDataDeclaration( $phpcsFile, $commentEnd ) ) {
			return;
		}

		$first = $phpcsFile->findNext( $empty, ( $stackPtr + 1 ), $commentEnd, true );

		if ( $first === false ) {
			return;
		}

		// Not a free-text summary (e.g. docblock starts with a tag like @param).
		if ( $tokens[ $first ]['code'] !== T_DOC_COMMENT_STRING ) {
			return;
		}

		$summary = $tokens[ $first ]['content'];

		// {@inheritDoc} / @inheritdoc variants — not a summary.
		if ( preg_match( '/^\s*\{?@?inheritdoc\}?\s*$/i', $summary ) === 1 ) {
			return;
		}

		$cleaned = $this->cleanSummary( $summary );
		if ( $cleaned === '' ) {
			return;
		}

		if ( preg_match( '/^([A-Za-z][A-Za-z\']*)/', $cleaned, $m ) !== 1 ) {
			return;
		}
		$firstWord = $m[1];

		foreach ( $this->whitelist as $allowed ) {
			if ( strcasecmp( $firstWord, $allowed ) === 0 ) {
				return;
			}
		}

		foreach ( $this->antiPatterns as $bad ) {
			if ( stripos( $cleaned, $bad ) === 0 ) {
				$phpcsFile->addWarning(
					'Docblock summary starts with "%s" — rewrite as what the element does (third-person singular per WordPress docs)',
					$first,
					'AntiPattern',
					[ $bad ],
				);
				return;
			}
		}

		foreach ( $this->bareInfinitiveClosers as $verb ) {
			if ( strcasecmp( $firstWord, $verb ) === 0 ) {
				$phpcsFile->addWarning(
					'Docblock summary first word "%s" is a bare infinitive — use the third-person singular form (e.g. Processes, Passes, Fixes)',
					$first,
					'BareInfinitive',
					[ $firstWord ],
				);
				return;
			}
		}

		if ( substr( strtolower( $firstWord ), -1 ) !== 's' ) {
			$phpcsFile->addWarning(
				'Docblock summary first word "%s" does not end in "s" — use third-person singular per WordPress docs (mental test: prepending "It" must read grammatically)',
				$first,
				'NotThirdPerson',
				[ $firstWord ],
			);
		}
	}

	/**
	 * Reports whether the docblock attaches to a property, constant, or bare variable.
	 *
	 * Scans forward from the docblock's closing tag and returns true if the
	 * first attachable declaration encountered is a property / constant /
	 * variable (noun-form summaries are idiomatic), false if it is a function,
	 * class, interface, trait, or enum (third-person rule applies).
	 *
	 * @param File $phpcsFile    The file being scanned.
	 * @param int  $commentClose Position of the docblock's T_DOC_COMMENT_CLOSE_TAG.
	 *
	 * @return bool
	 */
	private function describesDataDeclaration( File $phpcsFile, int $commentClose ): bool {
		$tokens = $phpcsFile->getTokens();
		$end    = $phpcsFile->numTokens;

		for ( $i = $commentClose + 1; $i < $end; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( $code === T_FUNCTION
				|| $code === T_CLASS
				|| $code === T_INTERFACE
				|| $code === T_TRAIT
				|| ( defined( 'T_ENUM' ) && $code === T_ENUM )
			) {
				return false;
			}

			if ( $code === T_VARIABLE || $code === T_CONST ) {
				return true;
			}

			if ( $code === T_DOC_COMMENT_OPEN_TAG ) {
				return false;
			}
		}

		return false;
	}

	/**
	 * Strips a leading backtick-quoted code reference and leading punctuation.
	 *
	 * @param string $summary Raw summary content.
	 *
	 * @return string Cleaned summary.
	 */
	private function cleanSummary( string $summary ): string {
		$cleaned = preg_replace( '/^`[^`]*`\s*/', '', $summary ) ?? $summary;
		return ltrim( $cleaned, " \t\"'*([{" );
	}
}
