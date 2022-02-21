<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

use Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Symplify\Astral\PhpDocParser\Contract\PhpDocNodeVisitorInterface;

final class ParamAndVar
{
    /**
     * @var PhpDocNodeVisitorInterface[]
     */
    private $phpDocNodeVisitors = [];

    /**
     * @param BasePhpDocNodeVisitorInterface[] $phpDocNodeVisitors
     */
    public function __construct(array $phpDocNodeVisitors)
    {
        $this->phpDocNodeVisitors = $phpDocNodeVisitors;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

use Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Symplify\Astral\PhpDocParser\Contract\PhpDocNodeVisitorInterface;

final class ParamAndVar
{
    /**
     * @param BasePhpDocNodeVisitorInterface[] $phpDocNodeVisitors
     */
    public function __construct(private array $phpDocNodeVisitors)
    {
    }
}

?>
