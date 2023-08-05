<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
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
    /**
     * @var int
     */
    private const MAX_UNION_TYPES = 3;
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        foreach ($node->getMethods() as $method) {
            if ($method->params === []) {
                continue;
            }
            $isPrivate = $node->isFinal() && !$node->extends instanceof Name && $node->implements === [] && $method->isProtected() || $method->isFinal() && !$node->extends instanceof Name && $node->implements === [] || $method->isPrivate();
            if (!$isPrivate) {
                continue;
            }
            if ($method->isPublic()) {
                continue;
            }
            $methodCalls = $this->localMethodCallFinder->match($node, $method);
            $classMethodParameterTypes = $this->callTypesResolver->resolveStrictTypesFromCalls($methodCalls);
            $classMethod = $this->classMethodParamTypeCompleter->complete($method, $classMethodParameterTypes, self::MAX_UNION_TYPES);
            if ($classMethod instanceof ClassMethod) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
