<?php
/**
 * Flag WordPress functions that implicitly access global $post.
 *
 * @package Apermo\Sniffs\WordPress
 */

namespace Apermo\Sniffs\WordPress;

use PHP_CodeSniffer\Files\File;
use PHPCSUtils\Utils\PassedParameters;

/**
 * Flags WordPress template functions called without an explicit post
 * argument inside function, method, closure, and arrow function scopes.
 *
 * Top-level (template) code is not flagged — the WordPress loop sets
 * global $post there. Inside custom functions, these calls create hidden
 * dependencies on global state.
 *
 * Error codes (configurable via PHPCS rule overrides):
 * - MissingArgument: post param exists but no argument provided (error)
 * - NullArgument:    literal null passed as post argument (error)
 * - IntegerArgument: literal int or $var->ID passed (warning)
 * - NoPostParameter: function has no post parameter at all (error)
 */
class ImplicitPostFunctionSniff extends AbstractPostContextSniff {

	/**
	 * WordPress functions that read global $post when called without
	 * the appropriate argument.
	 *
	 * Structure: function_name => [
	 *     'position' => int|null,  // 1-based param position, null = no post param
	 *     'name'     => string,    // PHP parameter name (for named arg lookup)
	 * ]
	 *
	 * @var array<string, array{position: int|null, name: string}>
	 */
	private const POST_FUNCTIONS = [
		// Functions with a $post parameter.
		'get_post'                          => [ 'position' => 1, 'name' => 'post' ],
		'get_the_title'                     => [ 'position' => 1, 'name' => 'post' ],
		'get_the_excerpt'                   => [ 'position' => 1, 'name' => 'post' ],
		'get_permalink'                     => [ 'position' => 1, 'name' => 'post' ],
		'get_the_permalink'                 => [ 'position' => 1, 'name' => 'post' ],
		'the_permalink'                     => [ 'position' => 1, 'name' => 'post' ],
		'get_post_permalink'                => [ 'position' => 1, 'name' => 'post' ],
		'has_post_thumbnail'                => [ 'position' => 1, 'name' => 'post' ],
		'get_post_thumbnail_id'             => [ 'position' => 1, 'name' => 'post' ],
		'get_the_post_thumbnail'            => [ 'position' => 1, 'name' => 'post' ],
		'get_the_post_thumbnail_url'        => [ 'position' => 1, 'name' => 'post' ],
		'get_the_post_thumbnail_caption'    => [ 'position' => 1, 'name' => 'post' ],
		'the_post_thumbnail_caption'        => [ 'position' => 1, 'name' => 'post' ],
		'get_edit_post_link'                => [ 'position' => 1, 'name' => 'post' ],
		'get_delete_post_link'              => [ 'position' => 1, 'name' => 'post' ],
		'get_preview_post_link'             => [ 'position' => 1, 'name' => 'post' ],
		'has_excerpt'                       => [ 'position' => 1, 'name' => 'post' ],
		'post_password_required'            => [ 'position' => 1, 'name' => 'post' ],
		'get_the_password_form'             => [ 'position' => 1, 'name' => 'post' ],
		'get_page_template_slug'            => [ 'position' => 1, 'name' => 'post' ],
		'get_post_parent'                   => [ 'position' => 1, 'name' => 'post' ],
		'has_post_parent'                   => [ 'position' => 1, 'name' => 'post' ],
		'get_the_guid'                      => [ 'position' => 1, 'name' => 'post' ],
		'the_guid'                          => [ 'position' => 1, 'name' => 'post' ],
		'wp_force_plain_post_permalink'     => [ 'position' => 1, 'name' => 'post' ],
		'get_post_datetime'                 => [ 'position' => 1, 'name' => 'post' ],
		'get_post_timestamp'                => [ 'position' => 1, 'name' => 'post' ],
		'get_the_content'                   => [ 'position' => 3, 'name' => 'post' ],
		'edit_post_link'                    => [ 'position' => 4, 'name' => 'post' ],
		'post_class'                        => [ 'position' => 2, 'name' => 'post' ],
		'get_post_class'                    => [ 'position' => 2, 'name' => 'post' ],
		'get_the_date'                      => [ 'position' => 2, 'name' => 'post' ],
		'get_the_modified_date'             => [ 'position' => 2, 'name' => 'post' ],
		'get_the_time'                      => [ 'position' => 2, 'name' => 'post' ],
		'get_the_modified_time'             => [ 'position' => 2, 'name' => 'post' ],
		'get_post_time'                     => [ 'position' => 3, 'name' => 'post' ],
		'get_post_modified_time'            => [ 'position' => 3, 'name' => 'post' ],

		// Functions without a $post parameter (position = null).
		'get_the_id'                        => [ 'position' => null, 'name' => '' ],
		'the_id'                            => [ 'position' => null, 'name' => '' ],
		'the_title'                         => [ 'position' => null, 'name' => '' ],
		'the_content'                       => [ 'position' => null, 'name' => '' ],
		'the_excerpt'                       => [ 'position' => null, 'name' => '' ],
		'the_post_thumbnail'                => [ 'position' => null, 'name' => '' ],
		'the_post_thumbnail_url'            => [ 'position' => null, 'name' => '' ],
		'post_custom'                       => [ 'position' => null, 'name' => '' ],
		'permalink_anchor'                  => [ 'position' => null, 'name' => '' ],
		'the_date'                          => [ 'position' => null, 'name' => '' ],
		'the_time'                          => [ 'position' => null, 'name' => '' ],
		'the_modified_date'                 => [ 'position' => null, 'name' => '' ],
		'the_modified_time'                 => [ 'position' => null, 'name' => '' ],
	];

	/**
	 * Getter alternatives for functions without a post parameter.
	 *
	 * Maps function names to their getter variant that accepts a WP_Post argument.
	 *
	 * @var array<string, string>
	 */
	private const GETTER_ALTERNATIVES = [
		'the_title'              => 'get_the_title',
		'the_content'            => 'get_the_content',
		'the_excerpt'            => 'get_the_excerpt',
		'the_post_thumbnail'     => 'get_the_post_thumbnail',
		'the_post_thumbnail_url' => 'get_the_post_thumbnail_url',
		'the_date'               => 'get_the_date',
		'the_time'               => 'get_the_time',
		'the_modified_date'      => 'get_the_modified_date',
		'the_modified_time'      => 'get_the_modified_time',
	];

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
		if ( ! $this->isInsideFunctionScope( $phpcsFile, $stackPtr ) ) {
			return;
		}

		$tokens   = $phpcsFile->getTokens();
		$funcName = strtolower( $tokens[ $stackPtr ]['content'] );

		if ( ! isset( self::POST_FUNCTIONS[ $funcName ] ) ) {
			return;
		}

		if ( ! $this->isFunctionCall( $phpcsFile, $stackPtr ) ) {
			return;
		}

		$config   = self::POST_FUNCTIONS[ $funcName ];
		$origName = $tokens[ $stackPtr ]['content'];

		// No post parameter exists at all.
		if ( $config['position'] === null ) {
			$this->reportNoPostParameter( $phpcsFile, $stackPtr, $origName, $funcName );
			return;
		}

		// Check if the post argument was provided.
		$params    = PassedParameters::getParameters( $phpcsFile, $stackPtr );
		$postParam = PassedParameters::getParameterFromStack( $params, $config['position'], $config['name'] );

		if ( $postParam === false ) {
			$phpcsFile->addError(
				'Function %s() called without explicit post argument; pass WP_Post',
				$stackPtr,
				'MissingArgument',
				[ $origName ]
			);
			return;
		}

		// Classify what was passed.
		$argType = $this->classifyArgument( $phpcsFile, $postParam );

		if ( $argType === 'null' ) {
			$phpcsFile->addError(
				'Function %s() called with null; pass WP_Post instead',
				$stackPtr,
				'NullArgument',
				[ $origName ]
			);
		} elseif ( $argType === 'integer' ) {
			$phpcsFile->addWarning(
				'Function %s() called with integer post ID; pass WP_Post instead',
				$stackPtr,
				'IntegerArgument',
				[ $origName ]
			);
		}
	}

	/**
	 * Classifies a passed argument as null, integer, or ok.
	 *
	 * @param File  $phpcsFile The file being scanned.
	 * @param array $param     Parameter info from PassedParameters.
	 *
	 * @return string 'null'|'integer'|'ok'
	 */
	private function classifyArgument( File $phpcsFile, array $param ): string {
		$tokens = $phpcsFile->getTokens();

		// Collect non-whitespace tokens in the argument.
		$significant = [];
		for ( $i = $param['start']; $i <= $param['end']; $i++ ) {
			if ( $tokens[ $i ]['code'] !== T_WHITESPACE ) {
				$significant[] = $tokens[ $i ];
			}
		}

		// Single token: check for null or integer literal.
		if ( count( $significant ) === 1 ) {
			if ( $significant[0]['code'] === T_NULL ) {
				return 'null';
			}
			if ( $significant[0]['code'] === T_LNUMBER ) {
				return 'integer';
			}
		}

		// Three tokens: $var->ID pattern.
		if ( count( $significant ) === 3
			&& $significant[0]['code'] === T_VARIABLE
			&& $significant[1]['code'] === T_OBJECT_OPERATOR
			&& $significant[2]['code'] === T_STRING
			&& $significant[2]['content'] === 'ID'
		) {
			return 'integer';
		}

		return 'ok';
	}

	/**
	 * Reports a NoPostParameter error with getter suggestion when available.
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  The position of the token.
	 * @param string $origName  The original function name (preserving case).
	 * @param string $funcName  The lowercased function name.
	 *
	 * @return void
	 */
	private function reportNoPostParameter( File $phpcsFile, int $stackPtr, string $origName, string $funcName ): void {
		if ( isset( self::GETTER_ALTERNATIVES[ $funcName ] ) ) {
			$phpcsFile->addError(
				'Function %s() relies on global post context; use %s() with explicit WP_Post instead',
				$stackPtr,
				'NoPostParameter',
				[ $origName, self::GETTER_ALTERNATIVES[ $funcName ] ]
			);
		} else {
			$phpcsFile->addError(
				'Function %s() relies on global post context and has no post parameter',
				$stackPtr,
				'NoPostParameter',
				[ $origName ]
			);
		}
	}
}
