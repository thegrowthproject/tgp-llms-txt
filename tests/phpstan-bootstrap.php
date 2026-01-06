<?php
/**
 * PHPStan bootstrap file.
 *
 * Defines constants for static analysis.
 *
 * @package TGP_LLMs_Txt
 */

define( 'TGP_LLMS_VERSION', '1.3.2' );
define( 'TGP_LLMS_PLUGIN_DIR', __DIR__ . '/../' );
define( 'TGP_LLMS_PLUGIN_URL', 'https://example.com/wp-content/plugins/tgp-llms-txt/' );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../../../' );
}
