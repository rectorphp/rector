<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

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

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

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
