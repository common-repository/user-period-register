<?php
/**
 * User Period Register
 *
 * @package    User Period Register
 * @subpackage UserPeriodRegisterAdmin Management screen
/*  Copyright (c) 2019- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
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

$userperiodregisteradmin = new UserPeriodRegisterAdmin();

/** ==================================================
 * Management screen
 */
class UserPeriodRegisterAdmin {

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'plugin_menu' ) );
		add_filter( 'plugin_action_links', array( $this, 'settings_link' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'notices' ) );

	}

	/** ==================================================
	 * Add a "Settings" link to the plugins page
	 *
	 * @param  array  $links  links array.
	 * @param  string $file   file.
	 * @return array  $links  links array.
	 * @since 1.00
	 */
	public function settings_link( $links, $file ) {
		static $this_plugin;
		if ( empty( $this_plugin ) ) {
			$this_plugin = 'user-period-register/userperiodregister.php';
		}
		if ( $file === $this_plugin ) {
			$links[] = '<a href="' . admin_url( 'options-general.php?page=userperiodregister' ) . '">' . __( 'Settings' ) . '</a>';
		}
			return $links;
	}

	/** ==================================================
	 * Settings page
	 *
	 * @since 1.00
	 */
	public function plugin_menu() {
		add_options_page( 'User Period Register Options', 'User Period Register', 'manage_options', 'userperiodregister', array( $this, 'plugin_options' ) );
	}

	/** ==================================================
	 * For only admin style
	 *
	 * @since 1.00
	 */
	private function is_my_plugin_screen() {
		$screen = get_current_screen();
		if ( is_object( $screen ) && 'settings_page_userperiodregister' === $screen->id ) {
			return true;
		} else {
			return false;
		}
	}

	/** ==================================================
	 * Settings page
	 *
	 * @since 1.00
	 */
	public function plugin_options() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
		}

		$this->options_updated();

		$scriptname = admin_url( 'options-general.php?page=userperiodregister' );
		$userperiodregister_settings = get_option( 'userperiodregister' );

		?>

		<div class="wrap">
		<h2>User Period Register</h2>

			<details>
			<summary><strong><?php esc_html_e( 'Various links of this plugin', 'user-period-register' ); ?></strong></summary>
			<?php $this->credit(); ?>
			</details>

			<hr>

			<form method="post" action="<?php echo esc_url( $scriptname ); ?>">
			<?php wp_nonce_field( 'upr_set', 'userperiodregister_set' ); ?>

			<div style="margin: 5px; padding: 5px;">
			<h3><?php echo esc_html( 'WordPress ' . __( 'Settings' ) ); ?></h3>
			<div style="display: block;padding:5px 5px">
			<?php esc_html_e( 'Membership' ); ?> : 
			<input name="users_can_register" type="checkbox" value="1" <?php checked( '1', get_option( 'users_can_register' ) ); ?> />
			<?php esc_html_e( 'Anyone can register' ); ?>
			</div>
			<div style="display: block;padding:5px 5px">
			<?php esc_html_e( 'New User Default Role' ); ?> : 
			<select name="default_role">
			<?php wp_dropdown_roles( get_option( 'default_role' ) ); ?>
			</select>
			</div>
			</div>

			<div style="margin: 5px; padding: 5px;">
			<h3><?php esc_html_e( 'User roles and expiration date', 'user-period-register' ); ?></h3>
			<div style="display: block;padding:5px 5px"><?php esc_html_e( 'Temporary registration user expiration hour', 'user-period-register' ); ?> : <input type="number" name="temp_user_period" min="1" max="48" value="<?php echo esc_attr( intval( $userperiodregister_settings['temp_user_period'] / 3600 ) ); ?>"></div>
			<div style="display: block;padding:5px 5px"><?php esc_html_e( 'Paid user roles', 'user-period-register' ); ?> : 
			<select name="paid_role">
			<?php wp_dropdown_roles( $userperiodregister_settings['paid_role'] ); ?>
			</select>
			</div>
			<div style="display: block;padding:5px 5px"><?php esc_html_e( 'Paid user expiration date', 'user-period-register' ); ?> : <input type="number" name="paid_period" min="1" value="<?php echo esc_attr( intval( $userperiodregister_settings['paid_period'] / 86400 ) ); ?>"></div>
			</div>

			<?php submit_button( __( 'Save Changes' ), 'large', 'Manageset', false ); ?>

			</form>

		</div>
		<?php
	}

	/** ==================================================
	 * Credit
	 *
	 * @since 1.00
	 */
	private function credit() {

		$plugin_name    = null;
		$plugin_ver_num = null;
		$plugin_path    = plugin_dir_path( __DIR__ );
		$plugin_dir     = untrailingslashit( wp_normalize_path( $plugin_path ) );
		$slugs          = explode( '/', $plugin_dir );
		$slug           = end( $slugs );
		$files          = scandir( $plugin_dir );
		foreach ( $files as $file ) {
			if ( '.' === $file || '..' === $file || is_dir( $plugin_path . $file ) ) {
				continue;
			} else {
				$exts = explode( '.', $file );
				$ext  = strtolower( end( $exts ) );
				if ( 'php' === $ext ) {
					$plugin_datas = get_file_data(
						$plugin_path . $file,
						array(
							'name'    => 'Plugin Name',
							'version' => 'Version',
						)
					);
					if ( array_key_exists( 'name', $plugin_datas ) && ! empty( $plugin_datas['name'] ) && array_key_exists( 'version', $plugin_datas ) && ! empty( $plugin_datas['version'] ) ) {
						$plugin_name    = $plugin_datas['name'];
						$plugin_ver_num = $plugin_datas['version'];
						break;
					}
				}
			}
		}
		$plugin_version = __( 'Version:' ) . ' ' . $plugin_ver_num;
		/* translators: FAQ Link & Slug */
		$faq       = sprintf( __( 'https://wordpress.org/plugins/%s/faq', 'user-period-register' ), $slug );
		$support   = 'https://wordpress.org/support/plugin/' . $slug;
		$review    = 'https://wordpress.org/support/view/plugin-reviews/' . $slug;
		$translate = 'https://translate.wordpress.org/projects/wp-plugins/' . $slug;
		$facebook  = 'https://www.facebook.com/katsushikawamori/';
		$twitter   = 'https://twitter.com/dodesyo312';
		$youtube   = 'https://www.youtube.com/channel/UC5zTLeyROkvZm86OgNRcb_w';
		$donate    = __( 'https://shop.riverforest-wp.info/donate/', 'user-period-register' );

		?>
		<span style="font-weight: bold;">
		<div>
		<?php echo esc_html( $plugin_version ); ?> | 
		<a style="text-decoration: none;" href="<?php echo esc_url( $faq ); ?>" target="_blank" rel="noopener noreferrer">FAQ</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $support ); ?>" target="_blank" rel="noopener noreferrer">Support Forums</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $review ); ?>" target="_blank" rel="noopener noreferrer">Reviews</a>
		</div>
		<div>
		<a style="text-decoration: none;" href="<?php echo esc_url( $translate ); ?>" target="_blank" rel="noopener noreferrer">
		<?php
		/* translators: Plugin translation link */
		echo esc_html( sprintf( __( 'Translations for %s' ), $plugin_name ) );
		?>
		</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $facebook ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-facebook"></span></a> | <a style="text-decoration: none;" href="<?php echo esc_url( $twitter ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-twitter"></span></a> | <a style="text-decoration: none;" href="<?php echo esc_url( $youtube ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-video-alt3"></span></a>
		</div>
		</span>

		<div style="width: 250px; height: 180px; margin: 5px; padding: 5px; border: #CCC 2px solid;">
		<h3><?php esc_html_e( 'Please make a donation if you like my work or would like to further the development of this plugin.', 'user-period-register' ); ?></h3>
		<div style="text-align: right; margin: 5px; padding: 5px;"><span style="padding: 3px; color: #ffffff; background-color: #008000">Plugin Author</span> <span style="font-weight: bold;">Katsushi Kawamori</span></div>
		<button type="button" style="margin: 5px; padding: 5px;" onclick="window.open('<?php echo esc_url( $donate ); ?>')"><?php esc_html_e( 'Donate to this plugin &#187;' ); ?></button>
		</div>

		<?php

	}

	/** ==================================================
	 * Update wp_options table.
	 *
	 * @since 1.00
	 */
	private function options_updated() {

		if ( isset( $_POST['Manageset'] ) && ! empty( $_POST['Manageset'] ) ) {
			if ( check_admin_referer( 'upr_set', 'userperiodregister_set' ) ) {
				if ( ! empty( $_POST['users_can_register'] ) ) {
					update_option( 'users_can_register', true );
				} else {
					update_option( 'users_can_register', false );
				}
				if ( ! empty( $_POST['default_role'] ) ) {
					update_option( 'default_role', sanitize_text_field( wp_unslash( $_POST['default_role'] ) ) );
				}
				$userperiodregister_settings = get_option( 'userperiodregister' );
				if ( ! empty( $_POST['temp_user_period'] ) ) {
					$userperiodregister_settings['temp_user_period'] = intval( $_POST['temp_user_period'] * 3600 );
				}
				if ( ! empty( $_POST['paid_role'] ) ) {
					$userperiodregister_settings['paid_role'] = sanitize_text_field( wp_unslash( $_POST['paid_role'] ) );
				}
				if ( ! empty( $_POST['paid_period'] ) ) {
					$userperiodregister_settings['paid_period'] = intval( $_POST['paid_period'] * 86400 );
				}
				update_option( 'userperiodregister', $userperiodregister_settings );
				echo '<div class="notice notice-success is-dismissible"><ul><li>' . esc_html( __( 'Settings' ) . ' --> ' . __( 'Settings saved.' ) ) . '</li></ul></div>';
			}
		}

	}

	/** ==================================================
	 * Notices
	 *
	 * @since 1.00
	 */
	public function notices() {

		if ( $this->is_my_plugin_screen() ) {
			if ( is_multisite() ) {
				$umor_install_url = network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=user-mail-only-register' );
				$slmc_install_url = network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=software-license-manager-client' );
			} else {
				$umor_install_url = admin_url( 'plugin-install.php?tab=plugin-information&plugin=user-mail-only-register' );
				$slmc_install_url = admin_url( 'plugin-install.php?tab=plugin-information&plugin=software-license-manager-client' );
			}
			$umor_install_html = '<a href="' . $umor_install_url . '" target="_blank" style="text-decoration: none; word-break: break-all;">User Mail Only Register</a>';
			$slmc_install_html = '<a href="' . $slmc_install_url . '" target="_blank" style="text-decoration: none; word-break: break-all;">Software License Manager Client</a>';
			if ( ! class_exists( 'UserMailOnlyRegister' ) ) {
				/* translators: no install message */
				echo '<div class="notice notice-warning is-dismissible"><ul><li>' . wp_kses_post( sprintf( __( 'If you wish to make the registration form mail only, Please use the %1$s.', 'user-period-register' ), $umor_install_html ) ) . '</li></ul></div>';
			}
			if ( ! class_exists( 'SlmClient' ) ) {
				/* translators: %1$s: Software License Manager Client */
				echo '<div class="notice notice-warning is-dismissible"><ul><li>' . wp_kses_post( sprintf( __( 'If you want registered users to charge with %1$s, Please use the %2$s.', 'add-multiple-user' ), __( 'License Key', 'add-multiple-user' ), $slmc_install_html ) ) . '</li></ul></div>';
			}
		}

	}

}


