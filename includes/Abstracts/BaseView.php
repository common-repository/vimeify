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

abstract class BaseView implements ViewInterface {

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
	 * The default args
	 * @var array|mixed
	 */
	protected $defaults = [];

	/**
	 * The required styles
	 * @var array
	 */
	protected $styles = [];

	/**
	 * The required scripts
	 * @var array
	 */
	protected $scripts = [];

	/**
	 * Constructor
	 *
	 * @param PluginInterface $plugin
	 * @param $args
	 */
	public function __construct( $plugin, $args = [] ) {
		$this->set_defaults();
		if ( ! empty( $args ) ) {
			$this->args = wp_parse_args( $args, $this->defaults );
		}
		$this->plugin = $plugin;
	}

	/**
	 * Outputs the views contents
	 *
	 * @param $params
	 *
	 * @return string
	 */
	public function output( $params = [] ) {
		$this->args = wp_parse_args( $params, $this->defaults );

		return $this->get_output();
	}

	/**
	 * Enqueue the assets
	 * @return void
	 */
	public function enqueue() {
		foreach ( $this->scripts as $script ) {
			wp_enqueue_script( $script );
		}
		foreach ( $this->styles as $style ) {
			wp_enqueue_style( $style );
		}
	}

	/**
	 * Get the defaults
	 * @return array
	 */
	public function get_defaults() {
		return $this->defaults;
	}

	/**
	 * Return the style dependencies
	 * @return string[]
	 */
	public function get_required_styles() {
		return $this->styles;
	}

	/**
	 * Set the defaults
	 *
	 * @param array $args
	 */
	abstract function set_defaults();

	/**
	 * Handles the output
	 * @return string
	 */
	abstract protected function get_output();

}