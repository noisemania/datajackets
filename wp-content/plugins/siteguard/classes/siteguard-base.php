<?php

function siteguard_error_log( $message ) {
	$logfile = SITEGUARD_PATH . 'error.log';
	$f = @fopen( $logfile, 'a+' );
	if ( false != $f ) {
		fwrite( $f, date_i18n( 'Y/m/d H:i:s:' ) . $message . "\n" );
		fclose( $f );
	}
}

function siteguard_error_dump( $title, $obj ) {
	ob_start();
	var_dump( $obj );
	$msg = ob_get_contents( );
	ob_end_clean( );
	siteguard_error_log( "$title: $msg" );
}

function siteguard_check_multisite( ) {
	if ( ! is_multisite() ) {
		return true;
	}
	$message  = esc_html__( 'It does not support the multisite function of WordPress.', 'siteguard' );
	$error = new WP_Error( 'siteguard', $message );
	return $error;
}

class SiteGuard_Base {
	public static $ip_mode_items = array( '0', '1', '2', '3' );
	function __construct() {
	}
	function is_switch_value( $value ) {
		if ( '0' === $value || '1' === $value ) {
			return true;
		}
		return false;
	}
	function check_module( $name, $default = false ) {
		return true;
		# It does not work WP-CLI
		#if ( isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
		#	return ( strpos( $_SERVER['SERVER_SOFTWARE'], 'Apache' ) !== false || strpos( $_SERVER['SERVER_SOFTWARE'], 'LiteSpeed' ) !== false);
		#} else {
		#	return $default;
		#}

		# It does not work in FastCGI well.
		#$module = 'mod_' . $name;
		#return apache_mod_loaded( $module, $default );
		#if ( function_exists('phpinfo') ) {
		#	ob_start( );
		#	phpinfo(8);
		#	$phpinfo = ob_get_clean( );
		#	if ( false !== strpos( $phpinfo, $module ) ) {
		#		return true;
		#	}
		#}
		#return $default;
	}

	function get_ip( ) {
		global $siteguard_config;
		$ip_mode = $siteguard_config->get( 'ip_mode' );
		if ( ! in_array( $ip_mode, SiteGuard_Base::$ip_mode_items ) ) {
			$ip_mode = '0';
			$siteguard_config->set( 'ip_mode', $ip_mode );
			$siteguard_config->update( );
		}
		$ip_mode_num = intval( $ip_mode );
		$remote_addr = '127.0.0.1';
		if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$remote_addr = $_SERVER['REMOTE_ADDR'];
		}
		if ( '0' === $ip_mode ) {
			return $remote_addr;
		} 
		if ( ! isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			return $remote_addr;
		}
		$xff = $_SERVER['HTTP_X_FORWARDED_FOR'];
		if ( empty( $xff ) ) {
			return $remote_addr;
		}
		$ips = explode( ',', $xff );
		$count = count( $ips );
		$idx = $count - $ip_mode_num;
		if ( $idx < 0 ) {
			return $remote_addr;
		}
		$ip = $ips[ $idx ];
		if ( ! filter_var($ip, FILTER_VALIDATE_IP ) ) {
			return $remote_addr;
		}
		return $ip;
	}
}
