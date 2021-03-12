<?php

declare(strict_types=1);

namespace Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Source;

final class MyClassFactory
{
	public function create(string $argument): MyClass
	{
		return new MyClass($argument);
	}
}
