<?php

declare (strict_types=1);
namespace Rector\Unambiguous\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeAnalyzer\ClassMethodAndPropertyAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @experimental since 2025-11
 *
 * @see \Rector\Tests\Unambiguous\Rector\Class_\RemoveReturnThisFromSetterClassMethodRector\RemoveReturnThisFromSetterClassMethodRectorTest
 */
final class RemoveReturnThisFromSetterClassMethodRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ClassMethodAndPropertyAnalyzer $classMethodAndPropertyAnalyzer;
    public function __construct(ClassMethodAndPropertyAnalyzer $classMethodAndPropertyAnalyzer)
    {
        $this->classMethodAndPropertyAnalyzer = $classMethodAndPropertyAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove return $this from setter method, to make explicit setter without return value. Goal is to make code unambiguous with one way to set value', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private $name;

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    private $name;

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Class_>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Class_
    {
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($classMethod->isMagic()) {
                continue;
            }
            // skip void return type
            if ($classMethod->returnType instanceof Identifier && $this->isName($classMethod->returnType, 'void')) {
                continue;
            }
            if (count($classMethod->params) !== 1) {
                continue;
            }
            $soleParam = $classMethod->params[0];
            // magic spread
            if ($soleParam->variadic) {
                continue;
            }
            $paramName = $this->getName($soleParam->var);
            if (!is_string($paramName)) {
                continue;
            }
            if (!$this->classMethodAndPropertyAnalyzer->hasPropertyAssignWithReturnThis($classMethod)) {
                continue;
            }
            // remove 2nd stmts, that is "return $this;"
            unset($classMethod->stmts[1]);
            $classMethod->returnType = new Identifier('void');
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
