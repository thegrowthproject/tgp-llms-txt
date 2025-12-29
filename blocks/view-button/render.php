<?php
/**
 * Server-side rendering for View as Markdown button block.
 *
 * @package TGP_LLMs_Txt
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

// Get current post.
global $post;
if ( ! $post ) {
	return '';
}

// Get attributes with defaults.
$label     = $attributes['label'] ?? __( 'View as Markdown', 'tgp-llms-txt' );
$show_icon = $attributes['showIcon'] ?? true;
$width     = $attributes['width'] ?? null;

// Build markdown URL.
$permalink = get_permalink( $post );
$md_url    = rtrim( $permalink, '/' ) . '.md';

// View/document icon SVG.
$view_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';

// Allowed SVG tags for wp_kses.
$allowed_svg = [
	'svg'      => [
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
	'path'     => [
		'd' => true,
	],
	'polyline' => [
		'points' => true,
	],
	'line'     => [
		'x1' => true,
		'y1' => true,
		'x2' => true,
		'y2' => true,
	],
];

// Build wrapper classes.
$wrapper_classes = [ 'wp-block-button' ];
if ( $width ) {
	$wrapper_classes[] = 'has-custom-width';
	$wrapper_classes[] = 'wp-block-button__width-' . $width;
}

// Get block wrapper attributes (includes Block Supports styles automatically).
$button_classes     = 'wp-block-button__link wp-element-button tgp-view-btn';
$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class' => $button_classes,
	]
);
?>
<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
	<a
		href="<?php echo esc_url( $md_url ); ?>"
		target="_blank"
		rel="noopener noreferrer"
		<?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		title="<?php esc_attr_e( 'View this content as plain markdown', 'tgp-llms-txt' ); ?>"
	>
		<?php if ( $show_icon ) : ?>
			<span class="tgp-btn-icon"><?php echo wp_kses( $view_icon, $allowed_svg ); ?></span>
		<?php endif; ?>
		<span class="tgp-btn-text"><?php echo esc_html( $label ); ?></span>
	</a>
</div>
