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

namespace Vimeify\Core\Utilities;

use WpOrg\Requests\Exception;

class FileSystem {

	/**
	 * Returns the WordPress filesystem
	 * @return bool|\WP_Filesystem_Base|null
	 * @throws Exception
	 */
	public static function wpfs() {
		global $wp_filesystem;
		if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/file.php' );
			if ( ! wp_filesystem( request_filesystem_credentials( site_url() ) ) ) {
				throw new Exception( 'Unable to initialize the WP FileSystem.' );
			}
		}

		return $wp_filesystem;
	}

	/**
	 * Check if given path is a file
	 * @return bool
	 */
	public static function is_file( $path ) {
		try {
			$fs = self::wpfs();

			return $fs->is_file( $path );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Check if given path is a dir
	 * @return bool
	 */
	public static function is_dir( $path ) {
		try {
			$fs = self::wpfs();

			return $fs->is_dir( $path );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Check if given path is writable
	 * @param $path
	 *
	 * @return bool
	 */
	public static function is_writable( $path ) {
		try {
			$fs = self::wpfs();
			return $fs->is_writable( $path );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Check if given path is readable
	 * @param $path
	 *
	 * @return bool
	 */
	public static function is_readable( $path ) {
		try {
			$fs = self::wpfs();
			return $fs->is_readable( $path );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Check if file exists
	 * @return bool
	 */
	public static function file_exists( $path ) {
		try {
			$fs = self::wpfs();

			return $fs->exists( $path );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Check the file size
	 * @param $path
	 *
	 * @param string $path Path to file.
	 * @return int|false Size of the file in bytes on success, false on failure.
	 */
	public static function file_size($path) {

		try {
			$fs = self::wpfs();
			return $fs->size( $path );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Check if file exists
	 * @param $path
	 *
	 * @return bool
	 */
	public static function exists($path) {
		try {
			$fs = self::wpfs();
			return $fs->exists( $path );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Check if file exists
	 *
	 * @param $path
	 * @param int $time
	 * @param int $atime
	 *
	 * @return bool
	 */
	public static function touch($path, $time = 0, $atime = 0) {
		try {
			$fs = self::wpfs();
			return $fs->touch( $path, $time = 0, $atime = 0 );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Create a directory
	 *
	 * @param $path
	 * @param bool $chmod
	 * @param bool $chown
	 * @param bool $chgrp
	 *
	 * @return bool
	 */
	public static function mkdir($path, $chmod = false, $chown = false, $chgrp = false) {
		try {
			$fs = self::wpfs();
			return $fs->mkdir( $path, $chmod, $chown, $chgrp );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Move a file to destination
	 *
	 * @param $source
	 * @param $destination
	 * @param $overwrite
	 *
	 * @return bool
	 */
	public static function move( $source, $destination, $overwrite = false ) {
		try {
			$fs = self::wpfs();
			return $fs->move( $source, $destination, $overwrite );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Copy a file to destination
	 *
	 * @param $source
	 * @param $destination
	 * @param $overwrite
	 * @param $mode
	 *
	 * @return bool
	 */
	public static function copy( $source, $destination, $overwrite = false, $mode = false ) {
		try {
			$fs = self::wpfs();
			return $fs->copy( $source, $destination, $overwrite, $mode );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Delete a file
	 *
	 * @param $path
	 * @param bool $recursive
	 * @param bool $type
	 *
	 * @return bool
	 */
	public static function delete( $path, $recursive = false, $type = false ) {
		try {
			$fs = self::wpfs();
			return $fs->delete( $path, $recursive, $type );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Put content into file
	 * @param $path
	 * @param $contents
	 * @param $mode
	 *
	 * @return bool
	 */
	public static function put_contents($path, $contents, $flag = 'a', $mode = false) {
		try {
			$fs = self::wpfs();
			if ( $flag == 'w' ) {
				$fs->delete( $path );
			}
			return $fs->put_contents( $path, $contents, $mode );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Get file contents
	 *
	 * @param $path
	 *
	 * @return bool
	 */
	public static function get_contents($path) {
		try {
			$fs = self::wpfs();
			return $fs->get_contents( $path );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 *  Set correct file permissions for specific file.
	 *
	 * @param $path
	 *
	 * @since 1.6.0
	 */
	public static function set_file_permissions( $path ) {
		try {
			$fs = self::wpfs();
			$fs->chmod( $path, 0644 );
		} catch ( Exception $e ) {
		}
	}

	/**
	 * Add trailing slash to path
	 *
	 * @param $path
	 *
	 * @return string
	 */
	public static function slash( $path ) {
		if ( function_exists( '\trailingslashit' ) ) {
			return \trailingslashit( $path );
		}

		return self::unslash( $path ) . '/';
	}

	/**
	 * Unslash path
	 *
	 * @param $path
	 *
	 * @return string
	 */
	public static function unslash( $path ) {

		if ( function_exists( '\untrailingslashit' ) ) {
			return \untrailingslashit( $path );
		}

		return rtrim( $path, '/\\' );
	}
}