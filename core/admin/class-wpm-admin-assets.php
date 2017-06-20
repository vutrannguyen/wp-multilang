<?php
/**
 * Load assets
 *
 * @author      VaLeXaR
 * @category    Admin
 * @package     WPM/Core/Admin
 * @class       WPM_Admin_Assets
 */

namespace WPM\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPM_Admin_Assets Class.
 */
class WPM_Admin_Assets {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Enqueue styles.
	 */
	public function admin_styles() {

		// Register admin styles
		wp_register_style( 'wpm_language_switcher', wpm_asset_path( 'styles/admin/admin.css' ), array(), WPM_VERSION );
	}


	/**
	 * Enqueue scripts.
	 */
	public function admin_scripts() {

		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		$suffix    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$config    = wpm_get_config();

		// Register scripts
		wp_register_script( 'wpm_main', wpm_asset_path( 'scripts/main' . $suffix . '.js' ), array(
			'jquery-ui-sortable'
		), WPM_VERSION );

		$main_params = array(
			'plugin_url'        => WPM()->plugin_url(),
			'ajax_url'          => admin_url( 'admin-ajax.php' ),
			'delete_lang_nonce' => wp_create_nonce( 'delete-lang' ),
			'confirm_question'  => __( 'Are you sure you want to delete this language?', 'wpm' ),
		);
		wp_localize_script( 'wpm_main', 'wpm_params', $main_params );

		wp_register_script( 'wpm_language_switcher', wpm_asset_path( 'scripts/language-switcher' . $suffix . '.js' ), array(
			'jquery',
			'underscore',
		), WPM_VERSION );
		wp_register_script( 'wpm_language_switcher_customizer', wpm_asset_path( 'scripts/customizer' . $suffix . '.js' ), array(
			'jquery',
			'underscore',
		), WPM_VERSION );

		wp_register_script( 'wpm_translator', wpm_asset_path( 'scripts/translator' . $suffix . '.js' ), array( 'jquery' ), WPM_VERSION );

		$translator_params = array(
			'languages'        => array_values( wpm_get_languages() ),
			'default_language' => wpm_get_languages()[ wpm_get_default_locale() ],
			'language'         => wpm_get_language(),
		);
		wp_localize_script( 'wpm_translator', 'wpm_translator_params', $translator_params );

		if ( 'customize' === $screen_id ) {
			wp_enqueue_style( 'wpm_language_switcher' );
			wp_enqueue_script( 'wpm_language_switcher_customizer' );
			$params = array(
				'switcher' => wpm_get_template_html( 'language-switcher-customizer.php' ),
			);
			wp_localize_script( 'wpm_language_switcher_customizer', 'wpm_language_switcher_params', $params );
		}

		$admin_pages_config = apply_filters( 'wpm_admin_pages', $config['admin_pages'] );

		if ( in_array( $screen_id, $admin_pages_config, true ) ) {
			$this->set_language_switcher();
		}

		$posts_config = $config['post_types'];
		$posts_config = apply_filters( 'wpm_posts_config', $posts_config );
		if ( ! is_null( $screen ) ) {
			$posts_config[ $screen->post_type ] = apply_filters( "wpm_posts_{$screen->post_type}_config", isset( $posts_config[ $screen->post_type ] ) ? $posts_config[ $screen->post_type ] : null );
		}

		if ( ! is_null( $screen ) && isset( $posts_config[ $screen->post_type ] ) && ! is_null( $posts_config [ $screen->post_type ] ) ) {
			$this->set_language_switcher();
		}

		$taxonomies_config = $config['taxonomies'];
		$taxonomies_config = apply_filters( 'wpm_taxonomies_config', $taxonomies_config );
		if ( ! is_null( $screen ) ) {
			$taxonomies_config[ $screen->taxonomy ] = apply_filters( "wpm_taxonomy_{$screen->taxonomy}_config", isset( $taxonomies_config[ $screen->taxonomy ] ) ? $taxonomies_config[ $screen->taxonomy ] : null );
		}

		if ( ! is_null( $screen ) && isset( $taxonomies_config[ $screen->taxonomy ] ) && ! is_null( $taxonomies_config[ $screen->taxonomy ] ) ) {
			$this->set_language_switcher();
		}

		if ( 'options-general' === $screen_id ) {
			wp_enqueue_script( 'wpm_main' );
		}
	}

	/**
	 * Display language switcher for edit posts, taxonomies, options
	 */
	public function set_language_switcher() {
		wp_enqueue_style( 'wpm_language_switcher' );
		wp_enqueue_script( 'wpm_language_switcher' );
		$params = array(
			'switcher' => wpm_get_template_html( 'language-switcher.php' ),
		);
		wp_localize_script( 'wpm_language_switcher', 'wpm_language_switcher_params', $params );
		add_action('admin_head', function() {
			?>
			<style>
				#wpbody-content .wrap {
					padding-top: 37px;
				}
			</style>
			<?php
		});
	}
}
