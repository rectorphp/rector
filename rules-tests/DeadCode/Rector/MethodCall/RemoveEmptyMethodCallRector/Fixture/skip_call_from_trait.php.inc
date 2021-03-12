<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

trait ValidatorTrait
{
	protected function validateLineLengths(): void
	{
	}
}

final class GeneratorStubUseTrait
{
	use ValidatorTrait;

	public function __construct()
	{
		$this->validateLineLengths();
	}
}

?>
