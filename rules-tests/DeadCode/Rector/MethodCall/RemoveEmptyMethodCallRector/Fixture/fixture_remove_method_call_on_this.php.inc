<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

final class FixtureRemoveMethodCallOnThis
{
	public function __construct()
	{
		$this->validateLineLengths();
	}

	protected function validateLineLengths(): void
	{
	}
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

final class FixtureRemoveMethodCallOnThis
{
	public function __construct()
	{
	}

	protected function validateLineLengths(): void
	{
	}
}

?>
