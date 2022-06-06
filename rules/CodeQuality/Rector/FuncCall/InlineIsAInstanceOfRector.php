<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\InlineIsAInstanceOfRector\InlineIsAInstanceOfRectorTest
 */
final class InlineIsAInstanceOfRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change is_a() with object and class name check to instanceof', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
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
        return new \PhpParser\Node\Expr\Instanceof_($firstArgValue, new \PhpParser\Node\Name\FullyQualified($className));
    }
    private function resolveClassName(\PhpParser\Node\Expr $expr) : ?string
    {
        if (!$expr instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return null;
        }
        $type = $this->getType($expr);
        if ($type instanceof \PHPStan\Type\Generic\GenericClassStringType) {
            $type = $type->getGenericType();
        }
        if (!$type instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        return $type->getClassName();
    }
    private function isFirstObjectType(\PhpParser\Node\Expr $expr) : bool
    {
        $exprType = $this->getType($expr);
        if ($exprType instanceof \PHPStan\Type\ObjectWithoutClassType) {
            return \true;
        }
        return $exprType instanceof \PHPStan\Type\ObjectType;
    }
}
