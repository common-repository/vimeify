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
/* @var \WP_Query $query */
/* @var bool $single_pages_enabled */
/* @var bool $show_pagination */
/* @var array $actions */
/* @var string $pagination */
?>

<div class="vimeify-table-wrapper table-responsive <?php echo esc_attr( empty( $query->posts ) ? 'vimeify-table-wrapper-empty' : '' ); ?>">
    <table class="vimeify-table table">
        <thead>
        <tr>
            <th class="vimeify-head-title"><?php esc_html_e( 'Title', 'vimeify' ); ?></th>
            <th class="vimeify-head-date"><?php esc_html_e( 'Date', 'vimeify' ); ?></th>
			<?php if ( ! empty( $actions ) ): ?>
                <th class="vimeify-head-actions"><?php esc_html_e( 'Actions', 'vimeify' ); ?></th>
			<?php endif; ?>
        </tr>
        </thead>
        <tbody>
		<?php if ( ! empty( $query->posts ) ): ?>
			<?php foreach ( $query->posts as $post ): ?>
                <tr>
                    <td class="vimeify-row-title"><?php echo esc_html( get_the_title( $post ) ); ?></td>
                    <td class="vimeify-row-date"><?php echo esc_html( get_the_date( '', $post ) ); ?></td>
                    <?php if ( ! empty( $actions ) ): ?>
                        <td class="vimeify-row-actions">
							<?php foreach ( $actions as $action ): ?>
								<?php
								$icon  = isset( $action['icon'] ) ? $action['icon'] : '';
								$text  = isset( $action['text'] ) ? $action['text'] : '';
								$cback = isset( $action['action'] ) && is_callable( $action['action'] ) ? $action['action'] : null;
								$data  = [];
								if ( ! is_null( $cback ) ) {
									$data = call_user_func( $cback, $post );
								}
								?>
                                <a href="<?php echo ! empty( $data['link'] ) ? esc_url( $data['link'] ) : ''; ?>" target="<?php echo isset( $data['target'] ) ? esc_attr( $data['target'] ) : '_blank'; ?>" title="<?php echo esc_attr( $text ); ?>">
                                    <span class="<?php echo esc_attr( $icon ); ?>"></span>
                                </a>
							<?php endforeach; ?>
                        </td>
					<?php endif; ?>
                </tr>
			<?php endforeach; ?>
		<?php else: ?>
            <tr>
                <td colspan="4"><?php esc_html_e( 'No results found', 'vimeify' ); ?></td>
            </tr>
		<?php endif; ?>
        </tbody>
    </table>
	<?php if ( $show_pagination && $query->max_num_pages > 1 ): ?>
		<?php echo wp_kses( $pagination, wp_kses_allowed_html( 'vimeify' ) ); ?>
	<?php endif; ?>
</div>
