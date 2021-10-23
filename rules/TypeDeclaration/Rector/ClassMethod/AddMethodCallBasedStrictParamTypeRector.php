<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\NodeFinder\LocalMethodCallFinder;
use Rector\Core\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeAnalyzer\CallTypesResolver;
use Rector\TypeDeclaration\NodeAnalyzer\ClassMethodParamTypeCompleter;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symplify/phpstan-rules/blob/master/docs/rules_overview.md#checktypehintcallertyperule
 *
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\AddMethodCallBasedStrictParamTypeRectorTest
 */
final class AddMethodCallBasedStrictParamTypeRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\TypeDeclaration\NodeAnalyzer\CallTypesResolver
     */
    private $callTypesResolver;
    /**
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ClassMethodParamTypeCompleter
     */
    private $classMethodParamTypeCompleter;
    /**
     * @var \Rector\Core\PhpParser\NodeFinder\LocalMethodCallFinder
     */
    private $localMethodCallFinder;
    public function __construct(\Rector\TypeDeclaration\NodeAnalyzer\CallTypesResolver $callTypesResolver, \Rector\TypeDeclaration\NodeAnalyzer\ClassMethodParamTypeCompleter $classMethodParamTypeCompleter, \Rector\Core\PhpParser\NodeFinder\LocalMethodCallFinder $localMethodCallFinder)
    {
        $this->callTypesResolver = $callTypesResolver;
        $this->classMethodParamTypeCompleter = $classMethodParamTypeCompleter;
        $this->localMethodCallFinder = $localMethodCallFinder;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change param type to strict type of passed expression', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function getById($id)
    {
    }
}

class CallerClass
{
    public function run(SomeClass $someClass)
    {
        $someClass->getById($this->getId());
    }

    public function getId(): int
    {
        return 1000;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function getById(int $id)
    {
    }
}

class CallerClass
{
    public function run(SomeClass $someClass)
    {
        $someClass->getById($this->getId());
    }

    public function getId(): int
    {
        return 1000;
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node->params === []) {
            return null;
        }
        if (!$node->isPrivate()) {
            return null;
        }
        $methodCalls = $this->localMethodCallFinder->match($node);
        $classMethodParameterTypes = $this->callTypesResolver->resolveStrictTypesFromCalls($methodCalls);
        return $this->classMethodParamTypeCompleter->complete($node, $classMethodParameterTypes);
    }
}
