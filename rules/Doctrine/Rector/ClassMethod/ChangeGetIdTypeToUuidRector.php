<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\Doctrine\Rector\ClassMethod\ChangeGetIdTypeToUuidRector\ChangeGetIdTypeToUuidRectorTest
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
        if (! $this->doctrineDocBlockResolver->isInDoctrineEntityClass($node)) {
            return null;
        }

        if (! $this->isName($node, 'getId')) {
            return null;
        }

        if ($this->hasUuidReturnType($node)) {
            return null;
        }

        $node->returnType = new FullyQualified('Ramsey\Uuid\UuidInterface');

        return $node;
    }

    private function hasUuidReturnType(ClassMethod $classMethod): bool
    {
        if ($classMethod->returnType === null) {
            return false;
        }

        return $this->isName($classMethod->returnType, 'Ramsey\Uuid\UuidInterface');
    }
}
