<?php
/**
 * Mortify Core Admin Settings
 *
 * Provides a top-level admin menu with PWA and UI configuration.
 *
 * @package Mortify\Core
 */

namespace Mortify\Core;

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

class Admin {

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
			'app_url',
			__( 'App URL', 'mortify2026' ),
			[ $this, 'field_app_url' ],
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
			$tabs = array_map( function( $tab ) {
				return [
					'label' => sanitize_text_field( $tab['label'] ?? '' ),
					'icon'  => sanitize_text_field( $tab['icon'] ?? '' ),
					'url'   => esc_url_raw( $tab['url'] ?? '' ),
				];
			}, $input['tabs'] );

			$tabs = array_values(
				array_filter(
					$tabs,
					function( array $tab ): bool {
						return ( '' !== $tab['label'] || '' !== $tab['icon'] || '' !== $tab['url'] );
					}
				)
			);

			$output['tabs'] = ! empty( $tabs ) ? $tabs : mortify_get_settings()['tabs'];
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
	 * Field: App URL.
	 */
	public function field_app_url(): void {
		$app_url = mortify_get_app_base();

		echo '<a href="' . esc_url( $app_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $app_url ) . '</a>';
		echo '<p class="description">' . esc_html__( 'Preview the configured application shell URL.', 'mortify2026' ) . '</p>';
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
		$max_i    = empty( $tabs ) ? -1 : max( array_keys( $tabs ) );
		?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Label', 'mortify2026' ); ?></th>
					<th><?php esc_html_e( 'Icon (emoji or HTML)', 'mortify2026' ); ?></th>
					<th><?php esc_html_e( 'URL', 'mortify2026' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'mortify2026' ); ?></th>
				</tr>
			</thead>
			<tbody id="mortify-tabs-table-body" data-next-index="<?php echo esc_attr( (string) ( $max_i + 1 ) ); ?>">
				<?php foreach ( $tabs as $i => $tab ) : ?>
					<tr>
						<td><input type="text" name="mortify2026_settings[tabs][<?php echo $i; ?>][label]" value="<?php echo esc_attr( $tab['label'] ); ?>"></td>
						<td><input type="text" name="mortify2026_settings[tabs][<?php echo $i; ?>][icon]" value="<?php echo esc_attr( $tab['icon'] ); ?>"></td>
						<td><input type="url" name="mortify2026_settings[tabs][<?php echo $i; ?>][url]" value="<?php echo esc_url( $tab['url'] ); ?>"></td>
						<td><button type="button" class="button button-link-delete mortify-remove-tab"><?php esc_html_e( 'Remove', 'mortify2026' ); ?></button></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p>
			<button type="button" class="button" id="mortify-add-tab"><?php esc_html_e( 'Add Tab', 'mortify2026' ); ?></button>
		</p>
		<p class="description"><?php esc_html_e( 'Add or edit the bottom navigation tabs displayed in the app interface.', 'mortify2026' ); ?></p>
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				const body = document.getElementById('mortify-tabs-table-body');
				const addButton = document.getElementById('mortify-add-tab');

				if (!body || !addButton) {
					return;
				}

				const bindRemove = function(button) {
					button.addEventListener('click', function() {
						const row = button.closest('tr');
						if (row) {
							row.remove();
						}
					});
				};

				body.querySelectorAll('.mortify-remove-tab').forEach(bindRemove);

				addButton.addEventListener('click', function() {
					const nextIndex = Number(body.dataset.nextIndex || '0');
					const row = document.createElement('tr');
					row.innerHTML = `
						<td><input type="text" name="mortify2026_settings[tabs][${nextIndex}][label]" value=""></td>
						<td><input type="text" name="mortify2026_settings[tabs][${nextIndex}][icon]" value=""></td>
						<td><input type="url" name="mortify2026_settings[tabs][${nextIndex}][url]" value=""></td>
						<td><button type="button" class="button button-link-delete mortify-remove-tab"><?php echo esc_js( __( 'Remove', 'mortify2026' ) ); ?></button></td>
					`;

					const removeButton = row.querySelector('.mortify-remove-tab');
					if (removeButton) {
						bindRemove(removeButton);
					}

					body.appendChild(row);
					body.dataset.nextIndex = String(nextIndex + 1);
				});
			});
		</script>
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

class_alias( Admin::class, 'Mortify2026_Admin' );
