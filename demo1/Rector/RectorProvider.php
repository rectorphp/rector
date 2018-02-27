<?php declare(strict_types=1);

namespace Demo1\Rector;

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
     * @return RectorInterface[]
     */
    public function provide(): array
    {
        $renameMethodRector = $this->builderRectorFactory->create()
            ->matchMethodCallByType('Demo1\PHPLive')
            ->matchMethodName('organizeConference')
            ->changeMethodNameTo('organizeInternationalConference');

        return [$renameMethodRector];
    }
}
