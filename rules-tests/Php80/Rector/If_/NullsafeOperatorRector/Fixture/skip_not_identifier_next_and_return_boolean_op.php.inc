<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

final class SkipNotIdentifierAndReturnBooleanOp
{
	private static function foo(string $f, ?string $b, bool $c): ?string
	{
		$e = self::check($f, $c);
		if ($e === null) {
			return null;
		}

		if ($b !== null) {
			$a = self::check($b, $c);
			if ($a === null) {
				return null;
			}

			return $a . $d;
		}

		return $d;
	}
}
