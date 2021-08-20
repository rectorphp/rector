<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

interface X {}

class Y implements X {}

class Z {}

final class UnionFullyQualified
{
	public Y $y;

	public Z $z;

	public function __construct(Y $y, Z $z)
	{
		$this->y = $y;
		$this->z = $z;
	}

	public function getX(): X
	{
		return $this->y;
	}
}
?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

interface X {}

class Y implements X {}

class Z {}

final class UnionFullyQualified
{
	public function __construct(public Y $y, public Z $z)
	{
	}

	public function getX(): X
	{
		return $this->y;
	}
}
?>
