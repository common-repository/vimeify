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

namespace Vimeify\Core\Abstracts;

use Vimeify\Core\Abstracts\Interfaces\PluginInterface;
use Vimeify\Core\Abstracts\Interfaces\ViewInterface;
use Vimeify\Core\Plugin;

abstract class BaseBlock implements ViewInterface {

	/**
	 * The plugin interface
	 * @var PluginInterface|Plugin
	 */
	protected $plugin = null;
	/**
	 * The args
	 * @var array|mixed
	 */
	protected $args = [];

	/**
	 * Constructor
	 *
	 * @param PluginInterface $plugin
	 * @param $args
	 */
	public function __construct( $plugin, $args = [] ) {
		if ( ! empty( $args ) ) {
			$this->args = wp_parse_args( $args, [] );
		}
		$this->plugin = $plugin;
	}

	/**
	 * Registers block editor assets
	 * @return void
	 */
	abstract public function register_block();

	/**
	 * Registers block editor assets
	 * @return void
	 */
	abstract public function register_block_editor_assets();

	/**
	 * Dynamic render for the upload block
	 *
	 * @param $block_attributes
	 * @param $content
	 *
	 * @return string
	 */
	abstract public function render_block( $block_attributes, $content );

}