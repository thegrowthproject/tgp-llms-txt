<?php
/**
 * Logger
 *
 * Provides structured logging for debugging and monitoring.
 * Respects WordPress debug constants and fires action hooks for external integrations.
 *
 * @package TGP_LLMs_Txt
 */

declare(strict_types=1);

namespace TGP\LLMsTxt;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logger class.
 *
 * Provides error, warning, and debug logging methods with structured context.
 * Logs are written to PHP error_log when WP_DEBUG is enabled.
 * Fires action hooks for external logging integrations (Sentry, etc).
 */
class Logger {

	/**
	 * Log prefix for all messages.
	 *
	 * @var string
	 */
	private const LOG_PREFIX = '[TGP LLMs.txt]';

	/**
	 * Log an error message.
	 *
	 * Errors indicate failures that prevent normal operation.
	 * Always fires the tgp_llms_txt_error action for external integrations.
	 *
	 * @param string $message The error message.
	 * @param array  $context Additional context data.
	 */
	public static function error( string $message, array $context = [] ): void {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging.
			error_log( self::format( 'ERROR', $message, $context ) );
		}

		/**
		 * Fires when an error is logged.
		 *
		 * Use this action to integrate with external logging services
		 * like Sentry, Bugsnag, or custom logging solutions.
		 *
		 * @since 1.3.0
		 *
		 * @param string $message The error message.
		 * @param array  $context Additional context data.
		 */
		do_action( 'tgp_llms_txt_error', $message, $context );
	}

	/**
	 * Log a warning message.
	 *
	 * Warnings indicate potential issues that don't prevent operation
	 * but may indicate problems (e.g., missing optional data).
	 *
	 * @param string $message The warning message.
	 * @param array  $context Additional context data.
	 */
	public static function warning( string $message, array $context = [] ): void {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging.
			error_log( self::format( 'WARNING', $message, $context ) );
		}

		/**
		 * Fires when a warning is logged.
		 *
		 * @since 1.3.0
		 *
		 * @param string $message The warning message.
		 * @param array  $context Additional context data.
		 */
		do_action( 'tgp_llms_txt_warning', $message, $context );
	}

	/**
	 * Log a debug message.
	 *
	 * Debug messages provide detailed information for troubleshooting.
	 * Only logged when WP_DEBUG_LOG is explicitly enabled.
	 *
	 * @param string $message The debug message.
	 * @param array  $context Additional context data.
	 */
	public static function debug( string $message, array $context = [] ): void {
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging.
			error_log( self::format( 'DEBUG', $message, $context ) );
		}
	}

	/**
	 * Format a log message.
	 *
	 * Creates a consistent log format: [TGP LLMs.txt] [LEVEL] Message {context}
	 *
	 * @param string $level   The log level (ERROR, WARNING, DEBUG).
	 * @param string $message The log message.
	 * @param array  $context Additional context data.
	 * @return string Formatted log message.
	 */
	private static function format( string $level, string $message, array $context ): string {
		$context_str = ! empty( $context ) ? ' ' . wp_json_encode( $context ) : '';
		return sprintf( '%s [%s] %s%s', self::LOG_PREFIX, $level, $message, $context_str );
	}
}
