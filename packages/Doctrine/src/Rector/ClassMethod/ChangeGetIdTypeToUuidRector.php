<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use Ramsey\Uuid\UuidInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\Doctrine\Tests\Rector\ClassMethod\ChangeGetIdTypeToUuidRector\ChangeGetIdTypeToUuidRectorTest
 */
final class ChangeGetIdTypeToUuidRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change return type of getId() to uuid interface');
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
        if (! $this->isInDoctrineEntityClass($node)) {
            return null;
        }

        if (! $this->isName($node, 'getId')) {
            return null;
        }

        if ($this->hasUuidReturnType($node)) {
            return null;
        }

        $node->returnType = new FullyQualified(UuidInterface::class);

        return $node;
    }

    private function hasUuidReturnType(Node $node): bool
    {
        if ($node->returnType === null) {
            return false;
        }

        return $this->isName($node->returnType, UuidInterface::class);
    }
}
