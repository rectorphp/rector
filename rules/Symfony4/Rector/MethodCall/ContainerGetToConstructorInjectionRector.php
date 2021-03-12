<?php

declare(strict_types=1);

namespace Rector\Symfony4\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Symfony\NodeAnalyzer\DependencyInjectionMethodCallAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#console
 * @see \Rector\Symfony4\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\ContainerGetToConstructorInjectionRectorTest
 */
final class ContainerGetToConstructorInjectionRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const CONTAINER_AWARE_PARENT_TYPES = 'container_aware_parent_types';

    /**
     * @var string[]
     */
    private $containerAwareParentTypes = [
        'Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand',
        'Symfony\Bundle\FrameworkBundle\Controller\Controller',
    ];

    /**
     * @var DependencyInjectionMethodCallAnalyzer
     */
    private $dependencyInjectionMethodCallAnalyzer;

    public function __construct(DependencyInjectionMethodCallAnalyzer $dependencyInjectionMethodCallAnalyzer)
    {
        $this->dependencyInjectionMethodCallAnalyzer = $dependencyInjectionMethodCallAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Turns fetching of dependencies via `$container->get()` in ContainerAware to constructor injection in Command and Controller in Symfony',
            [
                new ConfiguredCodeSample(
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
,
                    [
                        self::CONTAINER_AWARE_PARENT_TYPES => [
                            'ContainerAwareParentClassName',
                            'ContainerAwareParentCommandClassName',
                            'ThisClassCallsMethodInConstructorClassName',
                        ],
                    ]
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectType(
            $node->var,
            new ObjectType('Symfony\Component\DependencyInjection\ContainerInterface')
        )) {
            return null;
        }

        if (! $this->isName($node->name, 'get')) {
            return null;
        }

        $parentClassName = $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if (! in_array($parentClassName, $this->containerAwareParentTypes, true)) {
            return null;
        }

        return $this->dependencyInjectionMethodCallAnalyzer->replaceMethodCallWithPropertyFetchAndDependency($node);
    }

    public function configure(array $configuration): void
    {
        $this->containerAwareParentTypes = $configuration[self::CONTAINER_AWARE_PARENT_TYPES] ?? [];
    }
}
