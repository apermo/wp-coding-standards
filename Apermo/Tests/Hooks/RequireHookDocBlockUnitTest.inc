<?php

/**
 * Fires after a post is saved.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 */
do_action( 'my_after_save', $post_id, $post );

/**
 * Filters the post title.
 *
 * @param string $title The post title.
 *
 * @return string Filtered title.
 */
apply_filters( 'my_title_filter', $title );

// Warning Missing: no doc block.
do_action( 'my_undocumented_hook', $data );

// Warning Missing: regular comment is not a doc block.
// This is a hook.
apply_filters( 'my_no_docblock_filter', $value );

/**
 * Warning MissingParam: doc block present but no @param tags.
 */
do_action( 'my_missing_params_hook', $foo, $bar );

/**
 * Warning MissingReturn: filter without @return tag.
 *
 * @param string $value The value.
 */
apply_filters( 'my_missing_return_filter', $value );

/**
 * OK: action with no extra args beyond hook name.
 */
do_action( 'my_simple_action' );

/**
 * OK: filter with only hook name + value, has @return.
 *
 * @param string $value The value.
 *
 * @return string
 */
apply_filters( 'my_simple_filter', $value );

// Not a function call — method call, should be skipped.
$obj->do_action( 'not_a_hook' );
SomeClass::apply_filters( 'not_a_hook' );

/**
 * OK: _ref_array variant with @param and no extra args issue.
 *
 * @param array $args Hook arguments.
 *
 * @return array
 */
apply_filters_ref_array( 'my_ref_filter', $args );

/**
 * OK: deprecated action with proper docs.
 *
 * @param int $post_id Post ID.
 */
do_action_deprecated( 'my_old_action', [ $post_id ], '2.0.0', 'my_new_action' );

// Warning Missing: deprecated filter without doc block.
apply_filters_deprecated( 'my_old_filter', [ $value ], '2.0.0', 'my_new_filter' );

/**
 * OK: action with no extra arguments needs no @param.
 */
do_action_ref_array( 'my_ref_action', [] );

/**
 * Warning MissingParam + MissingReturn: ref_array filter with args but no @param/@return.
 */
apply_filters_ref_array( 'my_ref_filter_bad', $args );

/**
 * OK: deprecated action with no extra args (only name, version, message).
 */
do_action_deprecated( 'my_old_simple', [], '2.0.0', 'my_new_simple' );
