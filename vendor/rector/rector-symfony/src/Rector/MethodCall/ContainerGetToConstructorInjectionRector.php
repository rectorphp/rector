<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver;
use Rector\Symfony\NodeAnalyzer\DependencyInjectionMethodCallAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#console
 *
 * @see \Rector\Symfony\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\ContainerGetToConstructorInjectionRectorTest
 */
final class ContainerGetToConstructorInjectionRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const CONTAINER_AWARE_PARENT_TYPES = 'container_aware_parent_types';
    /**
     * @var string[]
     */
    private $containerAwareParentTypes = ['Symfony\\Bundle\\FrameworkBundle\\Command\\ContainerAwareCommand', 'Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller'];
    /**
     * @var \Rector\Symfony\NodeAnalyzer\DependencyInjectionMethodCallAnalyzer
     */
    private $dependencyInjectionMethodCallAnalyzer;
    /**
     * @var \Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver
     */
    private $parentClassScopeResolver;
    public function __construct(\Rector\Symfony\NodeAnalyzer\DependencyInjectionMethodCallAnalyzer $dependencyInjectionMethodCallAnalyzer, \Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver $parentClassScopeResolver)
    {
        $this->dependencyInjectionMethodCallAnalyzer = $dependencyInjectionMethodCallAnalyzer;
        $this->parentClassScopeResolver = $parentClassScopeResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns fetching of dependencies via `$container->get()` in ContainerAware to constructor injection in Command and Controller in Symfony', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
, [self::CONTAINER_AWARE_PARENT_TYPES => ['ContainerAwareParentClassName', 'ContainerAwareParentCommandClassName', 'ThisClassCallsMethodInConstructorClassName']])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('Symfony\\Component\\DependencyInjection\\ContainerInterface'))) {
            return null;
        }
        if (!$this->isName($node->name, 'get')) {
            return null;
        }
        $parentClassName = $this->parentClassScopeResolver->resolveParentClassName($node);
        if ($parentClassName === null) {
            return $this->dependencyInjectionMethodCallAnalyzer->replaceMethodCallWithPropertyFetchAndDependency($node);
        }
        if (\in_array($parentClassName, $this->containerAwareParentTypes, \true)) {
            return $this->dependencyInjectionMethodCallAnalyzer->replaceMethodCallWithPropertyFetchAndDependency($node);
        }
        return null;
    }
    public function configure(array $configuration) : void
    {
        $this->containerAwareParentTypes = $configuration[self::CONTAINER_AWARE_PARENT_TYPES] ?? [];
    }
}
