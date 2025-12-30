<?php
/**
 * Plugin Name: TGP LLMs.txt
 * Plugin URI: https://thegrowthproject.com.au
 * Description: Provides markdown endpoints for AI/LLM consumption. Adds .md URLs, /llms.txt index, and "Copy for LLM" buttons.
 * Version: 1.2.0
 * Author: The Growth Project
 * Author URI: https://thegrowthproject.com.au
 * License: GPL v2 or later
 * Text Domain: tgp-llms-txt
 *
 * @package TGP_LLMs_Txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TGP_LLMS_VERSION', '1.2.0' );
define( 'TGP_LLMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TGP_LLMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main plugin class.
 */
class TGP_LLMs_Txt {

	/**
	 * Single instance.
	 *
	 * @var TGP_LLMs_Txt|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return TGP_LLMs_Txt
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load required files.
	 */
	private function load_dependencies() {
		require_once TGP_LLMS_PLUGIN_DIR . 'includes/class-markdown-converter.php';
		require_once TGP_LLMS_PLUGIN_DIR . 'includes/class-frontmatter.php';
		require_once TGP_LLMS_PLUGIN_DIR . 'includes/class-endpoint-handler.php';
		require_once TGP_LLMS_PLUGIN_DIR . 'includes/class-llms-txt-generator.php';
		require_once TGP_LLMS_PLUGIN_DIR . 'includes/class-ui-buttons.php';
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		// Initialize components.
		new TGP_Endpoint_Handler();
		new TGP_LLMs_Txt_Generator();
		new TGP_UI_Buttons();

		// Register block.
		add_action( 'init', [ $this, 'register_blocks' ] );

		// Activation hook.
		register_activation_hook( __FILE__, [ $this, 'activate' ] );
		register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
	}

	/**
	 * Register Gutenberg blocks.
	 */
	public function register_blocks() {
		// Register individual button blocks.
		register_block_type( TGP_LLMS_PLUGIN_DIR . 'blocks/copy-button' );
		register_block_type( TGP_LLMS_PLUGIN_DIR . 'blocks/view-button' );

		// Copy button styles from core/button to our custom button blocks.
		$this->register_button_styles_from_theme();

		// Localize script for frontend copy functionality.
		wp_localize_script(
			'tgp-copy-button-view-script',
			'tgpLlmBlock',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'tgp_llms_nonce' ),
			]
		);

		// Register block pattern.
		register_block_pattern(
			'tgp/llm-buttons',
			[
				'title'       => __( 'LLM Buttons', 'tgp-llms-txt' ),
				'description' => __( 'Copy for LLM and View as Markdown buttons.', 'tgp-llms-txt' ),
				'categories'  => [ 'buttons' ],
				'keywords'    => [ 'llm', 'markdown', 'ai', 'copy', 'chatgpt' ],
				'content'     => '<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:tgp/copy-button /-->

<!-- wp:tgp/view-button /--></div>
<!-- /wp:buttons -->',
			]
		);
	}

	/**
	 * Register button styles from theme for our custom button blocks.
	 *
	 * This copies any block styles registered for core/button (like Brand, Dark, Light, Tint)
	 * and registers them for our tgp/copy-button and tgp/view-button blocks.
	 */
	private function register_button_styles_from_theme() {
		$registry = WP_Block_Styles_Registry::get_instance();

		// Get all styles registered for core/button.
		$button_styles = $registry->get_registered_styles_for_block( 'core/button' );

		if ( empty( $button_styles ) ) {
			return;
		}

		// Our custom button blocks.
		$our_blocks = [ 'tgp/copy-button', 'tgp/view-button' ];

		// Register each style for our blocks.
		foreach ( $button_styles as $style_name => $style_props ) {
			// Skip the default fill/outline styles - we define those in block.json.
			if ( in_array( $style_name, [ 'fill', 'outline' ], true ) ) {
				continue;
			}

			foreach ( $our_blocks as $block_name ) {
				register_block_style( $block_name, $style_props );
			}
		}
	}

	/**
	 * Plugin activation.
	 */
	public function activate() {
		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation.
	 */
	public function deactivate() {
		// Flush rewrite rules.
		flush_rewrite_rules();
	}
}

/**
 * Initialize plugin.
 *
 * @return TGP_LLMs_Txt
 */
function tgp_llms_txt_init() {
	return TGP_LLMs_Txt::get_instance();
}
add_action( 'plugins_loaded', 'tgp_llms_txt_init' );
