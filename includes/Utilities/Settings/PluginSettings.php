<?php

namespace Vimeify\Core\Utilities\Settings;

use Vimeify\Core\Abstracts\Interfaces\PluginSettingsInterface;
use Vimeify\Core\Utilities\Arrays\DotNotation;
use Vimeify\Core\Utilities\Input\Sanitizer;

class PluginSettings implements PluginSettingsInterface {

	/**
	 * @var string
	 */
	protected $settings_key;

	/**
	 * Updates count
	 * @var
	 */
	protected $updates_count = 0;

	/**
	 * The dot notation
	 * @var DotNotation
	 */
	protected $dot;

	/**
	 * Constructor
	 *
	 * @param  array  $config
	 *
	 * @throws \Exception
	 */
	public function __construct( $config = [] ) {
		$this->load( $config );
	}

	/**
	 * All the settings
	 *
	 * @return array
	 */
	public function all() {
		return $this->dot->getValues();
	}

	/**
	 * Update setting
	 *
	 * @param $key
	 * @param $value
	 */
	public function set( $key, $value ) {
		$value = Sanitizer::run( $value );
		$this->dot->set( $key, $value );
		$this->updates_count ++;
	}

	/**
	 * Remove setting
	 *
	 * @param $key
	 */
	public function remove( $key ) {
		if ( $this->dot->have( $key ) ) {
			$this->dot->set( $key, null );
			$this->updates_count ++;
		}
	}

	/**
	 * Retrieve single setting.
	 *
	 * @param $key
	 * @param  null  $default
	 *
	 * @return mixed|null
	 */
	public function get( $key, $default = null ) {
		$value = $this->dot->get( $key, $default );

		return apply_filters( 'vimeify_settings_get', $value, $key, $default );
	}

	/**
	 * Save settings
	 */
	public function save() {
		update_option( $this->settings_key, $this->dot->getValues() );
		$this->updates_count = 0;
	}

	/**
	 * Updates count
	 * @return int
	 */
	public function updates_count() {
		return $this->updates_count;
	}

	/**
	 * Load the settings
	 *
	 * @param  array  $config
	 *
	 * @return void
	 * @throws \Exception
	 */
	protected function load( $config = [] ) {
		if ( isset( $config['settings_key'] ) ) {
			$this->settings_key = $config['settings_key'];
		}

		if ( is_null( $this->settings_key ) ) {
			throw new \Exception( 'No settings key specified.' );
		}

		$data = get_option( $this->settings_key );
		if ( empty( $data ) ) {
			$this->dot = new DotNotation( [] );
		} else {
			$this->dot = new DotNotation( $data );
		}
	}
}