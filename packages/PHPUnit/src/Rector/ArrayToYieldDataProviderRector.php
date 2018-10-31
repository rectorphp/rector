<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector;

use Iterator;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ArrayToYieldDataProviderRector extends AbstractPHPUnitRector
{
    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    public function __construct(DocBlockAnalyzer $docBlockAnalyzer)
    {
        $this->docBlockAnalyzer = $docBlockAnalyzer;
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

        if (! $this->isInProvideMethod($node)) {
            return null;
        }

        if (! $this->hasClassMethodReturnArrayOfArrays($node)) {
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

            $yieldNodes = $this->turnArrayToYieldNodes($stmt->expr);

            unset($node->stmts[$key]);
        }

        $node->stmts = array_merge((array) $node->stmts, $yieldNodes);

        // 3. remove doc block
        $this->docBlockAnalyzer->removeTagFromNode($node, 'return');

        return $node;
    }

    private function isInProvideMethod(ClassMethod $classMethodNode): bool
    {
        if (! $classMethodNode->isPublic()) {
            return false;
        }

        return (bool) Strings::match($classMethodNode->name, '#^(provide|dataProvider)*#');
    }

    private function hasClassMethodReturnArrayOfArrays(ClassMethod $classMethodNode): bool
    {
        $statements = $classMethodNode->stmts;
        if (! $statements) {
            return false;
        }

        foreach ($statements as $statement) {
            if (! $statement instanceof Return_) {
                continue;
            }

            if (! $statement->expr instanceof Array_) {
                return false;
            }

            return $this->isArrayOfArrays($statement->expr);
        }

        return false;
    }

    /**
     * @return Expression[]
     */
    private function turnArrayToYieldNodes(Array_ $arrayNode): array
    {
        $yieldNodes = [];

        foreach ($arrayNode->items as $arrayItem) {
            $expressionNode = new Expression(new Yield_($arrayItem->value));
            if ($arrayItem->getComments()) {
                $expressionNode->setAttribute('comments', $arrayItem->getComments());
            }

            $yieldNodes[] = $expressionNode;
        }

        return $yieldNodes;
    }

    private function isArrayOfArrays(Node $node): bool
    {
        if (! $node instanceof Array_) {
            return false;
        }

        foreach ($node->items as $arrayItem) {
            if (! $arrayItem->value instanceof Array_) {
                return false;
            }
        }

        return true;
    }
}
