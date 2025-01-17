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

namespace Vimeify\Core\Components;

use Vimeify\Core\Abstracts\Interfaces\LoggerInterface;
use Vimeify\Core\Abstracts\Interfaces\SystemComponentInterface;
use Vimeify\Core\Abstracts\Interfaces\SystemInterface;
use Vimeify\Core\Utilities\FileSystem;

class Logger implements LoggerInterface, SystemComponentInterface {

	/**
	 * The log dir
	 * @var string|false
	 */
	protected $log_dir = null;

	/**
	 * The system instance
	 * @var SystemComponentInterface
	 */
	protected $system;

	/**
	 * Logger constructor.
	 */
	public function __construct( SystemInterface $system, $args = [] ) {
		$this->system = $system;
		$this->setup_log_dir();
		$this->protect_log_dir();
	}

	/**
	 * Wrapper for writing the interactions to /wp-content/uploads/ file
	 *
	 * @param  $message
	 * @param  string  $tag
	 * @param  string  $filename
	 *
	 * @return bool
	 */
	public function log( $message, $tag = '', $filename = "debug.log" ) {

		if ( ! FileSystem::file_exists( $this->log_dir ) ) {
			return false;
		}
		$log_file_path = trailingslashit( $this->log_dir ) . $filename;
		if ( FileSystem::file_exists( $log_file_path ) && FileSystem::file_size( $log_file_path ) > 10485760 ) {
			FileSystem::delete( $log_file_path );
		}
		$is_object = false;
		if ( ! is_string( $message ) && ! is_numeric( $message ) ) {
			ob_start();
			$this->dump( $message );
			$message   = ob_get_clean();
			$is_object = true;
		}

		if ( ! empty( $tag ) ) {
			if ( $is_object ) {
				$message = $tag . "\n" . $message;
				$message = sprintf( "%s%s%s", $tag, PHP_EOL, $message );
			} else {
				$message = sprintf( '%s: %s', $tag, $message );
			}
		}

		$message = sprintf( '[%s] %s', gmdate( 'Y-m-d H:i:s' ), $message );

		$this->writeln( $log_file_path, $message );

		return true;
	}

	/**
	 * Return the log dir
	 */
	public function get_log_dir() {
		return $this->log_dir;
	}

	/**
	 * Return the log path
	 */
	private function setup_log_dir() {
		$tmp_dir = $this->system->tmp_dir();
		$this->log_dir = $tmp_dir->path;
	}

	/**
	 * Return the log dir
	 *
	 * @param  bool  $noindex
	 */
	private function protect_log_dir( $noindex = true ) {

		$dir = $this->log_dir;

		if ( ! FileSystem::is_dir( $dir ) ) {
			FileSystem::mkdir($dir);
		}
		if ( FileSystem::is_dir( $dir ) ) {
			$index_path = $dir . DIRECTORY_SEPARATOR . 'index.html';
			if ( ! FileSystem::file_exists( $index_path ) ) {
				FileSystem::touch( $index_path );
			}
		}
		if ( $noindex ) {
			$htaccess_path = $dir . DIRECTORY_SEPARATOR . '.htaccess';
			if ( ! FileSystem::exists( $htaccess_path ) ) {
				$contents = '# BEGIN WP Vimeo Videos
# The directives (lines) between "BEGIN Vimeo Videos" and "END Vimeo Videos" are
# dynamically generated, and should only be modified via WordPress filters.
# Any changes to the directives between these markers will be overwritten.
# Disable PHP and Python scripts parsing.
<Files *>
  SetHandler none
  SetHandler default-handler
  RemoveHandler .cgi .php .php3 .php4 .php5 .phtml .pl .py .pyc .pyo
  RemoveType .cgi .php .php3 .php4 .php5 .phtml .pl .py .pyc .pyo
</Files>
<IfModule mod_php5.c>
  php_flag engine off
</IfModule>
<IfModule mod_php7.c>
  php_flag engine off
</IfModule>
<IfModule headers_module>
  Header set X-Robots-Tag "noindex"
</IfModule>
# END Vimeo Videos';
				$this->writeln( $htaccess_path, $contents );
			}
		}
	}

	/**
	 * Used to write contents into file provided by parameters
	 *
	 * @param $file string
	 * @param $contents string
	 * @param  string  $force_flag
	 */
	private function writeln( $file, $contents, $force_flag = '' ) {
		if ( FileSystem::exists( $file ) ) {
			$flag = $force_flag !== '' ? $force_flag : 'a';
		} else {
			$flag = $force_flag !== '' ? $force_flag : 'w';
		}
		FileSystem::put_contents( $file, $contents."\n", $flag );
	}

	/**
	 * Dump data
	 *
	 * @param $data
	 */
	private function dump( $data ) {
		print_r( $data );
	}

}
