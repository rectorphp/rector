<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\NodeManipulator;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\CakePHPToSymfony\Contract\NodeManipulator\RepositoryFindMethodCallManipulatorInterface;
use Rector\Exception\NotImplementedException;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;

final class DoctrineRepositoryClassMethodManipulator
{
    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var RepositoryFindMethodCallManipulatorInterface[]
     */
    private $repositoryFindMethodCallManipulators = [];

    /**
     * @param RepositoryFindMethodCallManipulatorInterface[] $repositoryFindMethodCallManipulators
     */
    public function __construct(
        CallableNodeTraverser $callableNodeTraverser,
        NameResolver $nameResolver,
        ValueResolver $valueResolver,
        array $repositoryFindMethodCallManipulators
    ) {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nameResolver = $nameResolver;
        $this->valueResolver = $valueResolver;
        $this->repositoryFindMethodCallManipulators = $repositoryFindMethodCallManipulators;
    }

    public function createFromCakePHPClassMethod(ClassMethod $classMethod, string $entityClass): ClassMethod
    {
        $this->callableNodeTraverser->traverseNodesWithCallable(
            (array) $classMethod->getStmts(),
            function (Node $node) use ($entityClass) {
                if (! $node instanceof MethodCall) {
                    return null;
                }

                if (! $this->nameResolver->isName($node->name, 'find')) {
                    return null;
                }

                return $this->refactorClassMethodByKind($node, $entityClass);
            }
        );

        return $classMethod;
    }

    private function refactorClassMethodByKind(MethodCall $methodCall, string $entityClass): Node
    {
        $findKind = $this->valueResolver->getValue($methodCall->args[0]->value);

        foreach ($this->repositoryFindMethodCallManipulators as $repositoryFindMethodCallManipulator) {
            if ($findKind !== $repositoryFindMethodCallManipulator->getKeyName()) {
                continue;
            }

            return $repositoryFindMethodCallManipulator->processMethodCall($methodCall);
        }

        if ($findKind === 'first') {
            $this->refactorFindFirst($methodCall, $entityClass);
            return $methodCall;
        }

        if ($findKind === 'threaded') {
            return $this->refactorFindThreaded($methodCall, $entityClass);
        }

        if ($findKind === 'list') {
            return $this->refactorFindList($entityClass);
        }

        throw new NotImplementedException($findKind);
    }

    private function getItemByKey(Array_ $array, string $key): ?ArrayItem
    {
        foreach ($array->items as $arrayItem) {
            if ($arrayItem->key === null) {
                continue;
            }

            if (! $this->valueResolver->isValue($arrayItem->key, $key)) {
                continue;
            }

            return $arrayItem;
        }

        return null;
    }

    private function clearStringFromEntityPrefix(String_ $string, string $entityClass): void
    {
        $string->value = Strings::replace($string->value, '#^' . $entityClass . '\.#');
    }

    /**
     * @return Arg[]
     */
    private function createFindOneByArgs(string $entityClass, MethodCall $methodCall): array
    {
        $args = [];

        $conditionsArray = $this->findConfigurationByKey($methodCall, 'conditions', $entityClass);
        if ($conditionsArray !== null) {
            $args[] = new Arg($conditionsArray);
        }

        $orderArray = $this->findConfigurationByKey($methodCall, 'order', $entityClass);
        if ($orderArray !== null) {
            if (count($args) === 0) {
                $args[] = new Arg(new ConstFetch(new Name('null')));
            }

            assert(isset($orderArray->items[0]));
            $args[] = new Arg($orderArray->items[0]);
        }

        return $args;
    }

    private function removeEntityPrefixFromConditionKeys(Array_ $conditionArray, string $entityClass): void
    {
        // clear keys from current class
        foreach ($conditionArray->items as $conditionArrayItem) {
            if (! $conditionArrayItem->key instanceof String_) {
                continue;
            }

            $this->clearStringFromEntityPrefix($conditionArrayItem->key, $entityClass);
        }
    }

    private function refactorFindFirst(MethodCall $methodCall, string $entityClass): void
    {
        $this->refactorToRepositoryMethod($methodCall, 'findOneBy');

        unset($methodCall->args[0]);
        if (! isset($methodCall->args[1])) {
            return;
        }

        $firstArgument = $methodCall->args[1]->value;
        if (! $firstArgument instanceof Array_) {
            return;
        }

        $methodCall->args = $this->createFindOneByArgs($entityClass, $methodCall);
    }

    private function refactorToRepositoryMethod(MethodCall $methodCall, string $methodName): void
    {
        $methodCall->var = new PropertyFetch(new Variable('this'), 'repository');
        $methodCall->name = new Identifier($methodName);
    }

    private function findConfigurationByKey(
        MethodCall $methodCall,
        string $configurationKey,
        string $entityClass
    ): ?Array_ {
        if (! isset($methodCall->args[1])) {
            return null;
        }

        $possibleArray = $methodCall->args[1]->value;
        if (! $possibleArray instanceof Array_) {
            return null;
        }

        $conditionsArrayItem = $this->getItemByKey($possibleArray, $configurationKey);
        if ($conditionsArrayItem === null) {
            return null;
        }

        $conditionsArrayItemArray = $conditionsArrayItem->value;
        if (! $conditionsArrayItemArray instanceof Array_) {
            throw new ShouldNotHappenException();
        }

        $this->removeEntityPrefixFromConditionKeys($conditionsArrayItemArray, $entityClass);

        return $conditionsArrayItemArray;
    }

    private function refactorFindThreaded(MethodCall $methodCall, string $entityClass): MethodCall
    {
        $this->refactorToRepositoryMethod($methodCall, 'findBy');

        unset($methodCall->args[0]);

        $conditionsArray = $this->findConfigurationByKey($methodCall, 'conditions', $entityClass);
        if ($conditionsArray === null) {
            return $methodCall;
        }

        $methodCall->args = [new Arg($conditionsArray)];

        return $methodCall;
    }

    private function refactorFindList(string $entityClass): MethodCall
    {
        // @see https://stackoverflow.com/a/42913902/1348344
        $thisRepositoryPropertyFetch = new PropertyFetch(new Variable('this'), new Identifier('repository'));

        $entityAliasLetter = strtolower($entityClass[0]);

        $createQueryBuilderMethodCall = new MethodCall($thisRepositoryPropertyFetch, new Identifier(
            'createQueryBuilder'
        ), [new Arg(new String_($entityAliasLetter))]);

        $getQueryMethodCall = new MethodCall($createQueryBuilderMethodCall, new Identifier('getQuery'));

        $getResultMethodCall = new MethodCall($getQueryMethodCall, new Identifier('getResult'));
        $hydrateArrayClassConstFetch = new ClassConstFetch(new FullyQualified('Doctrine\ORM\Query'), new Identifier(
            'HYDRATE_ARRAY'
        ));
        $getResultMethodCall->args[] = new Arg($hydrateArrayClassConstFetch);

        return $getResultMethodCall;
    }
}
