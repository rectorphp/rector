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
use Rector\BetterPhpDocParser\NodeAnalyzer\DocBlockAnalyzer;
use Rector\Node\Attribute;
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

    public function isCandidate(Node $node): bool
    {
        if (! $this->isInTestClass($node)) {
            return false;
        }

        if (! $node instanceof ClassMethod) {
            return false;
        }

        if (! $this->isInProvideMethod($node)) {
            return false;
        }

        if (! $this->hasClassMethodReturnArrayOfArrays($node)) {
            return false;
        }

        return true;
    }

    /**
     * @param ClassMethod $classMethodNode
     */
    public function refactor(Node $classMethodNode): ?Node
    {
        // 1. change return typehint
        $classMethodNode->returnType = new FullyQualified(Iterator::class);

        $yieldNodes = [];

        // 2. turn array items to yield
        foreach ($classMethodNode->stmts as $key => $stmt) {
            if (! $stmt instanceof Return_) {
                continue;
            }

            if (! $stmt->expr instanceof Array_) {
                continue;
            }

            $yieldNodes = $this->turnArrayToYieldNodes($stmt->expr);

            unset($classMethodNode->stmts[$key]);
        }

        $classMethodNode->stmts = array_merge($classMethodNode->stmts, $yieldNodes);

        // 3. remove doc block
        $this->docBlockAnalyzer->removeTagFromNode($classMethodNode, 'return');

        return $classMethodNode;
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

    /**
     * @return Yield_[]
     */
    private function turnArrayToYieldNodes(Array_ $arrayNode): array
    {
        $yieldNodes = [];

        foreach ($arrayNode->items as $arrayItem) {
            $expressionNode = new Expression(new Yield_($arrayItem->value));
            if ($arrayItem->getComments()) {
                $expressionNode->setAttribute(Attribute::COMMENTS, $arrayItem->getComments());
            }

            $yieldNodes[] = $expressionNode;
        }

        return $yieldNodes;
    }
}
