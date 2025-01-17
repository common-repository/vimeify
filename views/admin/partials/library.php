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

$core_validator = new \Vimeify\Core\Utilities\Validators\CoreValidator();

?>
<div class="wrap vimeify-wrap">
	<?php
	if ( ! $core_validator->is_version_met( $plugin->minimum_php_version() ) ) {
		include 'outdated.php';
	} else {

		if ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' && isset( $_GET['id'] ) ) {
			include 'library-edit.php';
		} elseif ( ( ! isset( $_GET['action'] ) || empty( $_GET['action'] ) ) || ( isset( $_GET['action'] ) && ( 'delete' === $_GET['action'] || - 1 === (int) $_GET['action'] ) ) ) {
			include 'library-list.php';

		} else {
			echo esc_html__( 'Invalid action', 'vimeify' );
		}
	}
	?>
</div>
