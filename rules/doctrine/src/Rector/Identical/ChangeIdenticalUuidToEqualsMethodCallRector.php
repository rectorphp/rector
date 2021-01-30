<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PHPStan\Type\ObjectType;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\Doctrine\DoctrineEntityManipulator;
use Rector\Php71\ValueObject\TwoNodeMatch;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Doctrine\Tests\Rector\Identical\ChangeIdenticalUuidToEqualsMethodCallRector\ChangeIdenticalUuidToEqualsMethodCallRectorTest
 */
final class ChangeIdenticalUuidToEqualsMethodCallRector extends AbstractRector
{
    /**
     * @var DoctrineEntityManipulator
     */
    private $doctrineEntityManipulator;

    public function __construct(DoctrineEntityManipulator $doctrineEntityManipulator)
    {
        $this->doctrineEntityManipulator = $doctrineEntityManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change $uuid === 1 to $uuid->equals(\Ramsey\Uuid\Uuid::fromString(1))',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function match($checkedId): int
    {
        $building = new Building();

        return $building->getId() === $checkedId;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function match($checkedId): int
    {
        $building = new Building();

        return $building->getId()->equals(\Ramsey\Uuid\Uuid::fromString($checkedId));
    }
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
        return [Identical::class];
    }

    /**
     * @param Identical $node
     */
    public function refactor(Node $node): ?Node
    {
        $twoNodeMatch = $this->matchEntityCallAndComparedVariable($node);
        if (! $twoNodeMatch instanceof TwoNodeMatch) {
            return null;
        }

        $entityMethodCall = $twoNodeMatch->getFirstExpr();
        $comparedVariable = $twoNodeMatch->getSecondExpr();

        $staticCall = $this->nodeFactory->createStaticCall(Uuid::class, 'fromString', [$comparedVariable]);

        return $this->nodeFactory->createMethodCall($entityMethodCall, 'equals', [$staticCall]);
    }

    private function matchEntityCallAndComparedVariable(Identical $identical): ?TwoNodeMatch
    {
        if ($this->doctrineEntityManipulator->isMethodCallOnDoctrineEntity($identical->left, 'getId')) {
            if ($this->isAlreadyUuidType($identical->right)) {
                return null;
            }

            return new TwoNodeMatch($identical->left, $identical->right);
        }

        if ($this->doctrineEntityManipulator->isMethodCallOnDoctrineEntity($identical->right, 'getId')) {
            if ($this->isAlreadyUuidType($identical->left)) {
                return null;
            }

            return new TwoNodeMatch($identical->right, $identical->left);
        }

        return null;
    }

    private function isAlreadyUuidType(Expr $expr): bool
    {
        $comparedValueObjectType = $this->getStaticType($expr);
        if (! $comparedValueObjectType instanceof ObjectType) {
            return false;
        }

        return $comparedValueObjectType->getClassName() === UuidInterface::class;
    }
}
