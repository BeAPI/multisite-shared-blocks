<?php
/**
 * Plugin Name: MS Install Custom Ports
 * Plugin URI: https://beapi.fr
 * Description: Allow Network installation with custom ports. This is not suitable for production environment !
 * Version: 1.0.0
 * Author: Be API
 * Author URI: http://beapi.fr
 *
 * Refactored from https://wordpress.stackexchange.com/a/213001
 */

// This is only allowed on development environments.
if ( ! defined( 'WP_ENVIRONMENT_TYPE' ) || 'local' !== WP_ENVIRONMENT_TYPE ) {
	return;
}

/**
 * Class Wpms_Custom_Ports
 */
class Ms_Custom_Ports {

	public function __construct() {
		add_filter( 'sanitize_user', [ $this, 'sanitize_user' ], 1, 3 );
		add_action( 'load-network.php', [ $this, 'ms_install_bypass_port_error' ] );
	}

	/**
	 * Get around the problem with wpmu_create_blog() where sanitize_user()
	 * strips out the semicolon (:) in the $domain string
	 * This means created sites with hostnames of
	 * e.g. example.tld8080 instead of example.tld:8080
	 *
	 * @param string $username
	 * @param string $raw_username
	 * @param bool $strict
	 *
	 * @return string
	 */
	public function sanitize_user( $username, $raw_username, $strict ) {

		// Edit the port to your needs
		$port = $this->get_custom_port();
		if ( ! $port ) {
			return $username;
		}

		if ( $strict                                                              // wpmu_create_blog uses strict mode
		     && is_multisite()                                                    // multisite check
		     && (int) wp_parse_url( $raw_username, PHP_URL_PORT ) === (int) $port // raw domain has port
		     && false === strpos( $username, ':' . $port )                 // stripped domain is without correct port
		) {
			$username = str_replace( $port, ':' . $port, $username ); // replace e.g. example.tldXXXX to example.tld:XXXX
		}

		return $username;
	}

	/**
	 * Temporary change the port (e.g. :8080 ) to :80 to get around
	 * the core restriction in the network.php page.
	 */
	public function ms_install_bypass_port_error() {
		add_filter(
			'option_active_plugins',
			function ( $value ) {
				add_filter(
					'option_siteurl',
					function ( $value ) {

						// Network step 2
						// Don't alter siteurl if we are on step 2 of network creation
						if ( is_multisite() || network_domain_check() ) {
							return $value;
						}

						// Get custom port in use. If we can't retrieve the custom port bail out
						$port = $this->get_custom_port();
						if ( ! $port ) {
							return $value;
						}

						// Network step 1
						// If we are on step 1 of network creation, remove custom port to allow
						// the creation process to continue
						static $count = 0;
						if ( 0 === $count ++ ) {
							$value = str_replace( ':' . $port, ':80', $value );
						}

						return $value;
					}
				);

				return $value;
			}
		);
	}

	/**
	 * Retrieve custom port in use
	 *
	 * @return string|bool
	 */
	protected function get_custom_port() {
		return defined( 'APACHE_PORT' ) ? APACHE_PORT : false;
	}
}

new Ms_Custom_Ports();
