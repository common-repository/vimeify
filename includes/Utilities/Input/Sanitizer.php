<?php

namespace Vimeify\Core\Utilities\Input;

class Sanitizer {

	/**
	 * Sanitization function
	 *
	 * @param $input
	 *
	 * @return float|int||string
	 */
	public static function run( $input ) {

		if ( is_scalar( $input ) ) {
			if ( is_numeric( $input ) ) {
				if ( strpos( $input, '.' ) !== false ) {
					$input = (double) $input;
				} else {
					$input = (int) $input;
				}
			} else {
				$input = sanitize_text_field( $input );
			}
		} elseif ( is_object( $input ) ) {
			foreach ( $input as $key => $value ) {
				$input->$key = self::sanitize( $value );
			}
		} elseif ( is_array( $input ) ) {
			foreach ( $input as $key => $value ) {
				$input[ $key ] = self::sanitize( $value );
			}
		}

		return $input;

	}

}