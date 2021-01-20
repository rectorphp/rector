<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
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
 * @see \Rector\Doctrine\Tests\Rector\ClassMethod\ChangeSetIdTypeToUuidRector\ChangeSetIdTypeToUuidRectorTest
 */
final class ChangeSetIdTypeToUuidRector extends AbstractRector
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
            'Change param type of setId() to uuid interface',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SetId
{
    private $id;

    public function setId(int $uuid): int
    {
        return $this->id = $uuid;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SetId
{
    private $id;

    public function setId(\Ramsey\Uuid\UuidInterface $uuid): int
    {
        return $this->id = $uuid;
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

        if (! $this->isName($node, 'setId')) {
            return null;
        }

        $this->renameUuidVariableToId($node);

        // is already set?
        if ($node->params[0]->type !== null) {
            $currentType = $this->getName($node->params[0]->type);
            if ($currentType === UuidInterface::class) {
                return null;
            }
        }

        $node->params[0]->type = new FullyQualified(UuidInterface::class);

        return $node;
    }

    private function renameUuidVariableToId(ClassMethod $classMethod): void
    {
        $this->traverseNodesWithCallable($classMethod, function (Node $node): ?Identifier {
            if (! $node instanceof Identifier) {
                return null;
            }

            if (! $this->isName($node, 'uuid')) {
                return null;
            }

            return new Identifier('id');
        });
    }
}
