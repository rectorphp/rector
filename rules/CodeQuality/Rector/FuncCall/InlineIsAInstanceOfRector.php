<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodeQuality\Rector\FuncCall;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\ClassConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Expr\Instanceof_;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PHPStan\Type\Generic\GenericClassStringType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\ObjectWithoutClassType;
use RectorPrefix20220606\PHPStan\Type\TypeWithClassName;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\InlineIsAInstanceOfRector\InlineIsAInstanceOfRectorTest
 */
final class InlineIsAInstanceOfRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change is_a() with object and class name check to instanceof', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(object $object)
    {
        return is_a($object, SomeType::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(object $object)
    {
        return $object instanceof SomeType;
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node->name, 'is_a')) {
            return null;
        }
        $args = $node->getArgs();
        $firstArgValue = $args[0]->value;
        if (!$this->isFirstObjectType($firstArgValue)) {
            return null;
        }
        $className = $this->resolveClassName($args[1]->value);
        if ($className === null) {
            return null;
        }
        return new Instanceof_($firstArgValue, new FullyQualified($className));
    }
    private function resolveClassName(Expr $expr) : ?string
    {
        if (!$expr instanceof ClassConstFetch) {
            return null;
        }
        $type = $this->getType($expr);
        if ($type instanceof GenericClassStringType) {
            $type = $type->getGenericType();
        }
        if (!$type instanceof TypeWithClassName) {
            return null;
        }
        return $type->getClassName();
    }
    private function isFirstObjectType(Expr $expr) : bool
    {
        $exprType = $this->getType($expr);
        if ($exprType instanceof ObjectWithoutClassType) {
            return \true;
        }
        return $exprType instanceof ObjectType;
    }
}
