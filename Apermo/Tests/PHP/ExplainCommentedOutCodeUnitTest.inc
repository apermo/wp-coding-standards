<?php
// Normal prose comment about the application logic.
$a = 1;

/** Disabled: Plugin doesn't support PHP 8.3 yet. */
// add_action( 'init', 'my_func' );
// add_filter( 'the_content', 'my_filter' );

/**
 * Kept (2026-02-14): Needed for rollback if migration fails.
 * See https://github.com/org/repo/issues/42
 */
// $rollback = new RollbackHandler( $args );
// $rollback->register();

/** Debug: Logging for intermittent cron failure. */
// error_log( 'Cron heartbeat: ' . time() );

/** Review: Found during refactor, unclear if still needed. */
// register_post_type( 'legacy_type', $args );

/** WIP: Partially migrated to new API. */
// $client = new OldApiClient( $config );

/** Disabled (2026-01-15): Waiting for upstream fix. */

// $gap_code = call_function( $param );

// $no_explanation = new DateTime();
// $no_explanation->modify( '+1 day' );

/** Disabled: Two-line gap below is too far. */


// $too_far_code = call_function( $args );
// $too_far_also = get_results( $data );

// $preceding_is_code = get_value( $input );
// $more_code_here = do_thing( $preceding_is_code );

/* Disabled: Wrong comment style, not doc-block. */
// $wrong_style = array_merge( $a, $b );

// Disabled: Wrong comment style, // instead of /** */.

// $wrong_style_inline = get_option( 'name' );

/** No keyword, just a description. */
// $missing_keyword = get_value( $input );

# $hash_code = array_merge( $a, $b );

/* $block_comment_code = array_merge( $a, $b ); */

// Here is some inline prose.
$b = 2;

// $after_real_code = get_value( $input );
// $more_orphan = process_data( $input );
