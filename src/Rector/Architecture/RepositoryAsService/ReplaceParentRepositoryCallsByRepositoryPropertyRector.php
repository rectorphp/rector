<?php declare(strict_types=1);

namespace Rector\Rector\Architecture\RepositoryAsService;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\BetterReflection\Reflector\SmartClassReflector;
use Rector\Node\PropertyFetchNodeFactory;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ReplaceParentRepositoryCallsByRepositoryPropertyRector extends AbstractRector
{
    /**
     * @var SmartClassReflector
     */
    private $smartClassReflector;

    /**
     * @var PropertyFetchNodeFactory
     */
    private $propertyFetchNodeFactory;

    /**
     * @var string
     */
    private $entityRepositoryClass;

    public function __construct(
        SmartClassReflector $smartClassReflector,
        PropertyFetchNodeFactory $propertyFetchNodeFactory,
        string $entityRepositoryClass
    ) {
        $this->smartClassReflector = $smartClassReflector;
        $this->propertyFetchNodeFactory = $propertyFetchNodeFactory;
        $this->entityRepositoryClass = $entityRepositoryClass;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $node->name instanceof Identifier) {
            return false;
        }

        return in_array($node->name->toString(), $this->getEntityRepositoryPublicMethodNames(), true);
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $methodCallNode->var = $this->propertyFetchNodeFactory->createLocalWithPropertyName('repository');

        return $methodCallNode;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Handles method calls in child of Doctrine EntityRepository and moves them to "$this->repository" property.',
            [
                new CodeSample(
<<<'CODE_SAMPLE'
<?php

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
<?php

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
     * @todo should be part of reflection
     * @return string[]
     */
    private function getEntityRepositoryPublicMethodNames(): array
    {
        $entityRepositoryReflection = $this->smartClassReflector->reflect($this->entityRepositoryClass);

        if ($entityRepositoryReflection !== null) {
            return array_keys($entityRepositoryReflection->getImmediateMethods());
        }

        return [];
    }
}
