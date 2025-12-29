<?php
/**
 * Server-side rendering for Copy Button block.
 *
 * Renders a button that copies the current page content as markdown.
 *
 * @package TGP_LLMs_Txt
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

global $post;
if ( ! $post ) {
	return '';
}

// Get attributes with defaults.
$label     = $attributes['label'] ?? __( 'Copy for LLM', 'tgp-llms-txt' );
$show_icon = $attributes['showIcon'] ?? true;
$width     = $attributes['width'] ?? null;

// Copy icon SVG.
$copy_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>';

// Allowed SVG tags for wp_kses.
$allowed_svg = [
	'svg'    => [
		'xmlns'           => true,
		'width'           => true,
		'height'          => true,
		'viewbox'         => true,
		'fill'            => true,
		'stroke'          => true,
		'stroke-width'    => true,
		'stroke-linecap'  => true,
		'stroke-linejoin' => true,
	],
	'rect'   => [
		'x'      => true,
		'y'      => true,
		'width'  => true,
		'height' => true,
		'rx'     => true,
		'ry'     => true,
	],
	'path'   => [
		'd' => true,
	],
	'circle' => [
		'cx' => true,
		'cy' => true,
		'r'  => true,
	],
];

// Build wrapper classes (outer div gets block styles like is-style-outline).
$wrapper_classes = [ 'wp-block-button' ];
if ( $width ) {
	$wrapper_classes[] = 'has-custom-width';
	$wrapper_classes[] = 'wp-block-button__width-' . $width;
}

// Get block wrapper attributes for the outer div (includes is-style-* classes).
$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class' => implode( ' ', $wrapper_classes ),
	]
);

// Inner button classes.
$button_classes = 'wp-block-button__link wp-element-button tgp-copy-btn';
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<button
		type="button"
		class="<?php echo esc_attr( $button_classes ); ?>"
		data-post-id="<?php echo esc_attr( $post->ID ); ?>"
		title="<?php esc_attr_e( 'Copy this content in markdown format for AI assistants', 'tgp-llms-txt' ); ?>"
	>
		<?php if ( $show_icon ) : ?>
			<span class="tgp-btn-icon"><?php echo wp_kses( $copy_icon, $allowed_svg ); ?></span>
		<?php endif; ?>
		<span class="tgp-btn-text"><?php echo esc_html( $label ); ?></span>
	</button>
</div>
