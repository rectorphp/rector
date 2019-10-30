<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Ramsey\Uuid\UuidInterface;
use Rector\DeadCode\Doctrine\DoctrineEntityManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Doctrine\Tests\Rector\ClassMethod\ChangeReturnTypeOfClassMethodWithGetIdRector\ChangeReturnTypeOfClassMethodWithGetIdRectorTest
 */
final class ChangeReturnTypeOfClassMethodWithGetIdRector extends AbstractRector
{
    /**
     * @var DoctrineEntityManipulator
     */
    private $doctrineEntityManipulator;

    public function __construct(DoctrineEntityManipulator $doctrineEntityManipulator)
    {
        $this->doctrineEntityManipulator = $doctrineEntityManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change getUuid() method call to getId()', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function getBuildingId(): int
    {
        $building = new Building();

        return $building->getId();
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function getBuildingId(): \Ramsey\Uuid\UuidInterface
    {
        $building = new Building();

        return $building->getId();
    }
}
PHP
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
        if ($node->returnType === null) {
            return null;
        }

        $hasEntityGetIdMethodCall = $this->hasEntityGetIdMethodCall($node);
        if (! $hasEntityGetIdMethodCall) {
            return null;
        }

        $node->returnType = new FullyQualified(UuidInterface::class);

        return $node;
    }

    private function hasEntityGetIdMethodCall(Node $node): bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $node->stmts, function (Node $node): bool {
            if (! $node instanceof Return_) {
                return false;
            }

            if ($node->expr === null) {
                return false;
            }

            return $this->doctrineEntityManipulator->isMethodCallOnDoctrineEntity($node->expr, 'getId');
        });
    }
}
