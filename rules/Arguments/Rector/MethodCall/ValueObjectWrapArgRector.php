<?php

declare(strict_types=1);

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
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\Arguments\Rector\MethodCall\ValueObjectWrapArgRector\ValueObjectWrapArgRectorTest
 */
final class ValueObjectWrapArgRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const VALUE_OBJECT_WRAP_ARGS = 'value_object_wrap_args';

    /**
     * @var ValueObjectWrapArg[]
     */
    private array $valueObjectWrapArgs = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Wrap argument with value object, if not yet the type', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->someMethod(1000);
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->someMethod(new Number(1000));
    }
}
CODE_SAMPLE
,
                [
                    self::VALUE_OBJECT_WRAP_ARGS => [new ValueObjectWrapArg('SomeClass', 'someMethod', 0, 'Number')],
                ]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->valueObjectWrapArgs as $valueObjectWrapArg) {
            $desiredArg = $this->matchArg($node, $valueObjectWrapArg);
            if (! $desiredArg instanceof Arg) {
                continue;
            }

            $argValue = $desiredArg->value;
            $argValueType = $this->getStaticType($argValue);

            $newObjectType = $valueObjectWrapArg->getNewType();
            if ($newObjectType->isSuperTypeOf($argValueType)->yes()) {
                continue;
            }

            if ($argValueType instanceof ConstantArrayType) {
                return $this->refactorArray($desiredArg, $newObjectType, $node);
            }

            $value = $desiredArg->value;
            $desiredArg->value = new New_(new FullyQualified($newObjectType->getClassName()), [new Arg($value)]);

            return $node;
        }

        return null;
    }

    /**
     * @param array<string, ValueObjectWrapArg[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $valueObjectWrapArgs = $configuration[self::VALUE_OBJECT_WRAP_ARGS] ?? [];
        Assert::allIsInstanceOf($valueObjectWrapArgs, ValueObjectWrapArg::class);

        $this->valueObjectWrapArgs = $valueObjectWrapArgs;
    }

    private function wrapInNewWithType(ObjectType $newObjectType, Expr $expr): New_
    {
        $fullyQualified = new FullyQualified($newObjectType->getClassName());
        return new New_($fullyQualified, [new Arg($expr)]);
    }

    private function refactorArray(Arg $desiredArg, ObjectType $newObjectType, MethodCall $methodCall): ?MethodCall
    {
        if ($desiredArg->value instanceof Array_) {
            foreach ($desiredArg->value->items as $arrayItem) {
                if (! $arrayItem instanceof ArrayItem) {
                    continue;
                }

                $arrayItem->value = $this->wrapInNewWithType($newObjectType, $arrayItem->value);
            }

            return $methodCall;
        }

        return null;
    }

    private function matchArg(MethodCall $methodCall, ValueObjectWrapArg $valueObjectWrapArg): ?Arg
    {
        if (! $this->isObjectType($methodCall->var, $valueObjectWrapArg->getObjectType())) {
            return null;
        }

        if (! $this->isName($methodCall->name, $valueObjectWrapArg->getMethodName())) {
            return null;
        }

        return $methodCall->args[$valueObjectWrapArg->getArgPosition()] ?? null;
    }
}
