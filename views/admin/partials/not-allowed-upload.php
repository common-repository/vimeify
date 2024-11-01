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
?>

<div class="vimeify-box">
    <h3><?php esc_html_e('Not allowed', 'vimeify'); ?></h3>
    <p>
	    <?php esc_html_e('Sorry. Looks like you are not allowed to upload videos to vimeo.', 'vimeify'); ?>
    </p>
    <?php if ( is_array( $plugin->system()->vimeo()->scopes ) && count( $plugin->system()->vimeo()->scopes ) > 0 ): ?>

    <ul>
        <li><strong><?php esc_html_e('Current scopes', 'vimeify'); ?></strong>: <?php echo esc_html( implode( ', ', $plugin->system()->vimeo()->scopes ) ) ; ?></li>
        <?php if(!empty($plugin->system()->vimeo()->scopes_missing)): ?>
            <li><strong><?php esc_html_e('Missing scopes', 'vimeify'); ?></strong>: <?php echo esc_html( implode( ', ', $plugin->system()->vimeo()->scopes_missing ) ); ?></li>
        <?php endif; ?>
    </ul>

    <?php endif; ?>
    <p>
        <?php
        echo wp_kses_post(
	        sprintf(
		        __( 'Please go to the %s and re-generate your access token with all the required scopes. If you need help check the link bellow.', 'vimeify' ),
		        '<a target="_blank" href="https://developer.vimeo.com/">' . esc_html_e( 'Vimeo developer portal', 'vimeify' ) . '</a>',
	        )
        );
        ?>
    </p>

    <hr/>
    
    <p>
        <a target="_blank" href="<?php echo esc_url( $plugin->documentation_url() ); ?>" class="button-primary"><?php esc_html_e('Documentation', 'vimeify'); ?></a>
    </p>
</div>
