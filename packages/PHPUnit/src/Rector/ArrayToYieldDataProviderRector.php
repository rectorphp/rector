<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector;

use Iterator;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\PhpParser\Node\Maintainer\ClassMethodMaintainer;
use Rector\PhpParser\NodeTransformer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ArrayToYieldDataProviderRector extends AbstractPHPUnitRector
{
    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var NodeTransformer
     */
    private $nodeTransformer;

    /**
     * @var ClassMethodMaintainer
     */
    private $classMethodMaintainer;

    public function __construct(
        DocBlockAnalyzer $docBlockAnalyzer,
        NodeTransformer $nodeTransformer,
        ClassMethodMaintainer $classMethodMaintainer
    ) {
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->nodeTransformer = $nodeTransformer;
        $this->classMethodMaintainer = $classMethodMaintainer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns method data providers in PHPUnit from arrays to yield', [
            new CodeSample(
                <<<'CODE_SAMPLE'
/**
 * @return mixed[]
 */
public function provide(): array
{
    return [
        ['item']
    ]
}
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
/**
 */
public function provide(): Iterator
{
    yield ['item'];
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInTestClass($node)) {
            return null;
        }

        if (! $this->isDataProviderMethod($node)) {
            return null;
        }

        if (! $this->classMethodMaintainer->hasReturnArrayOfArrays($node)) {
            return null;
        }

        // 1. change return typehint
        $node->returnType = new FullyQualified(Iterator::class);

        $yieldNodes = [];

        // 2. turn array items to yield
        foreach ((array) $node->stmts as $key => $stmt) {
            if (! $stmt instanceof Return_) {
                continue;
            }

            if (! $stmt->expr instanceof Array_) {
                continue;
            }

            $yieldNodes = $this->nodeTransformer->transformArrayToYields($stmt->expr);

            unset($node->stmts[$key]);
        }

        $node->stmts = array_merge((array) $node->stmts, $yieldNodes);

        // 3. remove doc block
        $this->docBlockAnalyzer->removeTagFromNode($node, 'return');

        return $node;
    }

    private function isDataProviderMethod(ClassMethod $classMethodNode): bool
    {
        if (! $classMethodNode->isPublic()) {
            return false;
        }

        return $this->isName($classMethodNode, '#^(provide|dataProvider)*#');
    }
}
