<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\PHPUnit\NodeFinder\DataProviderClassMethodFinder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\RemoveDataProviderParamKeysRector\RemoveDataProviderParamKeysRectorTest
 */
final class RemoveDataProviderParamKeysRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private DataProviderClassMethodFinder $dataProviderClassMethodFinder;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, DataProviderClassMethodFinder $dataProviderClassMethodFinder)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->dataProviderClassMethodFinder = $dataProviderClassMethodFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove data provider keys, that should match param names, as order is good enough', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\DataProvider;

final class SomeServiceTest extends TestCase
{
    #[DataProvider('provideData')]
    public function test(string $name): void
    {
    }

    public function provideData(): array
    {
        return [
            'name' => ['Tom'],
        ];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\DataProvider;

final class SomeServiceTest extends TestCase
{
    #[DataProvider('provideData')]
    public function test(string $name): void
    {
    }

    public function provideData(): array
    {
        return [
            ['Tom'],
        ];
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
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $dataProviderClassMethods = $this->dataProviderClassMethodFinder->find($node);
        $hasChanged = \false;
        foreach ($dataProviderClassMethods as $dataProviderClassMethod) {
            // 1. remove array keys
            foreach ((array) $dataProviderClassMethod->stmts as $classMethodStmt) {
                if (!$classMethodStmt instanceof Return_) {
                    continue;
                }
                if (!$classMethodStmt->expr instanceof Array_) {
                    continue;
                }
                $returnedArray = $classMethodStmt->expr;
                foreach ($returnedArray->items as $arrayItem) {
                    if (!$arrayItem->value instanceof Array_) {
                        continue;
                    }
                    $nestedArray = $arrayItem->value;
                    foreach ($nestedArray->items as $nestedArrayItem) {
                        if (!$nestedArrayItem->key instanceof Expr) {
                            continue;
                        }
                        // remove key
                        $nestedArrayItem->key = null;
                        $hasChanged = \true;
                    }
                }
            }
            // 2. remove yield keys
            foreach ((array) $dataProviderClassMethod->stmts as $classMethodStmt) {
                if (!$classMethodStmt instanceof Expression) {
                    continue;
                }
                if (!$classMethodStmt->expr instanceof Yield_) {
                    continue;
                }
                $yield = $classMethodStmt->expr;
                if (!$yield->value instanceof Array_) {
                    continue;
                }
                $yieldArray = $yield->value;
                foreach ($yieldArray->items as $yieldArrayItem) {
                    if (!$yieldArrayItem->key instanceof Expr) {
                        continue;
                    }
                    // remove key
                    $yieldArrayItem->key = null;
                    $hasChanged = \true;
                }
            }
        }
        if ($hasChanged === \false) {
            return null;
        }
        return $node;
    }
}
