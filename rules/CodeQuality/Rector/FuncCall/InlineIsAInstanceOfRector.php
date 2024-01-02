<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\TypeWithClassName;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $args = $node->getArgs();
        $firstArgValue = $args[0]->value;
        if (!$this->isFirstObjectType($firstArgValue)) {
            return null;
        }
        /**
         * instanceof with Variable is ok, while on FuncCal with instanceof cause fatal error, see https://3v4l.org/IHb30
         */
        if ($args[1]->value instanceof Variable) {
            return new Instanceof_($firstArgValue, $args[1]->value);
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
