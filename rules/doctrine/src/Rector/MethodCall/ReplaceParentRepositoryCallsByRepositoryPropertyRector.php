<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DoctrineCodeQuality\Tests\Rector\DoctrineRepositoryAsService\DoctrineRepositoryAsServiceTest
 */
final class ReplaceParentRepositoryCallsByRepositoryPropertyRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const ENTITY_REPOSITORY_PUBLIC_METHODS = [
        'createQueryBuilder',
        'createResultSetMappingBuilder',
        'clear',
        'find',
        'findBy',
        'findAll',
        'findOneBy',
        'count',
        'getClassName',
        'matching',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Handles method calls in child of Doctrine EntityRepository and moves them to $this->repository property.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Doctrine\ORM\EntityRepository;

class SomeRepository extends EntityRepository
{
    public function someMethod()
    {
        return $this->findAll();
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Doctrine\ORM\EntityRepository;

class SomeRepository extends EntityRepository
{
    public function someMethod()
    {
        return $this->repository->findAll();
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        if (! $this->isObjectType($node->var, 'Doctrine\ORM\EntityRepository')) {
            return null;
        }

        if (! $this->isNames($node->name, self::ENTITY_REPOSITORY_PUBLIC_METHODS)) {
            return null;
        }

        $node->var = $this->nodeFactory->createPropertyFetch('this', 'repository');

        return $node;
    }

    private function shouldSkip(MethodCall $methodCall): bool
    {
        $classLike = $methodCall->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return true;
        }

        return ! $this->isObjectType($classLike, 'Doctrine\ORM\EntityRepository');
    }
}
