<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\FrameworkBundle;

use PhpParser\Node;
use Rector\Node\Attribute;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#console
 */
final class ContainerGetToConstructorInjectionRector extends AbstractToConstructorInjectionRector
{
    /**
     * @var string[]
     */
    private $containerAwareParentTypes = [
        'Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand',
        'Symfony\Bundle\FrameworkBundle\Controller\Controller',
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns fetching of dependencies via $container->get() in ContainerAware to constructor injection in Command and Controller in Symfony',
            [
                new CodeSample(
<<<'CODE_SAMPLE'
class MyCommand extends ContainerAwareCommand
{
    public function someMethod()
    {
        // ...
        $this->getContainer()->get('some_service');
        $this->container->get('some_service');
    }
}
CODE_SAMPLE
                    ,
<<<'CODE_SAMPLE'
class MyCommand extends Command
{
    public function __construct(SomeService $someService)
    {
        $this->someService = $someService;
    }

    public function someMethod()
    {
        // ...
        $this->someService;
        $this->someService;
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isTypeAndMethod(
            $node,
            'Symfony\Component\DependencyInjection\ContainerInterface',
            'get'
        )) {
            return false;
        }

        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);

        return in_array($parentClassName, $this->containerAwareParentTypes, true);
    }
}
