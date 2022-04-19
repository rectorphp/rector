<?php

namespace Rector\Tests\Php70\Rector\If_\IfToSpaceshipRector\SideEffect;

class SideEffect {

	public static function foo( $param ) {
		if ( 0 === strpos( $param, '/*' ) ) {
			$foo = 1;
		}

		$foo = 2;
		return $foo;
	}

	private static function rsort( $a, $b ) {
		if ( $a['bar'] === $b['bar'] ) {
			return 0;
		} else {
			return ( $a['bar'] > $b['bar'] ) ? -1 : 1;
		}
	}

}

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\If_\IfToSpaceshipRector\SideEffect;

class SideEffect {

	public static function foo( $param ) {
		if ( 0 === strpos( $param, '/*' ) ) {
			$foo = 1;
		}

		$foo = 2;
		return $foo;
	}

	private static function rsort( $a, $b ) {
		return $b['bar'] <=> $a['bar'];
	}

}

?>
