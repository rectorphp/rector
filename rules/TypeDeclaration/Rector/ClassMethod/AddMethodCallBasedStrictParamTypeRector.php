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
final class AddMethodCallBasedStrictParamTypeRector extends AbstractRector
{
    /**
     * @var int
     */
    private const MAX_UNION_TYPES = 3;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\CallTypesResolver
     */
    private $callTypesResolver;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ClassMethodParamTypeCompleter
     */
    private $classMethodParamTypeCompleter;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\NodeFinder\LocalMethodCallFinder
     */
    private $localMethodCallFinder;
    public function __construct(CallTypesResolver $callTypesResolver, ClassMethodParamTypeCompleter $classMethodParamTypeCompleter, LocalMethodCallFinder $localMethodCallFinder)
    {
        $this->callTypesResolver = $callTypesResolver;
        $this->classMethodParamTypeCompleter = $classMethodParamTypeCompleter;
        $this->localMethodCallFinder = $localMethodCallFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change private method param type to strict type, based on passed strict types', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(int $value)
    {
        $this->resolve($value);
    }

    private function resolve($value)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(int $value)
    {
        $this->resolve($value);
    }

    private function resolve(int $value)
    {
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->params === []) {
            return null;
        }
        if (!$node->isPrivate()) {
            return null;
        }
        $methodCalls = $this->localMethodCallFinder->match($node);
        $classMethodParameterTypes = $this->callTypesResolver->resolveStrictTypesFromCalls($methodCalls);
        return $this->classMethodParamTypeCompleter->complete($node, $classMethodParameterTypes, self::MAX_UNION_TYPES);
    }
}
