<?php
/**
 * PHP file to use when rendering the block type on the server to show on the front end.
 *
 * The following variables are exposed to the file:
 *     $attributes (array): The block attributes.
 *     $content (string): The block default content.
 *     $block (WP_Block): The block instance.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 * @package query-taxonomy-filters
 */

$label            = $attributes['label'];
$selected_field   = $attributes['selectedField'];
$identifier       = 'query-' . $block->context['queryId'] . '-cf-' . $attributes['instanceId'];
$input_type       = sanitize_text_field( $attributes['inputType'] );
$accessible_label = ! empty( $attributes['accessibleLabel'] ) ? sanitize_text_field( $attributes['accessibleLabel'] ) : '';

if ( ! empty( $accessible_label ) ) {
	$computed_label = $accessible_label;
} else {
	$computed_label = 'Filter by ' . ucwords( str_replace( '_', ' ', $selected_field ) );
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( isset( $_GET[ $identifier ] ) && ! empty( $_GET[ $identifier ] ) ) {
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification.Recommended
	$selected_custom_field_raw = explode( ',', wp_unslash( trim( $_GET[ $identifier ] ) ) );
	$selected_custom_field_raw = array_map( 'sanitize_text_field', $selected_custom_field_raw );

	// For select input type, use single value (first item or empty string).
	// For checkboxes, use array.
	if ( 'select' === $input_type ) {
		$selected_custom_field = ! empty( $selected_custom_field_raw ) ? $selected_custom_field_raw[0] : '';
	} else {
		$selected_custom_field = $selected_custom_field_raw;
	}
} elseif ( 'select' === $input_type ) {
	// For select input type, use empty string.
	$selected_custom_field = '';
} else {
	// For checkboxes, use empty array.
	$selected_custom_field = array();
}

$context = array(
	'selected' => $selected_custom_field,
);

global $wpdb;

$custom_field_values = $wpdb->get_col(
	$wpdb->prepare(
		"SELECT DISTINCT meta_value
		FROM $wpdb->postmeta
		WHERE meta_key = %s",
		$selected_field
	)
);

?>
<div
	<?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> 
	data-wp-interactive="ctlt-query-custom-field-filter"
	data-wp-watch="callbacks.navigateToDestination"
	filter-id="<?php echo esc_attr( $attributes['instanceId'] ); ?>"
	<?php echo wp_interactivity_data_wp_context( $context ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> 
>
	<div class="live-region screen-reader-text" aria-live="polite" aria-atomic="true"></div>
	<?php if ( 'select' === $input_type ) : ?>
		<label for="<?php echo esc_attr( $identifier ); ?>" class="screen-reader-text"><?php echo esc_html( $computed_label ); ?></label>
		<select
			data-wp-on--change="actions.onChangeField"
			data-wp-bind--value="context.selected"
			class="wp-query-filter__select"
			id="<?php echo esc_attr( $identifier ); ?>"
			aria-label="<?php echo esc_attr( $computed_label ); ?>"
	>
		<option value=""><?php echo esc_html( $label ); ?></option>
		<?php foreach ( $custom_field_values as $key => $custom_field_value ) : ?>
			<option
				value="<?php echo esc_attr( $custom_field_value ); ?>"
				<?php selected( $selected_custom_field, $custom_field_value ); ?>
			>
				<?php echo esc_html( $custom_field_value ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php else : ?>
		<div class="wp-query-filter__checkboxes">
			<fieldset>
				<legend class="wp-query-filter__legend screen-reader-text"><?php echo esc_html( $computed_label ); ?></legend>
			<?php foreach ( $custom_field_values as $key => $custom_field_value ) : ?>
					<label for="<?php echo esc_attr( $identifier . '-checkbox-' . $custom_field_value ); ?>">
						<input
							type="checkbox"
							id="<?php echo esc_attr( $identifier . '-checkbox-' . $custom_field_value ); ?>"
							aria-label="<?php echo esc_attr( $custom_field_value ); ?>"
							name="<?php echo esc_attr( 'query-' . $attributes['instanceId'] . '-custom-field[]' ); ?>"
							value="<?php echo esc_attr( $custom_field_value ); ?>"
							data-wp-on--change="actions.onChangeField"
							class="wp-query-filter__checkbox"
							<?php checked( in_array( $custom_field_value, $selected_custom_field, true ) ); ?>
						/>
						<?php echo esc_html( $custom_field_value ); ?>
					</label>
				<?php endforeach; ?>
			</fieldset>
		</div>
	<?php endif; ?>
</div>
