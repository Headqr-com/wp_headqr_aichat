<?php
/**
 * Plugin Name: HeadQR AI Chat
 * Description: Displays the HeadQR chat widget for an Assistant you set at headqr.com. Remember you need to add this domain name in the allowed domains field of your assistant there.
 * Version: 1.0.1
 * Author: HeadQR
 * Author URI: https://www.headqr.com
 * License: GPL-2.0-or-later
 * Text Domain: headqr-aichat
 */

if (!defined('ABSPATH')) {
	exit;
}

final class Headqr_Aichat_Plugin
{
	private const OPTION_KEY = 'headqr_aichat_options';
	private const SCRIPT_URL = 'https://www.headqr.com/media/com_headqr/js/chat-widget.min.js';

	/**
	 * Bootstrap hooks.
	 *
	 * @return void
	 */
	public static function init(): void
	{
		add_action('admin_init', [__CLASS__, 'register_settings']);
		add_action('admin_menu', [__CLASS__, 'register_admin_page']);
		add_action('wp_footer', [__CLASS__, 'render_widget'], 100);
	}

	/**
	 * Minimal local i18n map (en/es) for admin UI labels.
	 *
	 * @return array<string, string>
	 */
	private static function i18n(): array
	{
		$lang = strtolower((string) get_locale());
		$is_fr = strpos($lang, 'fr_') === 0 || $lang === 'fr';
		$is_es = strpos($lang, 'es_') === 0 || $lang === 'es';

		if ($is_fr) {
			return [
				'page_title' => 'HeadQR AI Chat',
				'page_intro' => 'Affiche le widget de chat HeadQR pour un assistant configure sur headqr.com.',
				'user_id' => 'ID Utilisateur',
				'assistant_id' => 'ID Assistant',
				'button_color_1' => 'Couleur Bouton 1',
				'button_color_2' => 'Couleur Bouton 2',
				'widget_language' => 'Langue du Widget',
				'lang_auto' => 'Auto (langue navigateur/page)',
				'lang_fr' => 'Francais (fr-FR)',
				'lang_en' => 'Anglais (en-GB)',
				'lang_de' => 'Deutsch (de-DE)',
				'lang_es' => 'Espagnol (es-ES)',
				'lang_it' => 'Italiano (it-IT)',
				'lang_ja' => 'Japanese (ja-JP)',
				'lang_ko' => 'Korean (ko-KR)',
				'lang_nl' => 'Nederlands (nl-NL)',
				'lang_pt' => 'Portugues (pt-PT)',
				'lang_ru' => 'Russian (ru-RU)',
				'lang_zh' => 'Chinese (zh-CN)',
				'horizontal_alignment' => 'Alignement horizontal',
				'left' => 'Gauche',
				'right' => 'Droite',
				'bottom_offset' => 'Distance bord bas (px)',
				'horizontal_offset' => 'Distance bord horizontal (px)',
				'button_z_index' => 'Z-index bouton',
			];
		}

		if ($is_es) {
			return [
				'page_title' => 'HeadQR AI Chat',
				'page_intro' => 'Muestra el widget de chat de HeadQR para un asistente configurado en headqr.com.',
				'user_id' => 'ID de Usuario',
				'assistant_id' => 'ID de Asistente',
				'button_color_1' => 'Color Boton 1',
				'button_color_2' => 'Color Boton 2',
				'widget_language' => 'Idioma del Widget',
				'lang_auto' => 'Auto (idioma del navegador/pagina)',
				'lang_fr' => 'Frances (fr-FR)',
				'lang_en' => 'Ingles (en-GB)',
				'lang_de' => 'Aleman (de-DE)',
				'lang_es' => 'Espanol (es-ES)',
				'lang_it' => 'Italiano (it-IT)',
				'lang_ja' => 'Japones (ja-JP)',
				'lang_ko' => 'Coreano (ko-KR)',
				'lang_nl' => 'Neerlandes (nl-NL)',
				'lang_pt' => 'Portugues (pt-PT)',
				'lang_ru' => 'Ruso (ru-RU)',
				'lang_zh' => 'Chino (zh-CN)',
				'horizontal_alignment' => 'Alineacion horizontal',
				'left' => 'Izquierda',
				'right' => 'Derecha',
				'bottom_offset' => 'Distancia inferior (px)',
				'horizontal_offset' => 'Distancia horizontal (px)',
				'button_z_index' => 'Z-index boton',
			];
		}

		return [
			'page_title' => 'HeadQR AI Chat',
			'page_intro' => 'Displays the HeadQR chat widget for an assistant set at headqr.com.',
			'user_id' => 'User ID',
			'assistant_id' => 'Assistant ID',
			'button_color_1' => 'Button Color 1',
			'button_color_2' => 'Button Color 2',
			'widget_language' => 'Widget Language',
			'lang_auto' => 'Auto (browser/page language)',
			'lang_fr' => 'French (fr-FR)',
			'lang_en' => 'English (en-GB)',
			'lang_de' => 'Deutsch (de-DE)',
			'lang_es' => 'Spanish (es-ES)',
			'lang_it' => 'Italiano (it-IT)',
			'lang_ja' => 'Japanese (ja-JP)',
			'lang_ko' => 'Korean (ko-KR)',
			'lang_nl' => 'Nederlands (nl-NL)',
			'lang_pt' => 'Portugues (pt-PT)',
			'lang_ru' => 'Russian (ru-RU)',
			'lang_zh' => 'Chinese (zh-CN)',
			'horizontal_alignment' => 'Horizontal alignment',
			'left' => 'Left',
			'right' => 'Right',
			'bottom_offset' => 'Bottom offset (px)',
			'horizontal_offset' => 'Horizontal offset (px)',
			'button_z_index' => 'Button z-index',
		];
	}

	/**
	 * Returns normalized options, with the same runtime fallbacks as the Joomla template.
	 *
	 * @return array<string, mixed>
	 */
	private static function get_options(): array
	{
		$raw = get_option(self::OPTION_KEY, []);
		if (!is_array($raw)) {
			$raw = [];
		}

		$user_id = isset($raw['data_user_id']) ? (int) $raw['data_user_id'] : 0;
		$assistant_id = isset($raw['data_assistant_id']) ? (int) $raw['data_assistant_id'] : 0;
		$color1 = isset($raw['data_color1']) ? sanitize_text_field((string) $raw['data_color1']) : '#28d252';
		$color2 = isset($raw['data_color2']) ? sanitize_text_field((string) $raw['data_color2']) : '#25ad27';
		$lang = isset($raw['data_lang']) ? trim(sanitize_text_field((string) $raw['data_lang'])) : '';
		$btn_align = isset($raw['data_btn_align']) ? strtolower(trim(sanitize_text_field((string) $raw['data_btn_align']))) : 'right';
		$btn_offset_bottom = isset($raw['data_btn_offset_bottom']) ? (int) $raw['data_btn_offset_bottom'] : 24;
		$btn_offset_horizontal = isset($raw['data_btn_offset_horizontal']) ? (int) $raw['data_btn_offset_horizontal'] : 24;
		$btn_zindex = isset($raw['data_btn_zindex']) ? (int) $raw['data_btn_zindex'] : 1110;

		if (!self::is_hex_color($color1)) {
			$color1 = '#28d252';
		}
		if (!self::is_hex_color($color2)) {
			$color2 = '#25ad27';
		}
		if ($btn_align !== 'left' && $btn_align !== 'right') {
			$btn_align = 'right';
		}
		if ($btn_offset_bottom < 0) {
			$btn_offset_bottom = 24;
		}
		if ($btn_offset_horizontal < 0) {
			$btn_offset_horizontal = 24;
		}
		if ($btn_zindex < 1) {
			$btn_zindex = 1110;
		}

		return [
			'data_user_id' => $user_id,
			'data_assistant_id' => $assistant_id,
			'data_color1' => $color1,
			'data_color2' => $color2,
			'data_lang' => $lang,
			'data_btn_align' => $btn_align,
			'data_btn_offset_bottom' => $btn_offset_bottom,
			'data_btn_offset_horizontal' => $btn_offset_horizontal,
			'data_btn_zindex' => $btn_zindex,
		];
	}

	/**
	 * Render the widget container and remote script.
	 *
	 * @return void
	 */
	public static function render_widget(): void
	{
		if (is_admin()) {
			return;
		}

		$options = self::get_options();
		$lang_attribute = '';
		if ($options['data_lang'] !== '') {
			$lang_attribute = ' data-lang="' . esc_attr($options['data_lang']) . '"';
		}

		echo '<div id="headqr-chat-widget-container"'
			. ' data-user-id="' . (int) $options['data_user_id'] . '"'
			. ' data-assistant-id="' . (int) $options['data_assistant_id'] . '"'
			. ' data-btn-bg-color1="' . esc_attr($options['data_color1']) . '"'
			. ' data-btn-bg-color2="' . esc_attr($options['data_color2']) . '"'
			. ' data-btn-align="' . esc_attr($options['data_btn_align']) . '"'
			. ' data-btn-offset-bottom="' . (int) $options['data_btn_offset_bottom'] . '"'
			. ' data-btn-offset-horizontal="' . (int) $options['data_btn_offset_horizontal'] . '"'
			. ' data-btn-z-index="' . (int) $options['data_btn_zindex'] . '"'
			. $lang_attribute
			. '></div>' . "\n";
		echo '<script src="' . esc_url(self::SCRIPT_URL) . '" type="text/javascript"></script>' . "\n";
	}

	/**
	 * Register settings.
	 *
	 * @return void
	 */
	public static function register_settings(): void
	{
		register_setting(
			'headqr_aichat_settings_group',
			self::OPTION_KEY,
			[
				'type' => 'array',
				'sanitize_callback' => [__CLASS__, 'sanitize_options'],
				'default' => [
					'data_user_id' => 0,
					'data_assistant_id' => 0,
					'data_color1' => '#28d252',
					'data_color2' => '#25ad27',
					'data_lang' => '',
					'data_btn_align' => 'right',
					'data_btn_offset_bottom' => 12,
					'data_btn_offset_horizontal' => 12,
					'data_btn_zindex' => 1110,
				],
			]
		);
	}

	/**
	 * Sanitize options before save.
	 *
	 * @param mixed $input Raw option payload.
	 *
	 * @return array<string, mixed>
	 */
	public static function sanitize_options($input): array
	{
		$input = is_array($input) ? $input : [];
		$lang_options = ['', 'fr-FR', 'en-GB', 'de-DE', 'es-ES', 'it-IT', 'ja-JP', 'ko-KR', 'nl-NL', 'pt-PT', 'ru-RU', 'zh-CN'];

		$color1 = isset($input['data_color1']) ? sanitize_text_field((string) $input['data_color1']) : '#28d252';
		if (!self::is_hex_color($color1)) {
			$color1 = '#28d252';
		}

		$color2 = isset($input['data_color2']) ? sanitize_text_field((string) $input['data_color2']) : '#25ad27';
		if (!self::is_hex_color($color2)) {
			$color2 = '#25ad27';
		}

		$data_lang = isset($input['data_lang']) ? sanitize_text_field((string) $input['data_lang']) : '';
		if (!in_array($data_lang, $lang_options, true)) {
			$data_lang = '';
		}

		$data_btn_align = isset($input['data_btn_align']) ? strtolower(trim(sanitize_text_field((string) $input['data_btn_align']))) : 'right';
		if (!in_array($data_btn_align, ['left', 'right'], true)) {
			$data_btn_align = 'right';
		}

		$data_btn_offset_bottom = isset($input['data_btn_offset_bottom']) ? (int) $input['data_btn_offset_bottom'] : 12;
		if ($data_btn_offset_bottom < 0) {
			$data_btn_offset_bottom = 12;
		}

		$data_btn_offset_horizontal = isset($input['data_btn_offset_horizontal']) ? (int) $input['data_btn_offset_horizontal'] : 12;
		if ($data_btn_offset_horizontal < 0) {
			$data_btn_offset_horizontal = 12;
		}

		$data_btn_zindex = isset($input['data_btn_zindex']) ? (int) $input['data_btn_zindex'] : 1110;
		if ($data_btn_zindex < 1) {
			$data_btn_zindex = 1110;
		}

		return [
			'data_user_id' => isset($input['data_user_id']) ? max(0, (int) $input['data_user_id']) : 0,
			'data_assistant_id' => isset($input['data_assistant_id']) ? max(0, (int) $input['data_assistant_id']) : 0,
			'data_color1' => $color1,
			'data_color2' => $color2,
			'data_lang' => $data_lang,
			'data_btn_align' => $data_btn_align,
			'data_btn_offset_bottom' => $data_btn_offset_bottom,
			'data_btn_offset_horizontal' => $data_btn_offset_horizontal,
			'data_btn_zindex' => $data_btn_zindex,
		];
	}

	/**
	 * Register admin page.
	 *
	 * @return void
	 */
	public static function register_admin_page(): void
	{
		add_options_page(
			'HeadQR AI Chat',
			'HeadQR AI Chat',
			'manage_options',
			'headqr-aichat',
			[__CLASS__, 'render_admin_page']
		);
	}

	/**
	 * Render admin options page.
	 *
	 * @return void
	 */
	public static function render_admin_page(): void
	{
		if (!current_user_can('manage_options')) {
			return;
		}

		$options = self::get_options();
		$i18n = self::i18n();
		?>
		<div class="wrap">
			<h1><?php echo esc_html($i18n['page_title']); ?></h1>
			<p><?php echo esc_html($i18n['page_intro']); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields('headqr_aichat_settings_group'); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="headqr_data_user_id"><?php echo esc_html($i18n['user_id']); ?></label></th>
						<td><input id="headqr_data_user_id" name="<?php echo esc_attr(self::OPTION_KEY); ?>[data_user_id]" type="number" min="0" value="<?php echo (int) $options['data_user_id']; ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label for="headqr_data_assistant_id"><?php echo esc_html($i18n['assistant_id']); ?></label></th>
						<td><input id="headqr_data_assistant_id" name="<?php echo esc_attr(self::OPTION_KEY); ?>[data_assistant_id]" type="number" min="0" value="<?php echo (int) $options['data_assistant_id']; ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label for="headqr_data_color1"><?php echo esc_html($i18n['button_color_1']); ?></label></th>
						<td><input id="headqr_data_color1" name="<?php echo esc_attr(self::OPTION_KEY); ?>[data_color1]" type="text" value="<?php echo esc_attr($options['data_color1']); ?>" class="regular-text" placeholder="#28d252"></td>
					</tr>
					<tr>
						<th scope="row"><label for="headqr_data_color2"><?php echo esc_html($i18n['button_color_2']); ?></label></th>
						<td><input id="headqr_data_color2" name="<?php echo esc_attr(self::OPTION_KEY); ?>[data_color2]" type="text" value="<?php echo esc_attr($options['data_color2']); ?>" class="regular-text" placeholder="#25ad27"></td>
					</tr>
					<tr>
						<th scope="row"><label for="headqr_data_lang"><?php echo esc_html($i18n['widget_language']); ?></label></th>
						<td>
							<select id="headqr_data_lang" name="<?php echo esc_attr(self::OPTION_KEY); ?>[data_lang]">
								<?php
								$languages = [
									'' => $i18n['lang_auto'],
									'fr-FR' => $i18n['lang_fr'],
									'en-GB' => $i18n['lang_en'],
									'de-DE' => $i18n['lang_de'],
									'es-ES' => $i18n['lang_es'],
									'it-IT' => $i18n['lang_it'],
									'ja-JP' => $i18n['lang_ja'],
									'ko-KR' => $i18n['lang_ko'],
									'nl-NL' => $i18n['lang_nl'],
									'pt-PT' => $i18n['lang_pt'],
									'ru-RU' => $i18n['lang_ru'],
									'zh-CN' => $i18n['lang_zh'],
								];
								foreach ($languages as $code => $label) {
									printf(
										'<option value="%1$s" %3$s>%2$s</option>',
										esc_attr($code),
										esc_html($label),
										selected($options['data_lang'], $code, false)
									);
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html($i18n['horizontal_alignment']); ?></th>
						<td>
							<label><input type="radio" name="<?php echo esc_attr(self::OPTION_KEY); ?>[data_btn_align]" value="left" <?php checked($options['data_btn_align'], 'left'); ?>> <?php echo esc_html($i18n['left']); ?></label>
							<label style="margin-left:16px;"><input type="radio" name="<?php echo esc_attr(self::OPTION_KEY); ?>[data_btn_align]" value="right" <?php checked($options['data_btn_align'], 'right'); ?>> <?php echo esc_html($i18n['right']); ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="headqr_data_btn_offset_bottom"><?php echo esc_html($i18n['bottom_offset']); ?></label></th>
						<td><input id="headqr_data_btn_offset_bottom" name="<?php echo esc_attr(self::OPTION_KEY); ?>[data_btn_offset_bottom]" type="number" min="0" value="<?php echo (int) $options['data_btn_offset_bottom']; ?>" class="small-text"></td>
					</tr>
					<tr>
						<th scope="row"><label for="headqr_data_btn_offset_horizontal"><?php echo esc_html($i18n['horizontal_offset']); ?></label></th>
						<td><input id="headqr_data_btn_offset_horizontal" name="<?php echo esc_attr(self::OPTION_KEY); ?>[data_btn_offset_horizontal]" type="number" min="0" value="<?php echo (int) $options['data_btn_offset_horizontal']; ?>" class="small-text"></td>
					</tr>
					<tr>
						<th scope="row"><label for="headqr_data_btn_zindex"><?php echo esc_html($i18n['button_z_index']); ?></label></th>
						<td><input id="headqr_data_btn_zindex" name="<?php echo esc_attr(self::OPTION_KEY); ?>[data_btn_zindex]" type="number" min="1" value="<?php echo (int) $options['data_btn_zindex']; ?>" class="small-text"></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Validate hex color.
	 *
	 * @param string $value Input color.
	 *
	 * @return bool
	 */
	private static function is_hex_color(string $value): bool
	{
		return (bool) preg_match('/^#(?:[A-Fa-f0-9]{3}){1,2}$/', $value);
	}
}

Headqr_Aichat_Plugin::init();
