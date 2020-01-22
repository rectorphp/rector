<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector\NodeFactory;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Exception\NotImplementedException;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;

final class DoctrineRepositoryClassMethodFactory
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

    public function __construct(
        CallableNodeTraverser $callableNodeTraverser,
        NameResolver $nameResolver,
        ValueResolver $valueResolver
    ) {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nameResolver = $nameResolver;
        $this->valueResolver = $valueResolver;
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

                $this->createMethodByKind($node, $entityClass);
            }
        );

        return $classMethod;
    }

    private function createMethodByKind(MethodCall $methodCall, string $entityClass): void
    {
        $findKind = $this->valueResolver->getValue($methodCall->args[0]->value);
        if ($findKind === 'all') {
            $methodCall->var = new PropertyFetch(new Variable('this'), 'repository');
            $methodCall->name = new Identifier('findAll');
            $methodCall->args = [];
        } elseif ($findKind === 'first') {
            $methodCall->name = new Identifier('findOneBy');
            unset($methodCall->args[0]);

            if (! isset($methodCall->args[1])) {
                return;
            }

            $firstArgument = $methodCall->args[1]->value;
            assert($firstArgument instanceof Array_);

            $methodCall->args = $this->createFindOneByArgs($entityClass, $firstArgument);
        } else {
            throw new NotImplementedException();
        }
    }

    private function getItemByKey(Array_ $array, string $key): ?ArrayItem
    {
        foreach ($array->items as $arrayItem) {
            if ($arrayItem->key === null) {
                continue;
            }

            if ($this->valueResolver->getValue($arrayItem->key) !== $key) {
                continue;
            }

            return $arrayItem;
        }

        return null;
    }

    private function clearStringFromEntityPrefix(String_ $string, string $entityClass): String_
    {
        $string->value = Strings::replace($string->value, '#^' . $entityClass . '\.#');

        return $string;
    }

    /**
     * @return Arg[]
     */
    private function createFindOneByArgs(string $entityClass, Array_ $firstArgument): array
    {
        $args = [];

        /** @var Array_ $firstArgument */
        $conditionsArrayItem = $this->getItemByKey($firstArgument, 'conditions');
        if ($conditionsArrayItem !== null && $conditionsArrayItem->value instanceof Array_) {
            $conditionArray = $conditionsArrayItem->value;
            $this->removeEntityPrefixFromConditionKeys($conditionArray, $entityClass);
            $args[] = new Arg($conditionArray);
        }

        $orderItem = $this->getItemByKey($firstArgument, 'order');
        if ($orderItem !== null) {
            if (count($args) === 0) {
                $args[] = new Arg(new ConstFetch(new Name('null')));
            }

            /** @var Array_ $orderArray */
            $orderArray = $orderItem->value;
            assert(isset($orderArray->items[0]));
            $args[] = new Arg($orderArray->items[0]->value);
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

            $conditionArrayItem->key = $this->clearStringFromEntityPrefix($conditionArrayItem->key, $entityClass);
        }
    }
}
