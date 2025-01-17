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

use Vimeify\Core\Components\Vimeo;
use Vimeify\Core\Utilities\Formatters\ByteFormatter;
use Vimeify\Core\Utilities\Formatters\DateFormatter;

$byte_formatter = new ByteFormatter();
$date_formatter = new DateFormatter();

?>

<tr>
	<th style="width: 20%">
		<?php esc_html_e( 'Status', 'vimeify' ); ?>
	</th>
	<td>
		<?php if ( $plugin->system()->vimeo()->is_connected && $plugin->system()->vimeo()->is_authenticated_connection ): ?>
			<span class="vimeify-status-green"><?php esc_html_e( 'Connected', 'vimeify' ); ?></span>
		<?php elseif ( $plugin->system()->vimeo()->is_connected && ! $plugin->system()->vimeo()->is_authenticated_connection ): ?>
			<span class="vimeify-status-yellow"><?php esc_html_e( 'Connected (Unauthenticated)', 'vimeify' ); ?></span>
		<?php else: ?>
			<span class="vimeify-status-red"><?php esc_html_e( 'Not Connected', 'vimeify' ); ?></span>
		<?php endif; ?>
	</td>
</tr>
<?php if ( $plugin->system()->vimeo()->is_connected ): ?>

	<?php if ( $plugin->system()->vimeo()->is_authenticated_connection ): ?>
		<tr>
			<th style="width: 20%">
				<?php esc_html_e( 'User', 'vimeify' ); ?>
			</th>
			<td>
				<a href="<?php echo esc_url( $plugin->system()->vimeo()->user_link ); ?>" target="_blank"><?php echo esc_html( $plugin->system()->vimeo()->user_name ); ?></a>
			</td>
		</tr>
	<?php endif; ?>

	<?php if ( $plugin->system()->vimeo()->is_authenticated_connection ): ?>
		<tr>
			<th style="width: 20%">
				<?php esc_html_e( 'Plan', 'vimeify' ); ?>
			</th>
			<td>
				<?php echo esc_html( $plugin->system()->vimeo()->get_plan( true ) ); ?>
			</td>
		</tr>
	<?php endif; ?>

	<tr>
		<th style="width: 20%">
			<?php esc_html_e( 'App', 'vimeify' ); ?>
		</th>
		<td>
			<?php echo esc_html( $plugin->system()->vimeo()->app_name ); ?>
		</td>
	</tr>
	<tr>
		<th style="width: 20%">
			<?php esc_html_e( 'Scopes', 'vimeify' ); ?>
		</th>
		<td>
			<?php
			if ( ! empty( $plugin->system()->vimeo()->scopes ) ) {
				echo esc_html( implode( ', ', $plugin->system()->vimeo()->scopes ) );
			} else {
				echo esc_html__( 'No scopes found', 'vimeify' );
			}
			?>
		</td>
	</tr>
	<?php if ( ! empty( $plugin->system()->vimeo()->upload_quota ) ): ?>
        <tr>
            <th>
				<?php esc_html_e( 'Quota', 'vimeify' ); ?>
            </th>
            <td>
				<?php
				switch ( $plugin->system()->vimeo()->get_current_quota_type() ) {
					case Vimeo::QUOTA_TYPE_VIDEOS_COUNT:
						$used  = $plugin->system()->vimeo()->get_current_used_quota();
						$max   = $plugin->system()->vimeo()->get_current_max_quota();
						$reset = $plugin->system()->vimeo()->get_quota_reset_date();
						if ( $reset ) {
							echo sprintf( esc_html__( '%s / %s (resets on %s)', 'vimeify' ), (int) $used, (int) $max, esc_html($reset) );
						} else {
							echo sprintf( esc_html__( '%s / %s', 'vimeify' ), (int) $used, (int) $max );
						}
						break;
					case Vimeo::QUOTA_TYPE_VIDEOS_SIZE:
						$used  = $byte_formatter->format( (int) $plugin->system()->vimeo()->get_current_used_quota(), 2 );
						$max   = $byte_formatter->format( (int) $plugin->system()->vimeo()->get_current_max_quota(), 2 );
						$reset = $date_formatter->format_tz( $plugin->system()->vimeo()->get_quota_reset_date() );
						if ( $reset ) {
							echo sprintf( esc_html__( '%s / %s (resets on %s)', 'vimeify' ), (int) $used, (int) $max, esc_html($reset) );
						} else {
							echo sprintf( esc_html__( '%s / %s', 'vimeify' ), (int) $used, (int) $max );
						}
						break;
					default:
						esc_html_e( 'Unsupported account quota type.', 'vimeify' );
						break;
				}
				?>
            </td>
        </tr>
	<?php endif; ?>
	<?php if ( isset( $plugin->system()->vimeo()->headers['x-ratelimit-limit'] ) && is_numeric( $plugin->system()->vimeo()->headers['x-ratelimit-limit'] ) ): ?>
		<tr>
			<th style="width: 20%">
				<?php esc_html_e( 'Rate Limits', 'vimeify' ); ?>
			</th>
			<td>
				<?php
				$used  = (int) $plugin->system()->vimeo()->headers['x-ratelimit-limit'] - (int) $plugin->system()->vimeo()->headers['x-ratelimit-remaining'];
				$max   = (int) $plugin->system()->vimeo()->headers['x-ratelimit-limit'];
				$reset = $date_formatter->format_tz( $plugin->system()->vimeo()->headers['x-ratelimit-reset'] );
				echo sprintf( esc_html__( '%s / %s per minute (resets on %s)', 'vimeify' ), (int) $used, (int) $max, esc_html( $reset ) );
				?>
			</td>
		</tr>
	<?php endif; ?>
<?php endif; ?>