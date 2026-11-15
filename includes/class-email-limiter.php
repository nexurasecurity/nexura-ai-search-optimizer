<?php
/**
 * Email Rate Limiter for Live & Low-End Hosting Environments
 *
 * Enforces strict hourly dispatch throttling (max 50 emails/hour)
 * to prevent server overload, shared hosting bans, or mail queue congestion.
 *
 * @package NexuraAISearch
 */

namespace NexuraAISearch;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles email throttling and server health protection.
 */
class Email_Limiter {

	/**
	 * Storage option key for throttle metrics.
	 */
	const OPTION_KEY = 'naso_email_throttle_data';

	/**
	 * Default maximum allowed emails per hour.
	 */
	const DEFAULT_MAX_HOURLY_EMAILS = 50;

	/**
	 * Determine if an email can be safely dispatched without violating rate limits.
	 *
	 * @return bool True if within limits, false if throttled.
	 */
	public static function can_send_email() {
		$max_allowed = apply_filters( 'naso_max_emails_per_hour', self::DEFAULT_MAX_HOURLY_EMAILS );
		$data        = self::get_throttle_data();
		$now         = time();

		// If current 1-hour window expired, reset counter
		if ( ( $now - $data['window_start'] ) >= HOUR_IN_SECONDS ) {
			$data = array(
				'window_start' => $now,
				'count'        => 0,
			);
			update_option( self::OPTION_KEY, $data, false );
			return true;
		}

		if ( $data['count'] >= $max_allowed ) {
			// Log suppression for diagnostics
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Conditional debug log for diagnostics.
				error_log( sprintf( '[Nexura AI Search] Email suppressed: Reached maximum limit of %d emails in the current hour.', $max_allowed ) );
			}
			return false;
		}

		return true;
	}

	/**
	 * Record that an email was dispatched, incrementing the hourly counter.
	 *
	 * @return void
	 */
	public static function record_email_sent() {
		$data = self::get_throttle_data();
		$now  = time();

		if ( ( $now - $data['window_start'] ) >= HOUR_IN_SECONDS ) {
			$data['window_start'] = $now;
			$data['count']        = 1;
		} else {
			$data['count'] = intval( $data['count'] ) + 1;
		}

		update_option( self::OPTION_KEY, $data, false );
	}

	/**
	 * Retrieve current throttle metrics.
	 *
	 * @return array
	 */
	public static function get_throttle_data() {
		$data = get_option( self::OPTION_KEY, array() );
		$now  = time();

		if ( ! is_array( $data ) || ! isset( $data['window_start'], $data['count'] ) ) {
			return array(
				'window_start' => $now,
				'count'        => 0,
			);
		}

		return array(
			'window_start' => absint( $data['window_start'] ),
			'count'        => absint( $data['count'] ),
		);
	}
}
