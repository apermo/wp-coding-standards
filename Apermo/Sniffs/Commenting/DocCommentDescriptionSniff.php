<?php
declare(strict_types=1);

/**
 * Requires a short description in doc comments, with exceptions.
 *
 * @package Apermo\Sniffs\Commenting
 */

namespace Apermo\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Replaces Generic.Commenting.DocComment.MissingShort with
 * exceptions for `@see` and phpstan-ignore doc comments.
 *
 * A doc comment starting with `@see` is treated as a reference
 * (e.g. pointing to where a WP hook is documented). A doc
 * comment starting with a PHPStan ignore directive is a tool
 * annotation, not a description.
 */
class DocCommentDescriptionSniff implements Sniff {

	/**
	 * Tags that satisfy the short description requirement.
	 *
	 * @var array<string>
	 */
	private const ALLOWED_TAGS = [
		'@see',
		'@phpstan-ignore',
		'@phpstan-ignore-next-line',
	];

	/**
	 * Returns an array of tokens this sniff listens for.
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

		$empty = [
			T_DOC_COMMENT_WHITESPACE,
			T_DOC_COMMENT_STAR,
		];

		$short = $phpcsFile->findNext( $empty, ( $stackPtr + 1 ), $commentEnd, true );

		if ( $short === false ) {
			return;
		}

		if ( $tokens[ $short ]['code'] === T_DOC_COMMENT_STRING ) {
			return;
		}

		if ( $tokens[ $short ]['code'] === T_DOC_COMMENT_TAG
			&& \in_array( $tokens[ $short ]['content'], self::ALLOWED_TAGS, true )
		) {
			return;
		}

		$phpcsFile->addError(
			'Missing short description in doc comment',
			$stackPtr,
			'MissingShort',
		);
	}
}
