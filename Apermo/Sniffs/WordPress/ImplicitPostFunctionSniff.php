<?php
/**
 * Flag WordPress functions that implicitly access global $post.
 *
 * @package Apermo\Sniffs\WordPress
 */

namespace Apermo\Sniffs\WordPress;

use PHP_CodeSniffer\Files\File;

/**
 * Flags WordPress template functions called without an explicit post
 * argument inside function, method, closure, and arrow function scopes.
 *
 * Top-level (template) code is not flagged — the WordPress loop sets
 * global $post there. Inside custom functions, these calls create hidden
 * dependencies on global state.
 */
class ImplicitPostFunctionSniff extends AbstractPostContextSniff {

	/**
	 * WordPress functions that read global $post when called without
	 * the appropriate argument.
	 *
	 * Structure: function_name => [
	 *     'param_position' => int|null,  // 1-based, null = no post param exists
	 *     'severity'       => 'error'|'warning',
	 *     'code'           => 'ErrorCodeName',
	 * ]
	 *
	 * @var array<string, array{param_position: int|null, severity: string, code: string}>
	 */
	private const POST_FUNCTIONS = [
		// Error tier: direct global state access.
		'get_post'                          => [ 'param_position' => 1, 'severity' => 'error', 'code' => 'DirectAccess' ],
		'get_the_id'                        => [ 'param_position' => null, 'severity' => 'error', 'code' => 'DirectAccess' ],
		'the_id'                            => [ 'param_position' => null, 'severity' => 'error', 'code' => 'DirectAccess' ],

		// Warning tier: optional post parameter available.
		'get_the_title'                     => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'get_the_excerpt'                   => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'get_permalink'                     => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'get_the_permalink'                 => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'the_permalink'                     => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'get_post_permalink'                => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'has_post_thumbnail'                => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'get_post_thumbnail_id'             => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'get_the_post_thumbnail'            => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'get_the_post_thumbnail_url'        => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'get_the_post_thumbnail_caption'    => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'the_post_thumbnail_caption'        => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'get_edit_post_link'                => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'get_delete_post_link'              => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'get_preview_post_link'             => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'has_excerpt'                       => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'post_password_required'            => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'get_the_password_form'             => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'get_page_template_slug'            => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'get_post_parent'                   => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'has_post_parent'                   => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'get_the_guid'                      => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'the_guid'                          => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'wp_force_plain_post_permalink'     => [ 'param_position' => 1, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'get_the_content'                   => [ 'param_position' => 3, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'edit_post_link'                    => [ 'param_position' => 4, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'post_class'                        => [ 'param_position' => 2, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],
		'get_post_class'                    => [ 'param_position' => 2, 'severity' => 'warning', 'code' => 'MissingPostParameter' ],

		// Warning tier: no post parameter exists at all.
		'the_title'                         => [ 'param_position' => null, 'severity' => 'warning', 'code' => 'NoPostParameter' ],
		'the_content'                       => [ 'param_position' => null, 'severity' => 'warning', 'code' => 'NoPostParameter' ],
		'the_excerpt'                       => [ 'param_position' => null, 'severity' => 'warning', 'code' => 'NoPostParameter' ],
		'the_post_thumbnail'                => [ 'param_position' => null, 'severity' => 'warning', 'code' => 'NoPostParameter' ],
		'the_post_thumbnail_url'            => [ 'param_position' => null, 'severity' => 'warning', 'code' => 'NoPostParameter' ],
		'post_custom'                       => [ 'param_position' => null, 'severity' => 'warning', 'code' => 'NoPostParameter' ],
		'permalink_anchor'                  => [ 'param_position' => null, 'severity' => 'warning', 'code' => 'NoPostParameter' ],
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

		$config        = self::POST_FUNCTIONS[ $funcName ];
		$paramPosition = $config['param_position'];

		// No post parameter exists, or direct access functions with no param: always flag.
		if ( $paramPosition === null ) {
			$this->report( $phpcsFile, $stackPtr, $tokens[ $stackPtr ]['content'], $config );
			return;
		}

		// Post parameter exists: flag only if not enough arguments provided.
		$argCount = $this->countArguments( $phpcsFile, $stackPtr );
		if ( $argCount < $paramPosition ) {
			$this->report( $phpcsFile, $stackPtr, $tokens[ $stackPtr ]['content'], $config );
		}
	}

	/**
	 * Reports an error or warning.
	 *
	 * @param File   $phpcsFile The file being scanned.
	 * @param int    $stackPtr  The position of the token.
	 * @param string $funcName  The original function name (preserving case).
	 * @param array  $config    The function configuration from POST_FUNCTIONS.
	 *
	 * @return void
	 */
	private function report( File $phpcsFile, int $stackPtr, string $funcName, array $config ): void {
		$message = 'Function %s() called without explicit post context; pass WP_Post or post ID';

		if ( $config['severity'] === 'error' ) {
			$phpcsFile->addError( $message, $stackPtr, $config['code'], [ $funcName ] );
		} else {
			$phpcsFile->addWarning( $message, $stackPtr, $config['code'], [ $funcName ] );
		}
	}
}
