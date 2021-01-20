<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use Ramsey\Uuid\UuidInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\Doctrine\Tests\Rector\ClassMethod\ChangeGetIdTypeToUuidRector\ChangeGetIdTypeToUuidRectorTest
 */
final class ChangeGetIdTypeToUuidRector extends AbstractRector
{
    /**
     * @var DoctrineDocBlockResolver
     */
    private $doctrineDocBlockResolver;

    public function __construct(DoctrineDocBlockResolver $doctrineDocBlockResolver)
    {
        $this->doctrineDocBlockResolver = $doctrineDocBlockResolver;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change return type of getId() to uuid interface',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class GetId
{
    public function getId(): int
    {
        return $this->id;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class GetId
{
    public function getId(): \Ramsey\Uuid\UuidInterface
    {
        return $this->id;
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->doctrineDocBlockResolver->isInDoctrineEntityClass($node)) {
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

    private function hasUuidReturnType(ClassMethod $classMethod): bool
    {
        if ($classMethod->returnType === null) {
            return false;
        }

        return $this->isName($classMethod->returnType, UuidInterface::class);
    }
}
