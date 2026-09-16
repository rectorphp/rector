<?php

declare (strict_types=1);
namespace Rector\Php86\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://wiki.php.net/rfc/readonly_property_defaults
 * @see \Rector\Tests\Php86\Rector\Class_\ConstructorReadonlyAssignToDefaultRector\ConstructorReadonlyAssignToDefaultRectorTest
 */
final class ConstructorReadonlyAssignToDefaultRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Move a constant constructor assignment of a readonly property to a property default value', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public readonly int $number;

    public function __construct()
    {
        $this->number = 100;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public readonly int $number = 100;

    public function __construct()
    {
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return null;
        }
        if ($constructClassMethod->stmts === null) {
            return null;
        }
        $keysToRemove = [];
        foreach ($node->getProperties() as $property) {
            if (!$this->isDefaultableReadonlyProperty($property)) {
                continue;
            }
            $propertyName = $this->getName($property->props[0]);
            $assignKey = $this->matchConstantAssignKey($constructClassMethod->stmts, $propertyName);
            if ($assignKey === null) {
                continue;
            }
            $expression = $constructClassMethod->stmts[$assignKey];
            if (!$expression instanceof Expression || !$expression->expr instanceof Assign) {
                continue;
            }
            $property->props[0]->default = $expression->expr->expr;
            $keysToRemove[] = $assignKey;
        }
        if ($keysToRemove === []) {
            return null;
        }
        foreach ($keysToRemove as $keyToRemove) {
            unset($constructClassMethod->stmts[$keyToRemove]);
        }
        $constructClassMethod->stmts = array_values($constructClassMethod->stmts);
        return $node;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::READONLY_PROPERTY_DEFAULT_VALUE;
    }
    private function isDefaultableReadonlyProperty(Property $property): bool
    {
        if (!$property->isReadonly()) {
            return \false;
        }
        if ($property->isStatic()) {
            return \false;
        }
        if ($property->hooks !== []) {
            return \false;
        }
        if (count($property->props) !== 1) {
            return \false;
        }
        return !$property->props[0]->default instanceof Expr;
    }
    /**
     * @param Node\Stmt[] $stmts
     */
    private function matchConstantAssignKey(array $stmts, string $propertyName): ?int
    {
        foreach ($stmts as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $assign = $stmt->expr;
            if (!$assign->var instanceof PropertyFetch) {
                continue;
            }
            if (!$this->isName($assign->var->var, 'this')) {
                continue;
            }
            if (!$this->isName($assign->var->name, $propertyName)) {
                continue;
            }
            if (!$this->isConstantValue($assign->expr)) {
                return null;
            }
            return $key;
        }
        return null;
    }
    private function isConstantValue(Expr $expr): bool
    {
        if ($expr instanceof Scalar) {
            return \true;
        }
        if ($expr instanceof ConstFetch || $expr instanceof ClassConstFetch) {
            return \true;
        }
        if ($expr instanceof UnaryMinus || $expr instanceof UnaryPlus) {
            return $this->isConstantValue($expr->expr);
        }
        return \false;
    }
}
