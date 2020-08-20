<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\New_\NewObjectToFactoryCreateRector\Source;

final class MyClassFactory
{
	public function create(string $argument): MyClass
	{
		return new MyClass($argument);
	}
}
