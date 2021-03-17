<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\ExpectationAnalyzer;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\PHPUnit\NodeFactory\ConsecutiveAssertionFactory;
use Rector\PHPUnit\ValueObject\ExpectationMock;
use Rector\PHPUnit\ValueObject\ExpectationMockCollection;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\ClassMethod\MigrateAtToConsecutiveExpectationsRector\MigrateAtToConsecutiveExpectationsRectorTest
 */
final class MigrateAtToConsecutiveExpectationsRector extends AbstractRector
{
    /**
     * @var ConsecutiveAssertionFactory
     */
    private $consecutiveAssertionFactory;

    /**
     * @var TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;

    /**
     * @var ExpectationAnalyzer
     */
    private $expectationAnalyzer;

    public function __construct(
        ConsecutiveAssertionFactory $consecutiveAssertionFactory,
        TestsNodeAnalyzer $testsNodeAnalyzer,
        ExpectationAnalyzer $expectationAnalyzer
    )
    {
        $this->consecutiveAssertionFactory = $consecutiveAssertionFactory;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->expectationAnalyzer = $expectationAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Migrates deprecated $this->at to $this->withConsecutive and $this->willReturnOnConsecutiveCalls',
            [
                new CodeSample(
                    '',
                    ''
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
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
        $stmts = $node->stmts;
        if ($stmts === null) {
            return null;
        }

        $expressions = array_filter($stmts, function (Stmt $expr) {
            return $expr instanceof Expression && $expr->expr instanceof MethodCall;
        });

        $expectationMockCollection = $this->expectationAnalyzer->getExpectationsFromExpressions($expressions);

        if (!$expectationMockCollection->hasExpectationMocks()) {
            return null;
        }

        $expectationCollections = $this->groupExpectationCollectionsByVariableName($expectationMockCollection);
        foreach ($expectationCollections as $expectationCollection) {
            $this->replaceExpectationNodes($expectationCollection);
        }

        return $node;
    }

    private function buildNewExpectation(ExpectationMockCollection $expectationMockCollection): MethodCall
    {
        $expectationMockCollection = $this->fillMissingAtIndexes($expectationMockCollection);
        return $this->consecutiveAssertionFactory->createAssertionFromExpectationMockCollection($expectationMockCollection);
    }

    private function fillMissingAtIndexes(ExpectationMockCollection $expectationMockCollection): ExpectationMockCollection
    {
        $var = $expectationMockCollection->getExpectationMocks()[0]->getExpectationVariable();

        // 0,1,2,3,4
        // min = 0 ; max = 4 ; count = 5
        // OK

        // 1,2,3,4
        // min = 1 ; max = 4 ; count = 4
        // ADD 0

        // OR

        // 3
        // min = 3; max = 3 ; count = 1
        // 0,1,2
        if ($expectationMockCollection->getLowestAtIndex() !== 0) {
            for ($i = 0; $i < $expectationMockCollection->getLowestAtIndex(); ++$i) {
                $expectationMockCollection->add(
                    new ExpectationMock($var, [], $i, null, [], null)
                );
            }
        }

        // 0,1,2,4
        // min = 0 ; max = 4 ; count = 4
        // ADD 3
        if ($expectationMockCollection->isMissingAtIndexBetweenHighestAndLowest()) {
            $existingIndexes = array_column($expectationMockCollection->getExpectationMocks(), 'index');
            for ($i = 1; $i < $expectationMockCollection->getHighestAtIndex(); ++$i) {
                if (!in_array($i, $existingIndexes, true)) {
                    $expectationMockCollection->add(
                        new ExpectationMock($var, [], $i, null, [], null)
                    );
                }
            }
        }
        return $expectationMockCollection;
    }

    private function replaceExpectationNodes(ExpectationMockCollection $expectationMockCollection): void
    {
        if ($this->shouldSkipReplacement($expectationMockCollection)) {
            return;
        }

        $endLines = array_map(static function (ExpectationMock $expectationMock) {
            $originalExpression = $expectationMock->originalExpression();
            return $originalExpression === null ? 0 : $originalExpression->getEndLine();
        }, $expectationMockCollection->getExpectationMocks());
        $max = max($endLines);

        foreach ($expectationMockCollection->getExpectationMocks() as $expectationMock) {
            $originalExpression = $expectationMock->originalExpression();
            if ($originalExpression === null) {
                continue;
            }
            if ($max > $originalExpression->getEndLine()) {
                $this->removeNode($originalExpression);
            } else {
                $originalExpression->expr = $this->buildNewExpectation($expectationMockCollection);
            }
        }
    }

    private function shouldSkipReplacement(ExpectationMockCollection $expectationMockCollection): bool
    {
        if (!$expectationMockCollection->hasReturnValues()) {
            return false;
        }

        if (!$expectationMockCollection->isExpectedMethodAlwaysTheSame()) {
            return true;
        }

        if ($expectationMockCollection->hasMissingAtIndexes()) {
            return true;
        }

        if ($expectationMockCollection->hasMissingReturnValues()) {
            return true;
        }

        return false;
    }

    /**
     * @return ExpectationMockCollection[]
     */
    private function groupExpectationCollectionsByVariableName(ExpectationMockCollection $expectationMockCollection): array
    {
        $groupedByVariable = [];
        foreach ($expectationMockCollection->getExpectationMocks() as $expectationMock) {
            $variable = $expectationMock->getExpectationVariable();
            if (!is_string($variable->name)) {
                continue;
            }
            if (!isset($groupedByVariable[$variable->name])) {
                $groupedByVariable[$variable->name] = new ExpectationMockCollection();
            }
            $groupedByVariable[$variable->name]->add($expectationMock);
        }

        return array_values($groupedByVariable);
    }
}
