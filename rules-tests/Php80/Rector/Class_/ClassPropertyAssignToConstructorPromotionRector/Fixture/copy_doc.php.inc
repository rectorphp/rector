<?php declare(strict_types = 1);

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

class Y {}
class Z {}

final class CopyDoc
{
	public Y $y;

	/**
	 * @var Z[]
	 * @Assert\Valid()
	 * @Assert\NotBlank()
	 */
	public array $z = [];

	/**
	 * @param Z[] $z
	 */
	public function __construct(Y $y, array $z = [])
	{
		$this->y = $y;
		$this->z = $z;
	}
}
?>
-----
<?php declare(strict_types = 1);

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

class Y {}
class Z {}

final class CopyDoc
{
	/**
	 * @param Z[] $z
	 */
	public function __construct(
		public Y $y,
		/**
		 * @Assert\Valid()
		 * @Assert\NotBlank()
		 */
		public array $z = []
	)
	{
	}
}
?>
