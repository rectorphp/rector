<?php

declare(strict_types = 1);

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

class SkipTypedClosure {
	/**
	 * @param \Closure(int): bool $filter
	 */
	static function filter(\Closure $filter): array {
	}
}
?>
