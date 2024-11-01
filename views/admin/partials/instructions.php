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

// Urls
$url_guide    = $plugin->documentation_url();
$url_purchase = $plugin->commercial_url();

// Dismiss Init
$dismiss_link = esc_url( add_query_arg( [
	'dismiss'  => 'yes',
	'action'   => 'vimeify_dismiss_instructions',
	'_wpnonce' => wp_create_nonce( 'vimeify_nonce' ),
], admin_url( 'admin-ajax.php' ) ) );


?>
<div class="instructions vimeify-instructions notice">
    <div class="vimeify-instructions-card vimeify-instructions-card-shadow">
        <div class="vimeify-instructions-row vimeify-instructions-header">
            <div class="vimeify-instructions-colf">
                <p class="lead">
                    <?php esc_html_e( 'Thanks for installing', 'vimeify' ); ?>
                    <strong class="green"><?php esc_html_e( 'Video Uploads for Vimeo', 'vimeify' ); ?></strong>
                </p>
                <p class="desc"><?php esc_html_e( 'This plugin allows you to easily upload and embed Vimeo videos through your WordPress website.', 'vimeify' ); ?></p>
                <p class="desc"><?php echo wp_kses_post( sprintf( __( 'To %s please follow the steps below:', 'vimeify' ), '<strong>' . esc_html__( 'get started', 'vimeify' ) . '</strong>' ) ); ?></p>
            </div>
        </div>
        <div class="vimeify-instructions-row">
            <div class="vimeify-instructions-col4">
                <div class="vimeify-instructions-instruction">
                    <h4 class="navy"><?php esc_html_e( '1. Vimeo Developer Portal', 'vimeify' ); ?></h4>
                    <p>
						<?php
						$txt_create_app = '<strong>' . esc_html__( 'Create App', 'vimeify' ) . '</strong>';
						$txt_dev_portal = '<a target="_blank" href="https://developer.vimeo.com/">' . esc_html__( 'Vimeo Developer Portal', 'vimeify' ) . '</a>';
						echo wp_kses_post( sprintf( __( 'To get started and successfully connect the plugin to Vimeo you will need to sign up at %s and then %s that will be used by your website.', 'vimeify' ), $txt_dev_portal, $txt_create_app ) );
						?>
                    </p>
                </div>
            </div>
            <div class="vimeify-instructions-col4">
                <div class="vimeify-instructions-instruction">
                    <h4 class="navy"><?php esc_html_e( '2. Request Upload Access', 'vimeify' ); ?></h4>
                    <p>
						<?php esc_html_e( 'In order to be able to upload videos from external software like this plugin you need to request Upload Access from Vimeo and wait for approval.', 'vimeify' ); ?>
                        <br/>
                        <strong><?php esc_html_e( '(1-5 days required for approval)', 'vimeify' ); ?></strong>
                    </p>
                </div>
            </div>
            <div class="vimeify-instructions-col4">
                <div class="vimeify-instructions-instruction">
                    <h4 class="navy"><?php esc_html_e( '3. Obtain API Credentials', 'vimeify' ); ?></h4>
                    <p>
						<?php
						$txt_c_id  = '<strong>' . esc_html__( 'Client ID', 'vimeify' ) . '</strong>';
						$txt_c_sec = '<strong>' . esc_html__( 'Client Secret', 'vimeify' ) . '</strong>';
						$txt_a_tok = '<strong>' . esc_html__( 'Access Token', 'vimeify' ) . '</strong>';
						echo wp_kses_post( sprintf( __( 'After your upload access is approved you will need to create access token and also collect the required credentials such as %s, %s, %s.', 'vimeify' ), $txt_c_id, $txt_c_sec, $txt_a_tok ) );
						?>
                    </p>
                </div>
            </div>
            <div class="vimeify-instructions-col4">
                <div class="vimeify-instructions-instruction">
                    <h4 class="navy"><?php esc_html_e( '4. Setup Credentials', 'vimeify' ); ?></h4>
                    <p>
						<?php
						$txt_settings = '<a href="' . esc_url( $plugin->settings_url() ) . '">' . esc_html__( 'settings', 'vimeify' ) . '</a>';
						echo wp_kses_post( sprintf( __( 'Finally, assuming that you have upload access and all the credentials from the step 3, you need to enter those in the plugin %s page', 'vimeify' ), $txt_settings ) );
						?>
                    </p>
                </div>
            </div>
        </div>
        <div class="vimeify-instructions-row">
            <div class="vimeify-instructions-colf vimeify-pt-0 vimeify-pb-0">
                <a class="button-small button-primary" target="_blank"
                   href="<?php echo esc_url( $url_guide ); ?>"><?php esc_html_e( 'Read guide', 'vimeify' ); ?></a>
            </div>
            <div class="vimeify-instructions-colf vimeify-pb-0">
                <hr/>
            </div>
        </div>
        <div class="vimeify-instructions-row vimeify-instructions-mb-10">
            <div class="vimeify-instructions-colf">
                <div class="vimeify-instructions-extra">
                    <h4 class="navy"><?php esc_html_e( 'Support', 'vimeify' ); ?></h4>
                    <p>
						<?php esc_html_e( 'If you need any help setting up the plugin, feel free to get in touch with us and we can discuss more!', 'vimeify' ); ?>
                    </p>
                </div>
            </div>
        </div>
        <a href="<?php echo esc_url( $dismiss_link ); ?>" class="notice-dismiss vimeify-notice-dismiss"><span
                    class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice', 'vimeify' ); ?>.</span></a>
    </div>
</div>
