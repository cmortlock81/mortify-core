<?php
/**
 * Mortify 2026 Admin Settings
 *
 * Provides a top-level admin menu with PWA and UI configuration.
 *
 * @package Mortify2026
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Mortify2026_Admin {

	/**
	 * Hook into admin actions.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * Add top-level admin menu for Mortify 2026.
	 *
	 * @return void
	 */
	public function register_menu(): void {
		add_menu_page(
			__( 'Mortify 2026', 'mortify2026' ),
			__( 'Mortify 2026', 'mortify2026' ),
			'manage_options',
			'mortify2026-settings',
			[ $this, 'render_settings_page' ],
			'dashicons-smartphone',
			3
		);
	}

	/**
	 * Register settings and fields using the Settings API.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			'mortify2026_settings_group',
			'mortify2026_settings',
			[ 'sanitize_callback' => [ $this, 'sanitize' ] ]
		);

		add_settings_section(
			'mortify_general_section',
			__( 'General Settings', 'mortify2026' ),
			function() {
				echo '<p>' . esc_html__( 'Configure Mortify 2026 PWA and app shell options.', 'mortify2026' ) . '</p>';
			},
			'mortify2026-settings'
		);

		add_settings_field(
			'app_slug',
			__( 'App Slug', 'mortify2026' ),
			[ $this, 'field_app_slug' ],
			'mortify2026-settings',
			'mortify_general_section'
		);

		add_settings_field(
			'primary_color',
			__( 'Primary Color', 'mortify2026' ),
			[ $this, 'field_primary_color' ],
			'mortify2026-settings',
			'mortify_general_section'
		);

		add_settings_field(
			'accent_color',
			__( 'Accent Color', 'mortify2026' ),
			[ $this, 'field_accent_color' ],
			'mortify2026-settings',
			'mortify_general_section'
		);

		add_settings_field(
			'app_tabs',
			__( 'App Tabs', 'mortify2026' ),
			[ $this, 'field_tabs' ],
			'mortify2026-settings',
			'mortify_general_section'
		);
	}

	/**
	 * Sanitize incoming values before saving.
	 *
	 * @param array $input Raw input.
	 * @return array Sanitized data.
	 */
	public function sanitize( array $input ): array {
		$output = mortify_get_settings();

		if ( isset( $input['app_slug'] ) ) {
			$output['app_slug'] = sanitize_title( $input['app_slug'] );
		}
		if ( isset( $input['brand']['primary'] ) ) {
			$output['brand']['primary'] = sanitize_hex_color( $input['brand']['primary'] );
		}
		if ( isset( $input['brand']['accent'] ) ) {
			$output['brand']['accent'] = sanitize_hex_color( $input['brand']['accent'] );
		}
		if ( isset( $input['tabs'] ) && is_array( $input['tabs'] ) ) {
			$output['tabs'] = array_map( function( $tab ) {
				return [
					'label' => sanitize_text_field( $tab['label'] ?? '' ),
					'icon'  => sanitize_text_field( $tab['icon'] ?? '' ),
					'url'   => esc_url_raw( $tab['url'] ?? '' ),
				];
			}, $input['tabs'] );
		}

		return $output;
	}

	/**
	 * Field: App Slug.
	 */
	public function field_app_slug(): void {
		$settings = mortify_get_settings();
		echo '<input type="text" name="mortify2026_settings[app_slug]" value="' . esc_attr( $settings['app_slug'] ) . '" class="regular-text">';
		echo '<p class="description">' . esc_html__( 'The URL slug for the app shell (e.g., "app").', 'mortify2026' ) . '</p>';
	}

	/**
	 * Field: Primary Color.
	 */
	public function field_primary_color(): void {
		$settings = mortify_get_settings();
		echo '<input type="color" name="mortify2026_settings[brand][primary]" value="' . esc_attr( $settings['brand']['primary'] ) . '">';
	}

	/**
	 * Field: Accent Color.
	 */
	public function field_accent_color(): void {
		$settings = mortify_get_settings();
		echo '<input type="color" name="mortify2026_settings[brand][accent]" value="' . esc_attr( $settings['brand']['accent'] ) . '">';
	}

	/**
	 * Field: App Tabs.
	 */
	public function field_tabs(): void {
		$settings = mortify_get_settings();
		$tabs     = $settings['tabs'];
		?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Label', 'mortify2026' ); ?></th>
					<th><?php esc_html_e( 'Icon (emoji or HTML)', 'mortify2026' ); ?></th>
					<th><?php esc_html_e( 'URL', 'mortify2026' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $tabs as $i => $tab ) : ?>
					<tr>
						<td><input type="text" name="mortify2026_settings[tabs][<?php echo $i; ?>][label]" value="<?php echo esc_attr( $tab['label'] ); ?>"></td>
						<td><input type="text" name="mortify2026_settings[tabs][<?php echo $i; ?>][icon]" value="<?php echo esc_attr( $tab['icon'] ); ?>"></td>
						<td><input type="url" name="mortify2026_settings[tabs][<?php echo $i; ?>][url]" value="<?php echo esc_url( $tab['url'] ); ?>"></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p class="description"><?php esc_html_e( 'Add or edit the bottom navigation tabs displayed in the app interface.', 'mortify2026' ); ?></p>
		<?php
	}

	/**
	 * Render the settings page content.
	 */
	public function render_settings_page(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Mortify 2026 Settings', 'mortify2026' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'mortify2026_settings_group' );
				do_settings_sections( 'mortify2026-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
