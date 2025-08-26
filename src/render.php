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

$label                 = $attributes['label'];
$selected_field        = $attributes['selectedField'];
$identifier            = 'query-' . $block->context['queryId'] . '-cf-' . $attributes['instanceId'];
$selected_custom_field = isset( $_GET[ $identifier ] ) && ! empty( $_GET[ $identifier ] ) ? sanitize_text_field( wp_unslash( $_GET[ $identifier ] ) ) : '';
$input_type            = sanitize_text_field( $attributes['inputType'] );

if ( isset( $_GET[ $identifier ] ) && ! empty( $_GET[ $identifier ] ) ) {
	$selected_custom_field = explode( ',', wp_unslash( trim( $_GET[ $identifier ] ) ) );
} else {
	$selected_custom_field = array();
}

$selected_custom_field = array_map( 'sanitize_text_field', $selected_custom_field );

$conext = array(
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
	<?php echo wp_interactivity_data_wp_context( $conext ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> 
>
	<?php if ( 'select' === $input_type ) : ?>
		<select
			data-wp-on--change="actions.onChangeField"
			data-wp-bind--value="context.selected"
			class="wp-query-filter__select"
	>
		<option value=""><?php echo esc_html( $label ); ?></option>
		<?php foreach ( $custom_field_values as $key => $custom_field_value ) : ?>
			<option
				value="<?php echo esc_attr( $custom_field_value ); ?>"
			>
				<?php echo esc_html( $custom_field_value ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php else : ?>
		<div class="wp-query-filter__checkboxes">
			<fieldset>
				<legend class="wp-query-filter__legend visually-hidden">Custom Field Filter, available values in below list.</legend>
			<?php foreach ( $custom_field_values as $key => $custom_field_value ) : ?>
					<label>
						<input
							type="checkbox"
							name="<?php echo esc_attr( 'query-' . $attributes['instanceId'] . '-custom-field[]' ); ?>"
							value="<?php echo esc_attr( $custom_field_value ); ?>"
							data-wp-on--change="actions.onChangeField"
							class="wp-query-filter__checkbox"
							<?php checked( in_array( $custom_field_value, $selected_custom_field ) ); ?>
						/>
						<?php echo esc_html( $custom_field_value ); ?>
					</label>
				<?php endforeach; ?>
			</fieldset>
		</div>
	<?php endif; ?>
</div>
