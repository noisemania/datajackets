<?php

class SiteGuard_Menu_Advanced_Setting extends SiteGuard_Base {
	function __construct( ) {
		$this->render_page( );
	}
	function is_ip_mode_value( $value ) {
		$items = array( '0', '1', '2', '3' );
		if ( in_array( $value, $items ) ) {
			return true;
		}
		return false;
	}
	function render_page( ) {
		global $siteguard_config, $siteguard_admin_filter;

		$ip_mode = $siteguard_config->get( 'ip_mode' );
		if ( empty( $ip_mode ) ) {
			$ip_mode = '0';
			$siteguard_config->set( 'ip_mode', $ip_mode );
			$siteguard_config->update( );
		}
		if ( isset( $_POST['update'] ) && check_admin_referer( 'siteguard-menu-advanced-setting-submit' ) ) {
			$error = false;
			$errors = siteguard_check_multisite( );
			if ( is_wp_error( $errors ) ) {
				echo '<div class="error settings-error"><p><strong>';
				esc_html_e( $errors->get_error_message( ), 'siteguard' );
				echo '</strong></p></div>';
				$error = true;
			}
			if ( ( false === $error ) && ( false === $this->is_ip_mode_value( $_POST[ 'ip_mode' ] ) ) ) {
				echo '<div class="error settings-error"><p><strong>';
				esc_html_e( 'ERROR: Invalid input value.', 'siteguard' );
				echo '</strong></p></div>';
				$error = true;
			}
			if ( false === $error ) {
				$ip_mode = $_POST[ 'ip_mode' ];
				$siteguard_config->set( 'ip_mode', $ip_mode );
				$siteguard_config->update( );
				if ( 1 == $siteguard_config->get( 'admin_filter_enable' ) ) {
					$siteguard_admin_filter->feature_on( $this->get_ip( ) );
				}
				?>
				<div class="updated"><p><strong><?php esc_html_e( 'Options saved.', 'siteguard' ); ?></strong></p></div>
				<?php
			}
		}

		echo '<div class="wrap">';
		echo '<img src="' . SITEGUARD_URL_PATH . 'images/sg_wp_plugin_logo_40.png" alt="SiteGuard Logo" />';
		echo '<h2>' . esc_html__( 'Advanced Setting', 'siteguard' ) . '</h2>';
		echo '<div class="siteguard-description">'
		. esc_html__( 'You can find docs about this function on ', 'siteguard' )
		. '<a href="' . esc_url( __( 'https://www.jp-secure.com/siteguard_wp_plugin_en/howto/advanced_setting/', 'siteguard' ) )
		. '" target="_blank">'
		. esc_html__( 'here', 'siteguard' )
		. '</a>'
		. esc_html__( '.', 'siteguard' )
		. '</div>';
		?>
		<form name="form1" method="post" action="">
		<table class="form-table">
		<tr>
		<th scope="row"><?php esc_html_e( 'IP Address Mode', 'siteguard' ); ?></th>
			<td>
				<input type="radio" name="ip_mode" id="ip_mode_ra" value="0" <?php checked( $ip_mode, '0' ) ?> >
				<label for="ip_mode_ra"><?php esc_html_e( 'REMOTE_ADDR', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="ip_mode" id="ip_mode_xff1" value="1" <?php checked( $ip_mode, '1' ) ?> >
				<label for="ip_mode_xff1"><?php esc_html_e( 'X-Forwarded-For Level:1', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="ip_mode" id="ip_mode_xff2" value="2" <?php checked( $ip_mode, '2' ) ?> >
				<label for="ip_mode_xff2"><?php esc_html_e( 'X-Forwarded-For Level:2', 'siteguard' ) ?></label>
				<br />
				<input type="radio" name="ip_mode" id="ip_mode_xff3" value="3" <?php checked( $ip_mode, '3' ) ?> >
				<label for="ip_mode_xff3"><?php esc_html_e( 'X-Forwarded-For Level:3', 'siteguard' ) ?></label>
			</td>
		</tr>
		</table>
		<div class="siteguard-description">
		<?php esc_html_e( "Set the method for acquiring the IP address. Normally you should select a remote address. If there is a proxy or load balancer in front of the web server and you can not obtain the client's IP address with remote address, you can obtain the IP address from X-Forwarded-For. Level represents the number from the right end of the value of X-Forwarded-For.", 'siteguard' ) ?>
		</div>
		<input type="hidden" name="update" value="Y">
		<hr />

		<?php
		wp_nonce_field( 'siteguard-menu-advanced-setting-submit' );
		submit_button();
		?>
		</form>
		</div>
		<?php
	}
}
