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

/* @var \Vimeify\Core\Plugin $plugin */


$default_folder_uri = $plugin->system()->settings()->profile()->get( 'Backend.Form.Upload', 'folder' );
if ( $plugin->system()->vimeo()->is_connected && $default_folder_uri ) {
	$default_folder_name = sprintf( '%s (Default)', $plugin->system()->vimeo()->get_folder_name( $default_folder_uri ) );
}


$enable_view_privacy = (int) $plugin->system()->settings()->plugin()->get( 'admin.upload_forms.enable_view_privacy', 0 );
$enable_folders      = (int) $plugin->system()->settings()->plugin()->get( 'admin.upload_forms.enable_folders', 0 );


?>

<div class="wrap vimeify-wrap">

    <h2><?php esc_html_e( 'Upload to Vimeo', 'vimeify' ); ?></h2>

	<?php if ( $plugin->system()->vimeo()->is_connected && $plugin->system()->vimeo()->can_upload() ): ?>

        <div class="vimeify-box" style="max-width: 500px;">
            <form class="vimeify-video-upload" enctype="multipart/form-data" method="post" action="/">
                <div class="form-row">
                    <label for="vimeo_title"><?php esc_html_e( 'Title', 'vimeify' ); ?></label>
                    <input type="text" name="vimeo_title" id="vimeo_title">
                </div>
                <div class="form-row">
                    <label for="vimeo_description"><?php esc_html_e( 'Description', 'vimeify' ); ?></label>
                    <textarea name="vimeo_description" id="vimeo_description"></textarea>
                </div>
				<?php if ( $enable_view_privacy ): ?>
					<?php
					$view_privacy_opts = $plugin->system()->vimeo()->get_view_privacy_options_for_forms( 'admin' );
					?>
                    <div class="form-row">
                        <label for="vimeo_view_privacy"><?php esc_html_e( 'View Privacy', 'vimeify' ); ?></label>
                        <select name="vimeo_view_privacy" id="vimeo_view_privacy">
							<?php foreach ( $view_privacy_opts as $key => $option ): ?>
								<?php
								$option_state = $option['default'] && $option['available'] ? 'selected' : '';
								$option_state .= $option['available'] ? '' : ' disabled';
								?>
                                <option <?php echo esc_attr( $option_state ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $option['name'] ); ?></option>
							<?php endforeach; ?>
                        </select>
                    </div>
				<?php endif; ?>
				<?php if ( $enable_folders ): ?>
                    <div class="form-row">
                        <label for="folder_uri"><?php esc_html_e( 'Folder', 'vimeify' ); ?></label>
                        <select id="folder_uri" name="folder_uri" class="vimeify-select2" data-action="vimeify_folder_search"
                                data-placeholder="<?php esc_html_e( 'Select folder...', 'vimeify' ); ?>">
                            <option value="default" <?php selected( 'default', $default_folder_uri ); ?>><?php esc_html_e( 'Default (no folder)', 'vimeify' ); ?></option>
							<?php if ( ! empty( $default_folder_uri ) ): ?>
                                <option selected value="<?php echo esc_attr( $default_folder_uri ); ?>"><?php echo esc_html( $default_folder_name ); ?></option>
							<?php endif; ?>
                        </select>
                    </div>
				<?php endif; ?>
                <div class="form-row">
                    <label for="vimeo_video"><?php esc_html_e( 'Video File', 'vimeify' ); ?></label>
                    <p class="vimeify-mt-0"><input type="file" name="vimeo_video" id="vimeo_video"></p>
                    <div class="vimeify-progress-bar" style="display: none;">
                        <div class="vimeify-progress-bar-inner"></div>
                        <div class="vimeify-progress-bar-value">0%</div>
                    </div>
                </div>
                <div class="form-row with-border">
                    <div class="vimeify-loader" style="display:none;"></div>
                    <button type="submit" class="button-primary" name="vimeo_upload" value="1">
						<?php esc_html_e( 'Upload', 'vimeify' ); ?>
                    </button>
                </div>
            </form>
        </div>
        <p>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . \Vimeify\Core\Backend\Ui::PAGE_VIMEO ) ); ?>"><?php esc_html_e( '< Back to library', 'vimeify' ); ?></a>
        </p>

	<?php else: ?>

		<?php if ( ! $plugin->system()->vimeo()->is_connected ): ?>

            <?php include 'not-connected.php'; ?>

		<?php elseif( ! $plugin->system()->vimeo()->can_upload() ): ?>

			<?php include 'not-allowed-upload.php'; ?>

		<?php endif; ?>

	<?php endif; ?>

</div>

