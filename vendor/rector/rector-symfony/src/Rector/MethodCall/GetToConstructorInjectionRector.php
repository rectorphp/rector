<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\NodeAnalyzer\DependencyInjectionMethodCallAnalyzer;
use Rector\Symfony\TypeAnalyzer\ContainerAwareAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\MethodCall\GetToConstructorInjectionRector\GetToConstructorInjectionRectorTest
 */
final class GetToConstructorInjectionRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\DependencyInjectionMethodCallAnalyzer
     */
    private $dependencyInjectionMethodCallAnalyzer;
    /**
     * @readonly
     * @var \Rector\Symfony\TypeAnalyzer\ContainerAwareAnalyzer
     */
    private $containerAwareAnalyzer;
    public function __construct(\Rector\Symfony\NodeAnalyzer\DependencyInjectionMethodCallAnalyzer $dependencyInjectionMethodCallAnalyzer, \Rector\Symfony\TypeAnalyzer\ContainerAwareAnalyzer $containerAwareAnalyzer)
    {
        $this->dependencyInjectionMethodCallAnalyzer = $dependencyInjectionMethodCallAnalyzer;
        $this->containerAwareAnalyzer = $containerAwareAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns fetching of dependencies via `$this->get()` to constructor injection in Command and Controller', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class SomeController extend Controller
{
    public function someMethod()
    {
        // ...
        $this->get('some_service');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class SomeController extend Controller
{
    public function __construct(SomeService $someService)
    {
        $this->someService = $someService;
    }

    public function someMethod()
    {
        $this->someService;
    }
}
CODE_SAMPLE
)]);
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
        if (!$this->containerAwareAnalyzer->isGetMethodAwareType($node->var)) {
            return null;
        }
        if (!$this->isName($node->name, 'get')) {
            return null;
        }
        return $this->dependencyInjectionMethodCallAnalyzer->replaceMethodCallWithPropertyFetchAndDependency($node);
    }
}
