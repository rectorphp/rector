<?php

declare(strict_types=1);

namespace Rector\Doctrine\NodeAnalyzer;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Exception\Bridge\RectorProviderException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Doctrine\Contract\Mapper\DoctrineEntityAndRepositoryMapperInterface;
use Rector\NodeNameResolver\NodeNameResolver;

final class EntityClassOrAliasResolver
{
    /**
     * @var DoctrineEntityAndRepositoryMapperInterface
     */
    private $doctrineEntityAndRepositoryMapper;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        DoctrineEntityAndRepositoryMapperInterface $doctrineEntityAndRepositoryMapper,
        NodeNameResolver $nodeNameResolver
    ) {
        $this->doctrineEntityAndRepositoryMapper = $doctrineEntityAndRepositoryMapper;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function resolve(MethodCall $methodCall): string
    {
        $entityFqnOrAlias = $this->entityFqnOrAlias($methodCall);

        if ($entityFqnOrAlias !== null) {
            $repositoryClassName = $this->doctrineEntityAndRepositoryMapper->mapEntityToRepository($entityFqnOrAlias);
            if ($repositoryClassName !== null) {
                return $repositoryClassName;
            }
        }

        throw new RectorProviderException(sprintf(
            'A repository was not provided for "%s" entity by your "%s" class.',
            $entityFqnOrAlias,
            get_class($this->doctrineEntityAndRepositoryMapper)
        ));
    }

    private function entityFqnOrAlias(MethodCall $methodCall): string
    {
        $repositoryArgument = $methodCall->args[0]->value;

        if ($repositoryArgument instanceof String_) {
            return $repositoryArgument->value;
        }

        if ($repositoryArgument instanceof ClassConstFetch && $repositoryArgument->class instanceof Name) {
            return $this->nodeNameResolver->getName($repositoryArgument->class);
        }

        throw new ShouldNotHappenException('Unable to resolve repository argument');
    }
}
