<?php declare(strict_types=1);

namespace Rector\Rector\Architecture\PHPUnit;

use Iterator;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\ReflectionDocBlock\NodeAnalyzer\DocBlockAnalyzer;

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
               public function provide(): array
               {
                    return [
                        ['item']
                    ]
               }
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
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

            /** @var Array_ $arrayNode */
            $arrayNode = $stmt->expr;

            foreach ($arrayNode->items as $arrayItem) {
                $yieldNodes[] = new Expression(new Yield_($arrayItem->value));
            }

            unset($classMethodNode->stmts[$key]);
        }

        $classMethodNode->stmts = array_merge($classMethodNode->stmts, $yieldNodes);

        // 3. remove doc block
        $this->docBlockAnalyzer->removeAnnotationFromNode($classMethodNode, 'return', '');

        return $classMethodNode;
    }

    private function isInProvideMethod(ClassMethod $classMethodNode): bool
    {
        return (bool) Strings::match($classMethodNode->name->toString(), '#^provide*#');
    }

    private function hasClassMethodReturnArrayOfArrays(ClassMethod $classMethodNode): bool
    {
        $statements = $classMethodNode->stmts;
        if (! $statements) {
            return false;
        }

        foreach ($statements as $statement) {
            if ($statement instanceof Return_) {
                if (! $statement->expr instanceof Array_) {
                    return false;
                }

                return $this->isArrayOfArrays($statement->expr);
            }
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
}
