<?php

namespace Lazysizes\LegacyBlurhash;

final class DC {

	public static function encode( array $value ): int {
		$rounded_r = Color::tosRGB( $value[0] );
		$rounded_g = Color::tosRGB( $value[1] );
		$rounded_b = Color::tosRGB( $value[2] );
		return ( $rounded_r << 16 ) + ( $rounded_g << 8 ) + $rounded_b;
	}
}
