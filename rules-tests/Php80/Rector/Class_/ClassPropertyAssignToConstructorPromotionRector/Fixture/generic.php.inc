<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

/**
 * @template T
 */
final class Generic
{
	/**
	 * @var T
	 */
	public mixed $value;

	/**
	 * @param T $value
	 */
	public function __construct(mixed $value)
	{
		$this->value = $value;
	}
}
?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

/**
 * @template T
 */
final class Generic
{
	/**
	 * @param T $value
	 */
	public function __construct(public mixed $value)
	{
	}
}
?>
