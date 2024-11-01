<?php
/**
 * User Period Register
 *
 * @package    UserPeriodRegister
 * @subpackage User Period Register Main function
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

$userperiodregister = new UserPeriodRegister();

/** ==================================================
 * Class Main function
 *
 * @since 1.00
 */
class UserPeriodRegister {

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		add_filter( 'manage_users_columns', array( $this, 'custom_users_columns' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'custom_users_custom_column' ), 10, 3 );

		add_action( 'userperiodregisteracounthook', array( $this, 'delete_data_acount' ), 10, 2 );

		add_action( 'show_password_fields', array( $this, 'pay_form' ) );
		add_filter( 'slmclient_licensed', array( $this, 'licensekey_charge' ), 10, 2 );
		add_action( 'wp_dashboard_setup', array( $this, 'pay_form_dashboard_widgets' ) );

		add_action( 'set_user_role', array( $this, 'action_role_change' ), 10, 3 );
		add_action( 'user_register', array( $this, 'action_add_user' ), 10, 1 );
		add_action( 'delete_user', array( $this, 'action_delete_user' ), 10, 1 );

	}

	/** ==================================================
	 * Administrator Add User Hook
	 *
	 * @param int $userid  The user ID.
	 * @since 1.00
	 */
	public function action_add_user( $userid ) {

		$upr_settings = get_option( 'userperiodregister' );
		$user = get_userdata( $userid );
		$useremail = $user->user_email;
		$role = implode( ', ', $user->roles );
		if ( $upr_settings['paid_role'] === $role ) {
			update_user_meta( $userid, 'upr_paid', true );
			update_user_meta( $userid, 'wp_capabilities', array( $role => true ) );
			wp_clear_scheduled_hook( 'userperiodregisteracounthook', array( $userid, $useremail ) );
			wp_schedule_single_event( time() + intval( $upr_settings['paid_period'] ), 'userperiodregisteracounthook', array( $userid, $useremail ) );
		} else if ( 'temp_user' === $role ) {
			update_user_meta( $userid, 'upr_paid', false );
			update_user_meta( $userid, 'wp_capabilities', array( $role => true ) );
			wp_clear_scheduled_hook( 'userperiodregisteracounthook', array( $userid, $useremail ) );
			wp_schedule_single_event( time() + intval( $upr_settings['temp_user_period'] ), 'userperiodregisteracounthook', array( $userid, $useremail ) );
		} else {
			wp_clear_scheduled_hook( 'userperiodregisteracounthook', array( $userid, $useremail ) );
		}

	}

	/** ==================================================
	 * Administrator Delete User Hook
	 *
	 * @param int $userid  The user ID.
	 * @since 1.00
	 */
	public function action_delete_user( $userid ) {

		$user = get_userdata( $userid );
		$useremail = $user->user_email;

		/* translators: %s: blogname */
		$subject = sprintf( __( '[%s] Delete Acount', 'user-period-register' ), get_option( 'blogname' ) );
		$message = __( "The created account has been deleted due to expiration or administrator's judgment. Thank you for using.", 'user-period-register' ) . "\r\n\r\n";
		/* translators: %s: email  */
		$message_admin = sprintf( __( 'The created account[%s] has been deleted.', 'user-period-register' ), $useremail ) . "\r\n\r\n";
		wp_mail( $useremail, $subject, $message );
		@wp_mail( get_option( 'admin_email' ), $subject, $message_admin );

		wp_clear_scheduled_hook( 'userperiodregisteracounthook', array( $userid, $useremail ) );

		/* Deactive License Key for Software License Manager Client */
		if ( class_exists( 'SlmClient' ) ) {
			if ( get_option( 'license_key_user-period-register' ) ) {
				do_action( 'deactive_slm_key', $arg = array() );
			}
		}

	}

	/** ==================================================
	 * Administrator Role Change Hook
	 *
	 * @param int    $userid  The user ID.
	 * @param string $role  The new role.
	 * @param array  $old_roles  An array of the user's previous roles.
	 * @since 1.00
	 */
	public function action_role_change( $userid, $role, $old_roles ) {

		$upr_settings = get_option( 'userperiodregister' );
		$user = get_userdata( $userid );
		$useremail = $user->user_email;
		if ( $upr_settings['paid_role'] === $role ) {
			update_user_meta( $userid, 'upr_paid', true );
			update_user_meta( $userid, 'wp_capabilities', array( $role => true ) );
			wp_clear_scheduled_hook( 'userperiodregisteracounthook', array( $userid, $useremail ) );
			wp_schedule_single_event( time() + intval( $upr_settings['paid_period'] ), 'userperiodregisteracounthook', array( $userid, $useremail ) );
		} else if ( 'temp_user' === $role ) {
			update_user_meta( $userid, 'upr_paid', false );
			update_user_meta( $userid, 'wp_capabilities', array( $role => true ) );
			wp_clear_scheduled_hook( 'userperiodregisteracounthook', array( $userid, $useremail ) );
			wp_schedule_single_event( time() + intval( $upr_settings['temp_user_period'] ), 'userperiodregisteracounthook', array( $userid, $useremail ) );
			/* Deactive License Key for Software License Manager Client */
			if ( class_exists( 'SlmClient' ) ) {
				if ( get_option( 'license_key_user-period-register' ) ) {
					do_action( 'deactive_slm_key', $arg = array() );
				}
			}
		} else {
			wp_clear_scheduled_hook( 'userperiodregisteracounthook', array( $userid, $useremail ) );
		}

	}

	/** ==================================================
	 * Pay form
	 *
	 * @param bool $bool  bool.
	 * @since 1.00
	 */
	public function pay_form( $bool ) {

		$upr_settings = get_option( 'userperiodregister' );

		if ( current_user_can( 'administrator' ) || current_user_can( 'temp_user' ) || current_user_can( $upr_settings['paid_role'] ) ) {

			$user = wp_get_current_user();
			$userid = $user->ID;
			$useremail = $user->user_email;
			$dead_line = null;

			$screen = get_current_screen();
			if ( 'profile' === $screen->id || 'user-edit' === $screen->id ) {
				if ( current_user_can( 'administrator' ) ) {
					if ( isset( $_GET['user_id'] ) && ! empty( $_GET['user_id'] ) ) {
						$userid = intval( $_GET['user_id'] );
						$user_info = get_userdata( $userid );
						$role = implode( ', ', $user_info->roles );
						if ( $upr_settings['paid_role'] === $role || 'temp_user' === $role ) {
							$useremail = $user_info->user_email;
							if ( wp_next_scheduled( 'userperiodregisteracounthook', array( $userid, $useremail ) ) ) {
								$dead_line = ' ' . __( 'Expiration Date', 'user-period-register' ) . ': ' . get_date_from_gmt( gmdate( 'Y-m-d H:i:s', wp_next_scheduled( 'userperiodregisteracounthook', array( $userid, $useremail ) ) ) );
							}
							?>
							<tr>
							<th scope="row"><h3><?php echo esc_html( __( 'Registration status', 'user-period-register' ) . ' : ' . get_option( 'blogname' ) ); ?></h3></th>
							<td>
							<?php
							if ( get_user_meta( $userid, 'upr_paid', true ) ) {
								?>
								<h3><?php echo esc_html( __( 'Registered.', 'user-period-register' ) . $dead_line ); ?></h3>
								<?php
							} else {
								?>
								<h3><?php echo esc_html( __( 'Temporary registered.', 'user-period-register' ) . $dead_line ); ?></h3>
								<?php
							}
							?>
							</td>
							</tr>
							<?php
						}
					}
				} else {
					?>
					<tr>
					<th scope="row"><h3><?php echo esc_html( __( 'Registration status', 'user-period-register' ) . ' : ' . get_option( 'blogname' ) ); ?></h3></th>
					<td>
					<?php
					if ( wp_next_scheduled( 'userperiodregisteracounthook', array( $userid, $useremail ) ) ) {
						$dead_line = ' ' . __( 'Expiration Date', 'user-period-register' ) . ': ' . get_date_from_gmt( gmdate( 'Y-m-d H:i:s', wp_next_scheduled( 'userperiodregisteracounthook', array( $userid, $useremail ) ) ) );
					}
					if ( get_user_meta( $userid, 'upr_paid', true ) ) {
						?>
						<h3><?php echo esc_html( __( 'Registered.', 'user-period-register' ) . $dead_line ); ?></h3>
						<?php
					} else {
						?>
						<h3><?php echo esc_html( __( 'Temporary registered.', 'user-period-register' ) . $dead_line ); ?></h3>
						<?php
						if ( class_exists( 'SlmClient' ) ) {
							echo do_shortcode( '[slmcl]' );
						}
					}
					?>
					</td>
					</tr>
					<?php
				}
			}
		}

		return $bool;

	}

	/** ==================================================
	 * Pay form for Dashboard
	 *
	 * @since 1.00
	 */
	public function pay_form_dashboard_widgets() {

		$upr_settings = get_option( 'userperiodregister' );
		$user = wp_get_current_user();
		$useremail = $user->user_email;
		$role = implode( ', ', $user->roles );
		if ( $upr_settings['paid_role'] === $role || 'temp_user' === $role ) {
			global $wp_meta_boxes;
			wp_add_dashboard_widget( 'custom_help_widget', __( 'Registration status', 'user-period-register' ) . ' : ' . get_option( 'blogname' ), array( $this, 'dashboard_text' ) );
		}

	}

	/** ==================================================
	 * Dashboard text
	 *
	 * @since 1.00
	 */
	public function dashboard_text() {

		$upr_settings = get_option( 'userperiodregister' );
		$user = wp_get_current_user();
		$userid = $user->ID;
		$useremail = $user->user_email;
		$dead_line = null;

		$screen = get_current_screen();
		if ( 'dashboard' === $screen->id ) {
			if ( current_user_can( 'administrator' ) ) {
				if ( isset( $_GET['user_id'] ) && ! empty( $_GET['user_id'] ) ) {
					$userid = intval( $_GET['user_id'] );
					$user_info = get_userdata( $userid );
					$useremail = $user_info->user_email;
					if ( wp_next_scheduled( 'userperiodregisteracounthook', array( $userid, $useremail ) ) ) {
						$dead_line = ' ' . __( 'Expiration Date', 'user-period-register' ) . ': ' . get_date_from_gmt( gmdate( 'Y-m-d H:i:s', wp_next_scheduled( 'userperiodregisteracounthook', array( $userid, $useremail ) ) ) );
					}
					if ( get_user_meta( $userid, 'upr_paid', true ) ) {
						?>
						<h3><strong><?php echo esc_html( __( 'Registered.', 'user-period-register' ) . $dead_line ); ?></strong></h3>
						<?php
					} else {
						?>
						<h3><strong><?php echo esc_html( __( 'Temporary registered.', 'user-period-register' ) . $dead_line ); ?></strong></h3>
						<?php
					}
				}
			} else {
				if ( wp_next_scheduled( 'userperiodregisteracounthook', array( $userid, $useremail ) ) ) {
					$dead_line = ' ' . __( 'Expiration Date', 'user-period-register' ) . ': ' . get_date_from_gmt( gmdate( 'Y-m-d H:i:s', wp_next_scheduled( 'userperiodregisteracounthook', array( $userid, $useremail ) ) ) );
				}
				if ( get_user_meta( $userid, 'upr_paid', true ) ) {
					?>
					<h3><strong><?php echo esc_html( __( 'Registered.', 'user-period-register' ) . $dead_line ); ?></strong></h3>
					<?php
				} else {
					?>
					<h3><strong><?php echo esc_html( __( 'Temporary registered.', 'user-period-register' ) . $dead_line ); ?></strong></h3>
					<?php
					if ( class_exists( 'SlmClient' ) ) {
						echo do_shortcode( '[slmcl]' );
					}
				}
			}
		}

	}

	/** ==================================================
	 * License Key Charge
	 *
	 * @param string $license_key  license_key.
	 * @param string $item_reference  item_reference.
	 * @since 1.00
	 */
	public function licensekey_charge( $license_key, $item_reference ) {
		$this->paid( $item_reference );
	}

	/** ==================================================
	 * Paid
	 *
	 * @param string $payname  payname.
	 * @since 1.00
	 */
	private function paid( $payname ) {

		if ( is_admin() && 'user-period-register' === $payname ) {
			$upr_settings = get_option( 'userperiodregister' );
			$user = wp_get_current_user();
			$role = implode( ', ', $user->roles );
			if ( 'temp_user' === $role ) {
				$userid = $user->ID;
				$useremail = $user->user_email;
				update_user_meta( $userid, 'upr_paid', true );
				update_user_meta( $userid, 'wp_capabilities', array( $upr_settings['paid_role'] => true ) );
				wp_clear_scheduled_hook( 'userperiodregisteracounthook', array( $userid, $useremail ) );
				wp_schedule_single_event( time() + intval( $upr_settings['paid_period'] ), 'userperiodregisteracounthook', array( $userid, $useremail ) );
			}
		}

	}

	/** ==================================================
	 * For only pay form
	 *
	 * @since 1.00
	 */
	private function is_pay_form_screen() {
		$screen = get_current_screen();
		if ( is_object( $screen ) && 'dashboard' === $screen->id ) {
			return true;
		} else if ( is_object( $screen ) && 'profile' === $screen->id ) {
			return true;
		} else {
			return false;
		}
	}

	/** ==================================================
	 * User List Custom Column
	 *
	 * @param array $columns  columns.
	 * @since 1.00
	 */
	public function custom_users_columns( $columns ) {
		$columns['user_limit'] = __( 'Expiration Date', 'user-period-register' );
		return $columns;
	}

	/** ==================================================
	 * User List Custom Column Data
	 *
	 * @param string $dummy  dummy.
	 * @param string $column  column.
	 * @param int    $user_id  user_id.
	 * @since 1.00
	 */
	public function custom_users_custom_column( $dummy, $column, $user_id ) {
		if ( 'user_limit' === $column ) {
			$user_info = get_userdata( $user_id );
			$useremail = $user_info->user_email;
			if ( wp_next_scheduled( 'userperiodregisteracounthook', array( $user_id, $useremail ) ) ) {
				$dead_line = get_date_from_gmt( gmdate( 'Y-m-d H:i:s', wp_next_scheduled( 'userperiodregisteracounthook', array( $user_id, $useremail ) ) ) );
				return $dead_line;
			}
		}
	}

	/** ==================================================
	 * Delete Acount
	 *
	 * @param int    $userid  userid.
	 * @param string $useremail  useremail.
	 * @since 1.00
	 */
	public function delete_data_acount( $userid, $useremail ) {

		require_once( ABSPATH . 'wp-admin/includes/user.php' );
		wp_delete_user( $userid );
		wp_clear_scheduled_hook( 'userperiodregisteracounthook', array( $userid, $useremail ) );

	}

}


