<?php
/**
 * User Period Register
 *
 * @package    UserPeriodRegister
 * @subpackage UserPeriodRegisterRegist registered in the database
/*
	Copyright (c) 2019- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

$userperiodregisterregist = new UserPeriodRegisterRegist();

/** ==================================================
 * Registered in the database
 */
class UserPeriodRegisterRegist {

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'register_settings' ) );
		register_activation_hook( plugin_dir_path( __DIR__ ) . 'userperiodregister.php', array( $this, 'active' ) );
		register_deactivation_hook( plugin_dir_path( __DIR__ ) . 'userperiodregister.php', array( $this, 'deactive' ) );

	}

	/** ==================================================
	 * Settings register
	 *
	 * @since 1.00
	 */
	public function register_settings() {

		if ( get_option( 'userperiodregister' ) ) {
			$upr_settings = get_option( 'userperiodregister' );
			/* ver 1.03 later */
			if ( array_key_exists( 'login_message', $upr_settings ) ) {
				unset( $upr_settings['login_message'] );
				update_option( 'userperiodregister', $upr_settings );
			}
			if ( array_key_exists( 'login_logo_url', $upr_settings ) ) {
				unset( $upr_settings['login_logo_url'] );
				update_option( 'userperiodregister', $upr_settings );
			}
			if ( array_key_exists( 'login_logo_link_url', $upr_settings ) ) {
				unset( $upr_settings['login_logo_link_url'] );
				update_option( 'userperiodregister', $upr_settings );
			}
			if ( array_key_exists( 'termofuse', $upr_settings ) ) {
				unset( $upr_settings['termofuse'] );
				update_option( 'userperiodregister', $upr_settings );
			}
		} else {
			$upr_tbl = array(
				'temp_user_period' => 3600,
				'paid_role' => 'subscriber',
				'paid_period' => 31536000,
			);
			update_option( 'userperiodregister', $upr_tbl );
		}

	}

	/** ==================================================
	 * Active
	 *
	 * @since 1.00
	 */
	public function active() {
		add_role( 'temp_user', __( 'Temporary user', 'user-period-register' ), array( 'read' => true ) );
		update_option( 'default_role', 'temp_user' );
	}

	/** ==================================================
	 * Deactive
	 *
	 * @since 1.00
	 */
	public function deactive() {
		remove_role( 'temp_user' );
		update_option( 'default_role', 'subscriber' );
	}

}


