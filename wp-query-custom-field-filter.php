<?php
/**
 * Plugin Name:       WP Query Block Extension - Frontend Custom Field Filter
 * Description:       Add custom field filter in the frontend page that filters the posts returned from the query block.
 * Version:           0.1.1
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Author:            CTLT WordPress
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       query-custom-field-filter
 *
 * @package           query-custom-field-filter
 */

namespace UBC\CTLT\BLOCKS\QUERY_BLOCK\FILTERS\CUSTOMFIELD;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'init', __NAMESPACE__ . '\\init' );
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\localize_to_editor_script' );
add_filter( 'pre_render_block', __NAMESPACE__ . '\\pre_render_block', 10, 2 );
add_action( 'wp_ajax_query_filter_get_meta_keys', __NAMESPACE__ . '\\get_meta_keys' );
add_action( 'updated_post_meta', __NAMESPACE__ . '\\reset_metakeys_transient' );

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function init() {
	register_block_type_from_metadata( __DIR__ . '/build' );
}

/**
 * Localize PHP variables to editor script.
 */
function localize_to_editor_script() {
	wp_localize_script(
		'ctlt-query-custom-field-filter-editor-script',
		'query_custom_field_filter',
		array(
			'nonce' => wp_create_nonce( 'query_custom_field_filter_ajax' ),
		)
	);
}

/**
 * Recursive function to retrieves all inner blocks of a given block with a specific inner block name.
 *
 * @param array  $block The block to search for inner blocks.
 * @param string $inner_block_name The name of the inner block to search for.
 * @return array An array of inner blocks with the specified name.
 */
function get_inner_blocks( $block, $inner_block_name ) {
	if ( ! array_key_exists( 'innerBlocks', $block ) || ! is_array( $block['innerBlocks'] ) ) {
		return array();
	}

	$inner_blocks = array();
	foreach ( $block['innerBlocks'] as $key => $inner_block ) {
		if ( $inner_block_name === $inner_block['blockName'] ) {
			array_push( $inner_blocks, $inner_block );
		} else {
			$inner_blocks = array_merge( $inner_blocks, get_inner_blocks( $inner_block, $inner_block_name ) );
		}
	}

	return $inner_blocks;
}

/**
 * Inject new tax query from the filter based on query ID.
 *
 * @param string|null $pre_render The pre-rendered content. Default null.
 * @param array       $parsed_block The block being rendered.
 *
 * @return string|null The modified pre-rendered block content or the original pre-rendered content if the block name is not 'core/query'.
 */
function pre_render_block( $pre_render, $parsed_block ) {
	if ( 'core/query' !== $parsed_block['blockName'] ) {
		return $pre_render;
	}

	$is_interactive = isset( $parsed_block['attrs']['enhancedPagination'] )
		&& true === $parsed_block['attrs']['enhancedPagination']
		&& isset( $parsed_block['attrs']['queryId'] );

	if ( ! $is_interactive ) {
		return $pre_render;
	}

	// Loop through innerblocks recursively to get all the custom field filters.
	$inner_cf_blocks = get_inner_blocks( $parsed_block, 'ctlt/query-custom-field-filter' );

	// Creating a hash map. Key is the filter ID and value is the taxonomy type.
	$hash_map = array();
	foreach ( $inner_cf_blocks as $key => $inner_block ) {
		if ( array_key_exists( 'attrs', $inner_block ) &&
			array_key_exists( 'instanceId', $inner_block['attrs'] ) &&
			array_key_exists( 'selectedField', $inner_block['attrs'] )
		) {
			$hash_map[ $inner_block['attrs']['instanceId'] ] = $inner_block['attrs']['selectedField'];
		}
	}

	add_filter(
		'query_loop_block_query_vars',
		function ( $query, $block ) use ( $hash_map ) {
			$query_id   = $block->context['queryId'];
			$identifier = 'query-' . $query_id . '-cf-';

			if ( ! array_key_exists( 'meta_query', $query ) ) {
				$query['meta_query'] = array();
			}

			foreach ( $_GET as $key => $value ) {
				if ( preg_match( '/^' . $identifier . '(?<instance_id>\d+)$/', $key, $matches ) && ! empty( $value ) && array_key_exists( $matches['instance_id'], $hash_map ) ) {
					$custom_fields = explode( ',', $value );

					$new_cf_query = array(
						'key'     => $hash_map[ absint( $matches['instance_id'] ) ],
						'value'   => $custom_fields,
						'compare' => 'IN',
					);

					array_push( $query['meta_query'], $new_cf_query );
				}
			}

			return $query;
		},
		10,
		2
	);

	return $pre_render;
}

/**
 * Ajax request handler to return the list of meta keys from the post meta table.
 *
 * @return void
 */
function get_meta_keys() {
	global $wpdb;

	wp_verify_nonce( $_POST['nonce'], 'query_custom_field_filter_ajax' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.InputNotValidated

	$keys = get_transient( 'wp_metadata_get_keys' );
	if ( false !== $keys ) {
		wp_send_json_success( $keys );
	}

	$keys = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT meta_key
			FROM $wpdb->postmeta
			WHERE meta_key NOT BETWEEN '_' AND '_z'
			HAVING meta_key NOT LIKE %s
			ORDER BY meta_key",
			$wpdb->esc_like( '_' ) . '%'
		)
	);

	set_transient( 'wp_metadata_get_keys', $keys, HOUR_IN_SECONDS );

	wp_send_json_success( $keys );
}//end get_meta_keys()

/**
 * Delete `wp_metadata_filter_get_keys` transient when any of the post metas is updated.
 */
function reset_metakeys_transient() {
	if ( false !== get_transient( 'wp_metadata_get_keys' ) ) {
		delete_transient( 'wp_metadata_get_keys' );
	}
}//end reset_metakeys_transient()
