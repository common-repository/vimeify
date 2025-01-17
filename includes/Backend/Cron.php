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

namespace Vimeify\Core\Backend;

use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Utilities\FileSystem;
use Vimeify\Core\Utilities\VimeoSync;

class Cron extends BaseProvider {

	/**
	 * The synchronization processor
	 * @var VimeoSync
	 */
	protected $sync;

	/**
	 * Registers specific piece of functionality
	 * @return void
	 */
	public function register() {

		$this->sync = new VimeoSync( $this->plugin );

		add_filter( 'cron_schedules', [ $this, 'custom_schedules' ], 9, 1 );
		add_action( 'init', [ $this, 'schedule_actions' ], 9 );
	}

	/**
	 * Return possible list of cron tasks
	 * @return string[][]
	 */
	private function get_actions() {
		return apply_filters( 'vimeify_cron_actions', array(
			'hourly'                  => array(
				'cleanup_pull_files'
			),
			'vimeify_fifteen_minutes' => array(
				'metadata_sync',
			),
			'vimeify_twenty_minutes'  => array(
				'status_sync'
			)
		), $this->plugin );
	}

	/**
	 * Custom cron schedules
	 * @return void
	 */
	public function custom_schedules( $schedules ) {

		$schedules['vimeify_fifteen_minutes'] = array(
			'interval' => 15 * MINUTE_IN_SECONDS,
			'display'  => esc_html__( 'Every Fifteen Minutes', 'vimeify' ),
		);

		$schedules['vimeify_twenty_minutes'] = array(
			'interval' => 20 * MINUTE_IN_SECONDS,
			'display'  => esc_html__( 'Every Twenty Minutes', 'vimeify' ),
		);

		return apply_filters( 'vimeify_cron_schedules', $schedules, $this->plugin );
	}

	/**
	 * Registers the cron events
	 */
	public function schedule_actions() {

		foreach ( $this->get_actions() as $recurrence => $list_of_actions ) {
			foreach ( $list_of_actions as $key ) {
				$hook      = sprintf( 'vimeify_action_%s', $key );
				$timestamp = wp_next_scheduled( $hook );
				if ( ! $timestamp ) {
					wp_schedule_event( time(), $recurrence, $hook );
				}
				if ( method_exists( $this, 'do_' . $key ) ) {
					add_action( $hook, array( $this, 'do_' . $key ) );
				} elseif ( is_array( $recurrence ) ) {
					add_action( $hook, $recurrence );
				}
			}
		}
	}

	/**
	 * Clean up pull files
	 * @return void
	 */
	public function do_cleanup_pull_files() {

		$logtag = 'VIMEIFY-FILE-CLEANUP';

		$this->plugin->system()->logger()->log( 'Starting the temporary files clean up process.', $logtag, 'cron.log' );

		/**
		 * How many minutes needs to pass in order the temporary uploads used for Vimeo Pull uploading method to be removed.
		 * Warning: please make sure you leave at least 30 minutes for Vimeo to process the file. Setting this value to low may cause missing uploads.
		 */
		$removal_delay_minutes = apply_filters( 'vimeify_upload_pull_removal_delay', 180 );
		if ( $removal_delay_minutes < 20 ) { // Protection for just in case.
			$removal_delay_minutes = 100;
		}

		$time_now  = time();
		$tmp_files = $this->plugin->system()->settings()->get_temporary_files();
		if ( count( $tmp_files ) > 0 ) {
			foreach ( $tmp_files as $path => $time_age ) {
				$diff_minutes = round( abs( $time_now - $time_age ) / 60, 2 );
				$file_exists  = FileSystem::exists( $path );
				if ( $file_exists && $diff_minutes >= $removal_delay_minutes ) {
					if ( FileSystem::delete( $path ) ) {
						$this->plugin->system()->settings()->remove_from_temporary_files( $path );
						$this->plugin->system()->logger()->log( sprintf( 'Deleted temporary video file %s after %s minutes', $path, $diff_minutes ), $logtag, 'cron.log' );
					} else {
						$this->plugin->system()->logger()->log( sprintf( 'Unable to remove temporary video file %s.', $path ), $logtag, 'cron.log' );
					}
				} elseif ( ! $file_exists ) {
					$this->plugin->system()->settings()->remove_from_temporary_files( $path );
					$this->plugin->system()->logger()->log( sprintf( 'Temporary video file %s not found in the file system', $path ), $logtag, 'cron.log' );
				}
			}
		} else {
			$this->plugin->system()->logger()->log( 'No temporary files found for clean up.', $logtag, 'cron.log' );
		}
	}

	/**
	 * Sync all the videos in the library with Vimeo.com
	 * @return void
	 */
	public function do_metadata_sync() {
		$logtag = 'VIMEIFY-METADATA-SYNC';
		$this->plugin->system()->logger()->log( 'Starting metadata sync via cron.', $logtag, 'cron.log' );
		$this->sync->sync_metadata();
		$this->plugin->system()->logger()->log( 'Finished metadata sync via cron.', $logtag, 'cron.log' );
	}

	/**
	 * Sync all the videos in the library with Vimeo.com
	 * @return void
	 */
	public function do_status_sync() {
		$logtag = 'VIMEIFY-STATUS-SYNC';
		$this->plugin->system()->logger()->log( 'Starting status sync via cron.', $logtag, 'cron.log' );
		$this->sync->sync_status();
		$this->plugin->system()->logger()->log( 'Finished status sync via cron.', $logtag, 'cron.log' );
	}
}