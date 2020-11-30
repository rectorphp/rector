<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

abstract class Validator
{
	protected function validateLineLengths(): void
	{
	}
}

final class GeneratorStubUseAbstract extends Validator
{
	public function __construct()
	{
		$this->validateLineLengths();
	}
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

abstract class Validator
{
	protected function validateLineLengths(): void
	{
	}
}

final class GeneratorStubUseAbstract extends Validator
{
	public function __construct()
	{
	}
}

?>
