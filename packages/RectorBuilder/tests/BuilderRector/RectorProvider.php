<?php declare(strict_types=1);

namespace Rector\RectorBuilder\Tests\BuilderRector;

use PhpParser\NodeVisitor;
use Rector\Contract\Rector\RectorInterface;
use Rector\RectorBuilder\BuilderRectorFactory;
use Rector\RectorBuilder\Contract\RectorProviderInterface;

final class RectorProvider implements RectorProviderInterface
{
    /**
     * @var BuilderRectorFactory
     */
    private $builderRectorFactory;

    public function __construct(BuilderRectorFactory $builderRectorFactory)
    {
        $this->builderRectorFactory = $builderRectorFactory;
    }

    /**
     * @return RectorInterface[]|NodeVisitor[]
     */
    public function provide(): array
    {
        $validateToRedrawControlRector = $this->builderRectorFactory->create()
            ->matchMethodCallByType('Stub_Nette\Application\UI\Control')
            ->matchMethodName('validateControl')
            ->changeMethodNameTo('redrawControl')
            ->addArgument(1, false);

        return [$validateToRedrawControlRector];
    }
}
