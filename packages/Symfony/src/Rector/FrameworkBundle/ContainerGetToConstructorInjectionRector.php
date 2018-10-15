<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\FrameworkBundle;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\NodeTypeResolver\Node\Attribute;
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
    private $containerAwareParentTypes = [];

    /**
     * @param string[] $containerAwareParentTypes
     */
    public function __construct(array $containerAwareParentTypes = [
        'Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand',
        'Symfony\Bundle\FrameworkBundle\Controller\Controller',
    ])
    {
        $this->containerAwareParentTypes = $containerAwareParentTypes;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns fetching of dependencies via `$container->get()` in ContainerAware to constructor injection in Command and Controller in Symfony',
            [
                new CodeSample(
<<<'CODE_SAMPLE'
final class SomeCommand extends ContainerAwareCommand
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
final class SomeCommand extends Command
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

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        if (! $this->isType($methodCallNode, 'Symfony\Component\DependencyInjection\ContainerInterface')) {
            return null;
        }

        if (! $this->isName($methodCallNode, 'get')) {
            return null;
        }

        $parentClassName = $methodCallNode->getAttribute(Attribute::PARENT_CLASS_NAME);
        if (! in_array($parentClassName, $this->containerAwareParentTypes, true)) {
            return null;
        }

        return $this->processMethodCallNode($methodCallNode);
    }
}
