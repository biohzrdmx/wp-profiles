<?php
	/**
	 * Plugin Name: Profiles
	 * Description: Allow your users to sign-up to your site easily
	 * Author: biohzrdmx
	 * Version: 1.1
	 * Plugin URI: http://github.com/biohzrdmx/
	 * Author URI: http://github.com/biohzrdmx/wp-profiles
	 */

	if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	if( ! class_exists('Profiles') ) {

		class Profiles {

			public static function init() {
				load_plugin_textdomain('profiles', false, 'wp-profiles/lang');
			}

			public static function actionAdminMenu() {
				add_menu_page('Profiles', 'Profiles', 'manage_options', 'profiles', 'Profiles::callbackAdminPage', 'dashicons-admin-network');
			}

			public static function actionAdminInit() {
				register_setting( 'profiles', 'profiles_options' );
				add_settings_section( 'profiles_settings', __( 'General settings', 'profiles' ), 'Profiles::callbackSettings', 'profiles' );
				add_settings_field( 'profiles_field_page_register', __('Register page', 'profiles'), 'Profiles::fieldPage', 'profiles', 'profiles_settings', [ 'label_for' => 'profiles_field_page_register', 'class' => 'profiles_row' ] );
				add_settings_field( 'profiles_field_page_login', __('Sign-in page', 'profiles'), 'Profiles::fieldPage', 'profiles', 'profiles_settings', [ 'label_for' => 'profiles_field_page_login', 'class' => 'profiles_row' ] );
				add_settings_field( 'profiles_field_page_recover', __('Recover page', 'profiles'), 'Profiles::fieldPage', 'profiles', 'profiles_settings', [ 'label_for' => 'profiles_field_page_recover', 'class' => 'profiles_row' ] );
				add_settings_field( 'profiles_field_page_profile', __('Profile page', 'profiles'), 'Profiles::fieldPage', 'profiles', 'profiles_settings', [ 'label_for' => 'profiles_field_page_profile', 'class' => 'profiles_row' ] );
			}

			public static function adminSettingsLink($links, $file) {
				$links = (array) $links;
				if ( $file === 'wp-profiles/profiles.php' && current_user_can( 'manage_options' ) ) {
					$url = admin_url('admin.php?page=profiles');
					$link = sprintf( '<a href="%s">%s</a>', $url, __( 'Settings', 'profiles' ) );
					array_unshift($links, $link);
				}
				return $links;
			}

			public static function fieldPage($args) {
				global $post;
				$options = get_option( 'profiles_options' );
				$value = esc_html( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' );
				?>
					<select id="<?php echo esc_attr( $args['label_for'] ); ?>" name="profiles_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
						<option value=""><?php echo __( 'Select page', 'profiles' ) ?></option>
						<?php
							$params = array(
								'post_type' => 'page',
								'orderby' => 'title',
								'order' => 'asc',
								'posts_per_page' => -1
							);
							$query = new WP_Query($params);
							while ( $query->have_posts() ):
								$query->the_post();
						?>
							<option value="<?php echo $post->post_name; ?>" <?php echo($value == $post->post_name ? 'selected="selected"' : ''); ?>><?php the_title(); ?></option>
						<?php
							endwhile;
							wp_reset_postdata();
						?>
					</select>
				<?php
			}

			public static function callbackAdminPage() {
				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}
				if ( isset( $_GET['settings-updated'] ) ) {
					add_settings_error( 'profiles_messages', 'profiles_message', __( 'Settings Saved', 'profiles' ), 'updated' );
				}
				settings_errors( 'profiles_messages' );
				?>
					<div class="wrap">
						<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
						<form action="options.php" method="post">
							<?php
							settings_fields( 'profiles' );
							do_settings_sections( 'profiles' );
							submit_button( __('Save Settings', 'profiles') );
							?>
						</form>
					</div>
				<?php
			}

			public static function callbackSettings() {
				?>
					<p><?php echo __('Configure here the plugin parameters.', 'profiles'); ?></p>
				<?php
			}

			public static function handleRegisterPage() {
				global $post;
				#
				if ( get_current_user_id() ) {
					wp_redirect( home_url('/') );
					exit;
				}
				#
				if ($_POST) {
					$email = isset( $_POST['email'] ) ? $_POST['email'] : null;
					$firstname = isset( $_POST['firstname'] ) ? $_POST['firstname'] : null;
					$lastname = isset( $_POST['lastname'] ) ? $_POST['lastname'] : null;
					$password = isset( $_POST['password'] ) ? $_POST['password'] : null;
					$confirm = isset( $_POST['confirm'] ) ? $_POST['confirm'] : null;
					if ($email && $firstname && $lastname && $password && $confirm && $password == $confirm) {
						$userdata = array(
							'user_pass' => $password,
							'user_login' => $email,
							'user_email' => $email,
							'first_name' => $firstname,
							'last_name' => $lastname,
							'role' => 'Pending'
						);
						$user_id = wp_insert_user($userdata);
						if ( $user_id > 0 && !is_wp_error($user_id) ) {
							$checksum = hash_hmac( 'sha256', $user_id, wp_salt('AUTH_KEY') );
							$activate_auth = "{$user_id}.{$checksum}";
							update_user_meta($user_id, 'activate_auth', $activate_auth);
							$link = get_permalink() . "?activate_auth={$activate_auth}";
							#
							$site = get_bloginfo('name');
							$url = get_bloginfo('url');
							$from = 'no-reply@domain.com';
							if ( preg_match('/^(?:https?:\/\/)?(?:www\.)?([^\/]+)/', $url, $matches) == 1 ) {
								$from = "no-reply@{$matches[1]}";
							}
							#
							$params = array(
								'subject' => __('Activate your account', 'profiles') . " | {$site}",
								'from' => array($from => $site),
								'to' => array($email => "{$firstname} {$lastname}"),
								'link' => $link
							);
							do_action('profiles_activate_mailing', (object) $params);
							#
							wp_redirect( get_the_permalink() . '?msg=MSG_ACTIVATE_USER' );
							exit;
						} else {
							wp_redirect( get_the_permalink() . '?msg=ERR_INVALID_USER' );
							exit;
						}
					} else {
						wp_redirect( get_the_permalink() . '?msg=ERR_MISSING_FIELDS' );
						exit;
					}
				}
				#
				$activate_auth = isset( $_GET['activate_auth'] ) ? $_GET['activate_auth'] : null;
				if ($activate_auth) {
					$parts = explode('.', $activate_auth);
					$user_id = isset( $parts[0] ) ? $parts[0] : null;
					$checksum = isset( $parts[1] ) ? $parts[1] : null;
					if ($user_id && $checksum) {
						if ( $checksum && get_user_meta($user_id, 'activate_auth', true) ) {
							$userdata = array(
								'ID' => $user_id,
								'role' => 'subscriber'
							);
							$ret = wp_update_user($userdata);
							#
							wp_set_current_user($user_id);
							wp_set_auth_cookie($user_id, true);
							#
							wp_redirect( self::getPermalink('profile') );
							exit;
						}
					}
				}
			}

			public static function handleSignInPage() {
				global $post;
				#
				if ( get_current_user_id() ) {
					wp_redirect( home_url('/') );
					exit;
				}
				#
				if ($_POST) {
					$email = isset( $_POST['email'] ) ? $_POST['email'] : null;
					$password = isset( $_POST['password'] ) ? $_POST['password'] : null;
					if ($email && $password) {
						$credentials = array(
							'user_login' => $email,
							'user_password' => $password,
							'remember' => true
						);
						$user = wp_signon($credentials);
						if ( $user && !is_wp_error($user) ) {
							$userdata = get_userdata($user->ID);
							if (! $userdata->roles ) {
								wp_logout();
								wp_redirect( get_the_permalink() . '?msg=ERR_INACTIVE_USER' );
							} else {
								wp_redirect( home_url('/') );
							}
						} else {
							wp_redirect( get_the_permalink() . '?msg=ERR_INVALID_CREDENTIALS' );
							exit;
						}
					} else {
						wp_redirect( get_the_permalink() . '?msg=ERR_MISSING_FIELDS' );
						exit;
					}
				}
			}

			public static function handleRecoverPage() {
				global $post;
				#
				if ( get_current_user_id() ) {
					wp_redirect( home_url('/') );
					exit;
				}
				#
				if ($_POST) {
					$email = isset( $_POST['email'] ) ? $_POST['email'] : null;
					if ( $email ) {
						$user = get_user_by( 'login', $email );
						if ($user) {
							$now = time();
							$checksum = hash_hmac( 'sha256', "{$user->ID}{$now}", wp_salt('AUTH_KEY') );
							$recover_auth = "{$user->ID}.{$checksum}";
							update_user_meta($user->ID, 'recover_auth', $recover_auth);
							$link = get_permalink() . "?recover_auth={$recover_auth}";
							#
							#
							$site = get_bloginfo('name');
							$url = get_bloginfo('url');
							$from = 'no-reply@domain.com';
							if ( preg_match('/^(?:https?:\/\/)?(?:www\.)?([^\/]+)/', $url, $matches) == 1 ) {
								$from = "no-reply@{$matches[1]}";
							}
							#
							$params = array(
								'subject' => __('Recover your account', 'profiles') . " | {$site}",
								'from' => array($from => $site),
								'to' => array($user->data->user_email => $user->data->display_name),
								'link' => $link
							);
							do_action('profiles_recover_mailing', (object) $params);
							#
							wp_redirect( get_the_permalink() . '?msg=MSG_RECOVER_USER' );
							exit;
						} else {
							wp_redirect( get_the_permalink() . '?msg=ERR_NOT_SUCH_USER' );
							exit;
						}
					} else {
						wp_redirect( get_the_permalink() . '?msg=ERR_MISSING_FIELDS' );
						exit;
					}
				}
				#
				$recover_auth = isset( $_GET['recover_auth'] ) ? $_GET['recover_auth'] : null;
				if ($recover_auth) {
					$parts = explode('.', $recover_auth);
					$user_id = isset( $parts[0] ) ? $parts[0] : null;
					$checksum = isset( $parts[1] ) ? $parts[1] : null;
					if ($user_id && $checksum) {
						if ( $checksum && get_user_meta($user_id, 'recover_auth', true) ) {
							wp_set_current_user($user_id);
							wp_set_auth_cookie($user_id, true);
							#
							wp_redirect( self::getPermalink('profile') );
							exit;
						}
					}
				}
			}

			public static function handleProfilePage() {
				global $post;
				#
				if (! get_current_user_id() ) {
					wp_redirect( self::getPermalink('login') );
					exit;
				}
				#
				if ($_POST) {
					$email = isset( $_POST['email'] ) ? $_POST['email'] : null;
					$firstname = isset( $_POST['firstname'] ) ? $_POST['firstname'] : null;
					$lastname = isset( $_POST['lastname'] ) ? $_POST['lastname'] : null;
					$password = isset( $_POST['password'] ) ? $_POST['password'] : null;
					$confirm = isset( $_POST['confirm'] ) ? $_POST['confirm'] : null;
					if ( $email && $firstname && $lastname || ($password && $confirm && $password == $confirm) ) {
						$userdata = array(
							'ID' => get_current_user_id(),
							'user_login' => $email,
							'user_email' => $email,
							'first_name' => $firstname,
							'last_name' => $lastname
						);
						#
						$user_id = wp_update_user($userdata);
						#
						if ($password) {
							wp_set_password( $password, $user_id );
							wp_set_current_user($user_id);
							wp_set_auth_cookie($user_id, true);
						}
						#
						wp_redirect( get_the_permalink() . '?msg=MSG_UPDATED_PROFILE' );
						exit;
					} else {
						wp_redirect( get_the_permalink() . '?msg=ERR_MISSING_FIELDS' );
						exit;
					}
				}
			}

			public static function showMessage() {
				$messages = array(
					'ERR_INVALID_CREDENTIALS' => array(
						'type' => 'error',
						'text' => __('Please verify your email and password', 'profiles')
					),
					'ERR_NOT_SUCH_USER' => array(
						'type' => 'error',
						'text' => __('The specified user is not valid', 'profiles')
					),
					'ERR_INVALID_USER' => array(
						'type' => 'error',
						'text' => __('The entered values are not valid or the user already exists', 'profiles')
					),
					'ERR_MISSING_FIELDS' => array(
						'type' => 'error',
						'text' => __('Please fill all the required fields', 'profiles')
					),
					'ERR_INACTIVE_USER' => array(
						'type' => 'error',
						'text' => __('You must activate your user account before signing in', 'profiles')
					),
					'MSG_ACTIVATE_USER' => array(
						'type' => 'error',
						'text' => __('We have sent an email with instructions on how to activate your new account to your inbox' , 'profiles')
					),
					'MSG_RECOVER_USER' => array(
						'type' => 'error',
						'text' => __('We have sent an email with instructions on how to recover your account to your inbox', 'profiles')
					),
					'MSG_UPDATED_PROFILE' => array(
						'type' => 'error',
						'text' => __('Your profile has been updated', 'profiles')
					)
				);
				$msg = isset( $_GET['msg'] ) ? $_GET['msg'] : null;
				if ( $msg && isset( $messages[$msg] ) ) {
					$message = (object) $messages[$msg];
					?>
						<div class="message <?php echo "message-{$message->type}" ?>"><?php echo $message->text; ?></div>
					<?php
				}
			}

			public static function getRegisterFields() {
				$fields = array(
					(object) array(
						'name' => 'email',
						'type' => 'text',
						'label' => __('Email', 'profiles'),
						'value' => ''
					),
					(object) array(
						'name' => 'firstname',
						'type' => 'text',
						'label' => __('First name', 'profiles'),
						'value' => ''
					),
					(object) array(
						'name' => 'lastname',
						'type' => 'text',
						'label' => __('Last name', 'profiles'),
						'value' => ''
					),
					(object) array(
						'name' => 'password',
						'type' => 'password',
						'label' => __('Password', 'profiles'),
						'value' => ''
					),
					(object) array(
						'name' => 'confirm',
						'type' => 'password',
						'label' => __('Confirm password', 'profiles'),
						'value' => ''
					)
				);
				return $fields;
			}

			public static function getSignInFields() {
				$fields = array(
					(object) array(
						'name' => 'email',
						'type' => 'text',
						'label' => __('Email', 'profiles'),
						'value' => ''
					),
					(object) array(
						'name' => 'password',
						'type' => 'password',
						'label' => __('Password', 'profiles'),
						'value' => ''
					)
				);
				return $fields;
			}

			public static function getRecoverFields() {
				$fields = array(
					(object) array(
						'name' => 'email',
						'type' => 'text',
						'label' => __('Email', 'profiles'),
						'value' => ''
					)
				);
				return $fields;
			}

			public static function getProfileFields() {
				$current_user = wp_get_current_user();
				$fields = array(
					(object) array(
						'name' => 'login',
						'type' => 'text',
						'label' => __('User name', 'profile'),
						'value' => htmlspecialchars($current_user->user_login)
					),
					(object) array(
						'name' => 'email',
						'type' => 'text',
						'label' => __('Email', 'profile'),
						'value' => htmlspecialchars($current_user->user_email)
					),
					(object) array(
						'name' => 'firstname',
						'type' => 'text',
						'label' => __('First name', 'profile'),
						'value' => htmlspecialchars($current_user->user_firstname)
					),
					(object) array(
						'name' => 'lastname',
						'type' => 'text',
						'label' => __('Last name', 'profile'),
						'value' => htmlspecialchars($current_user->user_lastname)
					),
					(object) array(
						'name' => 'displayname',
						'type' => 'text',
						'label' => __('Display name', 'profile'),
						'value' => htmlspecialchars($current_user->display_name)
					),
					(object) array(
						'name' => 'password',
						'type' => 'password',
						'label' => __('New password', 'profile'),
						'value' => ''
					),
					(object) array(
						'name' => 'confirm',
						'type' => 'password',
						'label' => __('Confirm password', 'profile'),
						'value' => ''
					),
				);
				return $fields;
			}

			public static function getPermalink($page) {
				$ret = '/';
				$options = get_option( 'profiles_options' );
				if ( isset( $options["profiles_field_page_{$page}"] ) ) {
					$ret = $options["profiles_field_page_{$page}"];
				}
				return home_url( $ret );
			}
		}
	}

	add_action( 'init', 'Profiles::init' );
	add_action( 'wp_head', 'Profiles::actionHead' );
	add_action( 'admin_init', 'Profiles::actionAdminInit' );
	add_action( 'admin_menu', 'Profiles::actionAdminMenu' );
	add_filter( 'plugin_action_links', 'Profiles::adminSettingsLink', 10, 5 );

?>