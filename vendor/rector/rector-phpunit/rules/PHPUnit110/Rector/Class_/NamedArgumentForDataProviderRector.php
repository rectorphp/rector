<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit110\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/sebastianbergmann/phpunit/blob/11.0.0/ChangeLog-11.0.md
 * @see https://github.com/sebastianbergmann/phpunit/pull/5225
 * @see \Rector\PHPUnit\Tests\PHPUnit100\Rector\Class_\StaticDataProviderClassMethodRector\StaticDataProviderClassMethodRectorTest
 */
final class NamedArgumentForDataProviderRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change the array-index names to the argument name of the dataProvider', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public static function dataProviderArray(): array
    {
        return [
            [
                'keyA' => true,
                'keyB' => false,
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderArray')]
    public function testFilter(bool $changeToKeyA, bool $changeToKeyB): void
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public static function dataProviderArray(): array
    {
        return [
            [
                'changeToKeyA' => true,
                'changeToKeyB' => false,
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderArray')]
    public function testFilter(bool $changeToKeyA, bool $changeToKeyB): void
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
    public function refactor(Node $node) : ?\PhpParser\Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $wasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if (!$classMethod->isPublic()) {
                continue;
            }
            if ($classMethod->getParams() === []) {
                continue;
            }
            $dataProviderMethodNames = $this->getDataProviderMethodNames($classMethod);
            if ($dataProviderMethodNames === []) {
                continue;
            }
            foreach ($dataProviderMethodNames as $dataProviderMethodName) {
                $dataProviderMethod = $node->getMethod($dataProviderMethodName);
                if ($dataProviderMethod === null) {
                    continue;
                }
                $namedArgumentsFromTestClass = $this->getNamedArguments($classMethod);
                foreach ($this->extractDataProviderArrayItem($dataProviderMethod) as $dataProviderArrayItem) {
                    if ($this->refactorArrayKey($dataProviderArrayItem, $namedArgumentsFromTestClass)) {
                        $wasChanged = \true;
                    }
                }
            }
        }
        return $wasChanged ? $node : null;
    }
    /**
     * @return list<string>
     */
    public function getNamedArguments(ClassMethod $classMethod) : array
    {
        $dataProviderNameMapping = [];
        foreach ($classMethod->getParams() as $param) {
            if ($param->var instanceof Variable) {
                $dataProviderNameMapping[] = $this->getName($param->var);
            }
        }
        return \array_values(\array_filter($dataProviderNameMapping));
    }
    /**
     * @param list<Node\Stmt> $stmts
     * @return array<string, Array_>
     */
    public function getResolvedVariables(array $stmts) : array
    {
        $variables = [];
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            if (!$stmt->expr->var instanceof Variable) {
                continue;
            }
            if (!$stmt->expr->expr instanceof Array_) {
                continue;
            }
            $variables[$this->getName($stmt->expr->var)] = $stmt->expr->expr;
        }
        return $variables;
    }
    /**
     * @return list<string>
     */
    private function getDataProviderMethodNames(ClassMethod $classMethod) : array
    {
        $dataProviderMethodNames = [];
        $attributeClassName = 'PHPUnit\\Framework\\Attributes\\DataProvider';
        foreach ($classMethod->attrGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attribute) {
                if (!$this->isName($attribute->name, $attributeClassName)) {
                    continue;
                }
                foreach ($attribute->args as $arg) {
                    if ($arg->value instanceof String_) {
                        $dataProviderMethodNames[] = $arg->value->value;
                    }
                }
            }
        }
        return $dataProviderMethodNames;
    }
    /**
     * @param list<string> $dataProviderNameMapping
     */
    private function refactorArrayKey(Array_ $array, array $dataProviderNameMapping) : bool
    {
        $hasChanged = \false;
        $needToSetAllKeyNames = \false;
        $allArrayKeyNames = [];
        foreach ($array->items as $arrayItem) {
            if ((($nullsafeVariable1 = $arrayItem) ? $nullsafeVariable1->key : null) instanceof String_) {
                $needToSetAllKeyNames = \true;
                $allArrayKeyNames[] = $arrayItem->key->value;
            }
        }
        // Skip already modified keys because they could be in a different order
        if (\array_intersect($dataProviderNameMapping, $allArrayKeyNames) === $dataProviderNameMapping) {
            return \false;
        }
        foreach ($array->items as $arrayIndex => $arrayItem) {
            if ($arrayItem === null) {
                continue;
            }
            if (!isset($dataProviderNameMapping[$arrayIndex])) {
                continue;
            }
            if ($arrayItem->key === null && $needToSetAllKeyNames) {
                $arrayItem->key = String_::fromString($dataProviderNameMapping[$arrayIndex]);
            }
            if ($arrayItem->key instanceof String_ && $arrayItem->key->value !== $dataProviderNameMapping[$arrayIndex]) {
                $arrayItem->key->value = $dataProviderNameMapping[$arrayIndex];
                $hasChanged = \true;
            }
        }
        return $hasChanged;
    }
    /**
     * @return iterable<Array_>
     */
    private function extractDataProviderArrayItem(ClassMethod $classMethod) : iterable
    {
        $stmts = $classMethod->getStmts() ?? [];
        $resolvedVariables = $this->getResolvedVariables($stmts);
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Expression && $stmt->expr instanceof Yield_) {
                $arrayItem = $stmt->expr->value;
                if ($arrayItem instanceof Array_) {
                    (yield $arrayItem);
                }
            }
            if ($stmt instanceof Return_ && $stmt->expr instanceof Array_) {
                $dataProviderTestCases = $stmt->expr;
                foreach ($dataProviderTestCases->items as $dataProviderTestCase) {
                    $arrayItem = ($nullsafeVariable2 = $dataProviderTestCase) ? $nullsafeVariable2->value : null;
                    if ($arrayItem instanceof Array_) {
                        (yield $arrayItem);
                    }
                    $variableName = $arrayItem === null ? null : $this->getName($arrayItem);
                    if ($arrayItem instanceof Variable && $variableName !== null && isset($resolvedVariables[$variableName])) {
                        $dataProviderList = $resolvedVariables[$variableName];
                        foreach ($dataProviderList->items as $dataProviderItem) {
                            if ((($nullsafeVariable3 = $dataProviderItem) ? $nullsafeVariable3->value : null) instanceof Array_) {
                                (yield $dataProviderItem->value);
                            }
                        }
                    }
                }
            }
        }
    }
}
