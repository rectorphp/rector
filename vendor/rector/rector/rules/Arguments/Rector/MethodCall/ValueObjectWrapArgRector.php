<?php

declare (strict_types=1);
namespace Rector\Arguments\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\ObjectType;
use Rector\Arguments\ValueObject\ValueObjectWrapArg;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Arguments\Rector\MethodCall\ValueObjectWrapArgRector\ValueObjectWrapArgRectorTest
 */
final class ValueObjectWrapArgRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const VALUE_OBJECT_WRAP_ARGS = 'value_object_wrap_args';
    /**
     * @var ValueObjectWrapArg[]
     */
    private $valueObjectWrapArgs = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Wrap argument with value object, if not yet the type', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->someMethod(1000);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->someMethod(new Number(1000));
    }
}
CODE_SAMPLE
, [self::VALUE_OBJECT_WRAP_ARGS => [new \Rector\Arguments\ValueObject\ValueObjectWrapArg('SomeClass', 'someMethod', 0, 'Number')]])]);
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
        foreach ($this->valueObjectWrapArgs as $valueObjectWrapArg) {
            $desiredArg = $this->matchArg($node, $valueObjectWrapArg);
            if (!$desiredArg instanceof \PhpParser\Node\Arg) {
                continue;
            }
            $argValue = $desiredArg->value;
            $argValueType = $this->getStaticType($argValue);
            $newObjectType = $valueObjectWrapArg->getNewType();
            if ($newObjectType->isSuperTypeOf($argValueType)->yes()) {
                continue;
            }
            if ($argValueType instanceof \PHPStan\Type\Constant\ConstantArrayType) {
                return $this->refactorArray($desiredArg, $newObjectType, $node);
            }
            $value = $desiredArg->value;
            $desiredArg->value = new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name\FullyQualified($newObjectType->getClassName()), [new \PhpParser\Node\Arg($value)]);
            return $node;
        }
        return null;
    }
    /**
     * @param array<string, ValueObjectWrapArg[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $this->valueObjectWrapArgs = $configuration[self::VALUE_OBJECT_WRAP_ARGS] ?? [];
    }
    private function wrapInNewWithType(\PHPStan\Type\ObjectType $newObjectType, \PhpParser\Node\Expr $expr) : \PhpParser\Node\Expr\New_
    {
        $fullyQualified = new \PhpParser\Node\Name\FullyQualified($newObjectType->getClassName());
        return new \PhpParser\Node\Expr\New_($fullyQualified, [new \PhpParser\Node\Arg($expr)]);
    }
    private function refactorArray(\PhpParser\Node\Arg $desiredArg, \PHPStan\Type\ObjectType $newObjectType, \PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Expr\MethodCall
    {
        if ($desiredArg->value instanceof \PhpParser\Node\Expr\Array_) {
            foreach ($desiredArg->value->items as $arrayItem) {
                if (!$arrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                    continue;
                }
                $arrayItem->value = $this->wrapInNewWithType($newObjectType, $arrayItem->value);
            }
            return $methodCall;
        }
        return null;
    }
    private function matchArg(\PhpParser\Node\Expr\MethodCall $methodCall, \Rector\Arguments\ValueObject\ValueObjectWrapArg $valueObjectWrapArg) : ?\PhpParser\Node\Arg
    {
        if (!$this->isObjectType($methodCall->var, $valueObjectWrapArg->getObjectType())) {
            return null;
        }
        if (!$this->isName($methodCall->name, $valueObjectWrapArg->getMethodName())) {
            return null;
        }
        return $methodCall->args[$valueObjectWrapArg->getArgPosition()] ?? null;
    }
}
