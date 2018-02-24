<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Nette\Application\ValidateControlRector;

use Rector\Contract\Rector\RectorInterface;
use Rector\RectorBuilder\BuilderRectorFactory;
use Rector\RectorBuilder\Contract\RectorProviderInterface;

final class RectorProvider implements RectorProviderInterface
{
    /**
     * @var BuilderRectorFactory
     */
    private $BuilderRectorBuilder;

    public function __construct(BuilderRectorFactory $BuilderRectorBuilder)
    {
        $this->BuilderRectorBuilder = $BuilderRectorBuilder;
    }

    public function provide(): RectorInterface
    {
        return $this->BuilderRectorBuilder->create()
            ->matchMethodCallByType('Stub_Nette\Application\UI\Control')
            ->matchMethodName('validateControl')
            ->changeMethodNameTo('redrawControl')
            ->addArgument(1, false);
    }
}
