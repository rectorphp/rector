<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\NodeManipulator\RepositoryFindClassMethodManipulator;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\CakePHPToSymfony\Contract\NodeManipulator\RepositoryFindMethodCallManipulatorInterface;

final class FindListRepositoryFindMethodCallManipulator extends AbstractRepositoryFindMethodCallManipulator implements RepositoryFindMethodCallManipulatorInterface
{
    public function getKeyName(): string
    {
        return 'list';
    }

    public function processMethodCall(MethodCall $methodCall, string $entityClass): MethodCall
    {
        // @see https://stackoverflow.com/a/42913902/1348344
        $thisRepositoryPropertyFetch = new PropertyFetch(new Variable('this'), new Identifier('repository'));

        $entityAliasLetter = strtolower($entityClass[0]);

        $createQueryBuilderMethodCall = new MethodCall($thisRepositoryPropertyFetch, new Identifier(
            'createQueryBuilder'
        ), [new Arg(new String_($entityAliasLetter))]);

        $getQueryMethodCall = new MethodCall($createQueryBuilderMethodCall, new Identifier('getQuery'));

        $getResultMethodCall = new MethodCall($getQueryMethodCall, new Identifier('getResult'));
        $hydrateArrayClassConstFetch = new ClassConstFetch(new FullyQualified(
            'Doctrine\ORM\Query'
        ), new Identifier('HYDRATE_ARRAY'));
        $getResultMethodCall->args[] = new Arg($hydrateArrayClassConstFetch);

        return $getResultMethodCall;
    }
}
