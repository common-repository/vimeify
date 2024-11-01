<?php
/********************************************************************
 * Copyright (C) 2024 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2024 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
 *
 * This file is property of IDEOLOGIX MEDIA Dooel (https://ideologix.com)
 * This file is part of Vimeify Plugin - https://wordpress.org/plugins/vimeify/
 *
 * Vimeify - Formerly "WP Vimeo Videos" is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * Vimeify - Formerly "WP Vimeo Videos" is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this plugin. If not, see <https://www.gnu.org/licenses/>.
 *
 * Code developed by Darko Gjorgjijoski <dg@darkog.com>.
 **********************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* @var \Vimeify\Core\Plugin $plugin
 * @var int $video_id
 * @var int $vimeo_id
 * @var int $front_pages
 * @var int $folders_management
 * @var int $embed_presets_management
 * @var int $embed_privacy_management
 * @var \Vimeify\Core\Utilities\Formatters\VimeoFormatter $vimeo_formatter
 */
?>

<div class="vimeify-pedit">
	<?php if ( ! $plugin->system()->vimeo()->is_connected ): ?>
        <p><?php esc_html_e( 'Please enter valid api credentails.', 'vimeify' ); ?></p>
	<?php elseif ( ! $plugin->system()->vimeo()->can_edit() ): ?>
        <p><?php esc_html_e( 'Edit scope is missing. Please request Edit scope for your access token in the Vimeo Developer Tools in order to be able to edit videos', 'vimeify' ); ?></p>
	<?php else: ?>
		<?php
		// Gather data
		$video = array();
		try {
			$video = $plugin->system()->vimeo()->get_video_by_local_id( $video_id, array(
				'uri',
				'name',
				'description',
				'link',
				'duration',
				'width',
				'height',
				'is_playable',
				'privacy',
				'embed',
				'parent_folder',
                'content_rating',
				'upload'
			) );
		} catch ( \Exception $e ) {
		}

		$view_privacy_opts = $plugin->system()->vimeo()->get_view_privacy_options_for_forms( 'admin' );

		$embed_preset_uri = isset( $video['body']['embed']['uri'] ) && ! empty( $video['body']['embed']['uri'] ) ? $video['body']['embed']['uri'] : null; //eg. /presets/120554271
		$folder_uri       = isset( $video['body']['parent_folder']['uri'] ) && ! empty( $video['body']['parent_folder']['uri'] ) ? $video['body']['parent_folder']['uri'] : null; //eg. /users/120624714/projects/2801250
		$link             = get_post_meta( $video_id, 'vimeify_embed_link', true );
		if ( empty( $link ) ) {
			$link = sprintf( 'https://player.vimeo.com/%s', $vimeo_id );
		}
		?>

		<?php do_action( 'vimeify_video_edit_top', $plugin, $video, $video_id ); ?>

        <div class="vimeify-pedit-section">
            <input type="hidden" name="video_uri" value="<?php echo esc_attr( $video['body']['uri'] ); ?>">
            <div class="vimeify-pedit-preview">
                <div class='vimeify-embed-container'>
                    <iframe id="vimeify-video-preview" src='<?php echo esc_url( $link ); ?>' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
                </div>
            </div>
            <div class="vimeify-pedit-description">
                <div class="vimeify-pedit-section">
                    <h4><?php esc_html_e( 'Basic Details', 'vimeify' ); ?></h4>
                    <div class="vimeify-pedit-section--fields">
                        <div class="vimeify-pedit-section--row">
                            <label for="video_name"><?php esc_html_e( 'Name', 'vimeify' ); ?></label>
                            <input type="text" name="video_name" id="video_name" value="<?php echo esc_attr( wp_unslash( $video['body']['name'] ) ); ?>" autocomplete="off">
                        </div>
                        <div class="vimeify-pedit-section--row">
                            <label for="video_description"><?php esc_html_e( 'Description', 'vimeify' ); ?></label>
                            <textarea name="video_description" id="video_description" rows="4"><?php echo esc_attr( wp_unslash( $video['body']['description'] ) ); ?></textarea>
                        </div>
						<?php do_action( 'vimeify_video_edit_basic_section', $plugin, $video, $video_id ); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="vimeify-pedit-section">
            <h4><?php esc_html_e( 'Privacy Settings', 'vimeify' ); ?></h4>
            <div class="vimeify-pedit-section--fields">
                <div class="vimeify-pedit-section--row">
                    <label for="view_privacy"><?php esc_html_e( 'View Privacy', 'vimeify' ); ?></label>
                    <select name="view_privacy" id="view_privacy">
						<?php foreach ( $view_privacy_opts as $key => $option ): ?><?php
							$option_state = isset( $video['body']['privacy']['view'] ) && $video['body']['privacy']['view'] === $key ? ' selected ' : '';
							$option_state .= $option['available'] ? '' : ' disabled';
							?>
                            <option <?php echo esc_attr( $option_state ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $option['name'] ); ?></option>
						<?php endforeach; ?>
                    </select>
                </div>
                <div class="vimeify-pedit-section--row">
                    <label for="privacy_embed"><?php esc_html_e( 'Embed privacy type', 'vimeify' ); ?></label>
                    <select id="privacy_embed" name="privacy_embed" data-target=".vimeify-embed-privacy-whitelist" data-show-target-if-value="whitelist" class="vimeify-conditional-field">
                        <option value="public" <?php selected( $video['body']['privacy']['embed'], 'public' ); ?>><?php esc_html_e( 'Public', 'vimeify' ); ?></option>
                        <option value="whitelist" <?php selected( $video['body']['privacy']['embed'], 'whitelist' ); ?>><?php esc_html_e( 'Specific domains', 'vimeify' ); ?></option>
                    </select>
                    <div class="vimeify-embed-privacy-whitelist" style="<?php echo $video['body']['privacy']['embed'] !== 'whitelist' ? 'display:none;' : ''; ?>">
                        <label for="privacy_embed_domain"><?php esc_html_e( 'Enter domain (without http(s)://)', 'vimeify' ); ?></label>
                        <input type="text" name="privacy_embed_domain" id="privacy_embed_domain"/>
                        <button type="submit" name="admin_action" value="add_domain" class="button" disabled><?php esc_html_e( 'Add', 'vimeify' ); ?></button>
                        <input type="hidden" name="uri" value="<?php echo esc_attr( $video['body']['uri'] ); ?>">
                        <div class="form-row">
                            <ul class="privacy-embed-whitelisted-domains">
								<?php
								//if($video['body']['privacy']['embed'] === 'whitelist') {
								try {
									$domains = $plugin->system()->vimeo()->get_whitelisted_domains( $video['body']['uri'] );

									if ( $domains['status'] === 200 ) {
										foreach ( $domains['body']['data'] as $domain ) {
											echo '<li>' . esc_html( $domain['domain'] ) . ' <a href="#" class="submitdelete vimeify-delete-domain" data-uri="' . esc_attr( $video['body']['uri'] ) . '" data-domain="' . esc_attr( $domain['domain'] ) . '">(' . esc_html__( 'remove', 'vimeify' ) . ')</a> </li>';
										}
									}
								} catch ( \Vimeify\Vimeo\Exceptions\VimeoRequestException $e ) {
									echo "<p style='color:red;'>" . esc_html( $e->getMessage() ) . "</p>";
								}
								//}
								?>
                            </ul>
                        </div>
                    </div>
                </div>
				<?php do_action( 'vimeify_video_edit_privacy_section', $plugin, $video, $video_id ); ?>
            </div>
        </div>
        <div class="vimeify-pedit-section">
            <h4><?php esc_html_e( 'Other Settings', 'vimeify' ); ?></h4>
            <div class="vimeify-pedit-section--fields">
                <div class="vimeify-pedit-section--row">
                    <label for="embed_preset_uri">
						<?php esc_html_e( 'Embed preset', 'vimeify' ); ?>
                    </label>
					<?php if ( ! $plugin->system()->vimeo()->supports_embed_presets() ): ?>
                        <p><?php esc_html_e( 'Embed presets are only supported by the following plans:', 'vimeify' ); ?></p>
                        <ul class="vimeify-std-list">
                            <li>Vimeo Plus</li>
                            <li>Vimeo PRO</li>
                            <li>Vimeo Business</li>
                            <li>Vimeo Premium</li>
                        </ul>
                        <p><?php echo wp_kses_post( sprintf( __( 'Your current plan is %s.', 'vimeify' ), '<strong>' . 'Vimeo ' . esc_html( ucfirst( $plugin->system()->vimeo()->user_type ) ) . '</strong>' ) ); ?></p>
                        <p><a href="https://vimeo.com/upgrade" target="_blank" class="button"><?php esc_html_e( 'Upgrade', 'vimeify' ); ?></a></p>
					<?php else: ?>
						<?php
						$current_preset_uri  = empty( $embed_preset_uri ) ? 'default' : $vimeo_formatter->embed_preset_uri_to_id( $embed_preset_uri );
						$current_preset_name = ! empty( $current_preset_uri ) && ( 'default' != $current_preset_uri ) ? $plugin->system()->vimeo()->get_embed_preset_name( $current_preset_uri ) : esc_html__( 'Default (no preset)', 'vimeify' );
						?>
                        <select id="embed_preset_uri" name="embed_preset_uri" class="vimeify-select2" data-action="vimeify_embed_preset_search" data-placeholder="<?php esc_html_e( 'Select preset...', 'vimeify' ); ?>">
							<?php if ( ! empty( $current_preset_uri ) ): ?>
                                <option selected value="<?php echo esc_attr( $current_preset_uri ); ?>"><?php echo esc_html( $current_preset_name ); ?></option>
							<?php endif; ?>
                        </select>
					<?php endif; ?>
                </div>
                <div class="vimeify-pedit-section--row">
					<?php if ( ! $plugin->system()->vimeo()->supports_folders() ): ?>
                        <p><?php esc_html_e( 'Folders are not supported without <strong>interact</strong> scope.', 'vimeify' ); ?></p>
                        <p><?php esc_html_e( 'If you want to use Folders, please go to developer.vimeo.com/apps, regenerate your access token, add <strong>Interact</strong> to the scopes and finally replace your old token in Vimeo settings on your site.', 'vimeify' ); ?></p>
					<?php else: ?>
						<?php
						$current_folder_uri  = empty( $folder_uri ) ? 'default' : $folder_uri;
						$current_folder_name = ! empty( $current_folder_uri ) && ( 'default' != $current_folder_uri ) ? $plugin->system()->vimeo()->get_folder_name( $current_folder_uri ) : esc_html__( 'Default (no folder)', 'vimeify' );
						?>
                        <label for="folder_uri"><?php esc_html_e( 'Folder', 'vimeify' ); ?></label>
                        <select id="folder_uri" name="folder_uri" class="vimeify-select2" data-action="vimeify_folder_search" data-placeholder="<?php esc_html_e( 'Select folder...', 'vimeify' ); ?>">
                            <option value="default" <?php selected( 'default', $current_folder_uri ); ?>><?php esc_html_e( 'Default (no folder)', 'vimeify' ); ?></option>
							<?php if ( ! empty( $current_folder_uri ) ): ?>
                                <option selected value="<?php echo esc_attr( $current_folder_uri ); ?>"><?php echo esc_html( $current_folder_name ); ?></option>
							<?php endif; ?>
                        </select>
					<?php endif; ?>
                </div>
				<?php do_action( 'vimeify_video_edit_other_section', $plugin, $video, $video_id ); ?>
            </div>
        </div>
		<?php do_action( 'vimeify_video_edit_bottom', $plugin, $video, $video_id ); ?>
	<?php endif; ?>

</div>