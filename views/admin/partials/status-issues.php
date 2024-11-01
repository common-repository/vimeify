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

// Detect problems
$problems = $plugin->system()->vimeo()->find_problems();
?>

<?php if ( ! empty( $problems ) && count( $problems ) > 0 ): ?>
	<tr class="vimeify-problems">
		<th style="width: 20%">
			<?php esc_html_e( 'Detected Problems', 'vimeify' ); ?>
		</th>
		<td>
			<p class="vimeify-problem-head"><?php esc_html_e( 'Fix the following problems to ensure proper function:', 'vimeify' ); ?></p>
			<ol>
				<?php foreach ( $problems as $problem ): ?>
					<li>
						<div class="vimeify-problem-wrapper">
							<div class="vimeify-problem--info">
								<p><?php echo wp_kses_post($problem['info']); ?></p>
								<p><a class="vimeify-problem-fix-trigger" href="#"><?php esc_html_e( 'Fix prolbem', 'vimeify' ); ?></a></p>
							</div>
							<div class="vimeify-problem--fix" style="display: none;">
								<?php echo wp_kses_post($problem['fix']); ?>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</td>
	</tr>
<?php endif; ?>