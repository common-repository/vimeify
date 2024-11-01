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

namespace Vimeify\Core;

use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Backend\Registry as BackendRegistry;
use Vimeify\Core\Frontend\Registry as FrontendRegistry;
use Vimeify\Core\Integrations\Registry as IntegrationRegistry;
use Vimeify\Core\RestAPI\Registry as RestAPIRegistry;
use Vimeify\Core\Shared\Registry as SharedRegistry;
use Vimeify\Core\Utilities\ProcessManager;

class Boot extends BaseProvider {

	/**
	 * The frontend hooks
	 * @var FrontendRegistry
	 */
	public $frontend;

	/**
	 * The backend hooks
	 * @var BackendRegistry
	 */
	public $backend;

	/**
	 * The shared hooks
	 * @var SharedRegistry
	 */
	public $shared;

	/**
	 * The rest API
	 * @var RestAPIRegistry
	 */
	public $restApi;

	/**
	 * The integrations
	 * @var IntegrationRegistry
	 */
	public $integrations;

	/**
	 * Registers specific piece of functionality
	 * @return void
	 */
	public function register() {

		$this->integrations = $this->boot( IntegrationRegistry::class );

		$this->init_process_manager();

		do_action( 'vimeify_booting', $this );

		$this->shared   = $this->boot( SharedRegistry::class );
		$this->frontend = $this->boot( FrontendRegistry::class );
		$this->backend  = $this->boot( BackendRegistry::class );
		$this->restApi  = $this->boot( RestAPIRegistry::class );

		do_action( 'vimeify_booted', $this );
	}

	/**
	 * Initializes the process manager instance.
	 * @return void
	 */
	public function init_process_manager() {
		ProcessManager::create( $this->plugin() );
		ProcessManager::instance();
	}

	/**
	 * The plugin
	 * @return Abstracts\Interfaces\PluginInterface
	 */
	public function plugin() {
		return $this->plugin;
	}

	/**
	 * The backend
	 * @return BackendRegistry
	 */
	public function backend() {
		return $this->backend;
	}

	/**
	 * The frontend
	 * @return FrontendRegistry
	 */
	public function frontend() {
		return $this->frontend;
	}

	/**
	 * The shared
	 * @return SharedRegistry
	 */
	public function shared() {
		return $this->shared;
	}

	/**
	 * The rest API
	 * @return RestAPIRegistry
	 */
	public function restApi() {
		return $this->restApi;
	}

}