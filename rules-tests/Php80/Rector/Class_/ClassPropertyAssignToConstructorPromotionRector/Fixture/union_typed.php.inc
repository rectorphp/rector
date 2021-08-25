<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

use stdClass;
use DateTime;

final class UnionTyped
{
	public stdClass|DateTime $property;

	public function __construct(stdClass|DateTime $property)
	{
		$this->property = $property;
	}
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

use stdClass;
use DateTime;

final class UnionTyped
{
	public function __construct(public stdClass|DateTime $property)
	{
	}
}

?>
