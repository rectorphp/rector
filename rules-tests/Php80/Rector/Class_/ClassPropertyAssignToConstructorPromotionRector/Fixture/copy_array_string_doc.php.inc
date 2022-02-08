<?php declare(strict_types = 1);

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

final class CopyArrayStringDoc
{
    /**
     * Comment
     * @var string[]
     */
    private array $map;

    public function __construct(array $map)
    {
        $this->map = $map;
    }
}

?>
-----
<?php declare(strict_types = 1);

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

final class CopyArrayStringDoc
{
    /**
     * @param string[] $map
     */
    public function __construct(
        /**
         * Comment
         */
        private array $map
    )
    {
    }
}

?>
