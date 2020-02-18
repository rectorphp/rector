<?php

declare(strict_types=1);

namespace Rector\Architecture\Rector\MethodCall;

use Doctrine\ORM\EntityRepository;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Core\Tests\Rector\Architecture\DoctrineRepositoryAsService\DoctrineRepositoryAsServiceTest
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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Handles method calls in child of Doctrine EntityRepository and moves them to "$this->repository" property.',
            [
                new CodeSample(
                    <<<'PHP'
<?php

use Doctrine\ORM\EntityRepository;

class SomeRepository extends EntityRepository
{
    public function someMethod()
    {
        return $this->findAll();
    }
}
PHP
                    ,
                    <<<'PHP'
<?php

use Doctrine\ORM\EntityRepository;

class SomeRepository extends EntityRepository
{
    public function someMethod()
    {
        return $this->repository->findAll();
    }
}
PHP
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
        if (! $node->name instanceof Identifier) {
            return null;
        }

        if (! $this->isObjectType($node->var, EntityRepository::class)) {
            return null;
        }

        if (! $this->isNames($node->name, self::ENTITY_REPOSITORY_PUBLIC_METHODS)) {
            return null;
        }

        $node->var = $this->createPropertyFetch('this', 'repository');

        return $node;
    }
}
