<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Application;

use PhpParser\NodeVisitor;
use Rector\Contract\Rector\RectorInterface;
use Rector\RectorBuilder\BuilderRectorFactory;
use Rector\RectorBuilder\Contract\RectorProviderInterface;

final class ValidateControlRectorProvider implements RectorProviderInterface
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
     * Before:
     * - $myControl->validateControl(?$snippet)
     *
     * After:
     * - $myControl->redrawControl(?$snippet, false);
     *
     * @return RectorInterface[]|NodeVisitor[]
     */
    public function provide(): array
    {
        $validateToRedrawControlRector = $this->builderRectorFactory->create()
            ->matchMethodCallByType('Nette\Application\UI\Control')
            ->matchMethodName('validateControl')
            ->changeMethodNameTo('redrawControl')
            ->addArgument(1, false);

        return [$validateToRedrawControlRector];
    }
}
