<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\NodeManipulator\RepositoryFindClassMethodManipulator;

use Nette\Utils\Strings;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\Value\ValueResolver;

abstract class AbstractRepositoryFindMethodCallManipulator
{
    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @required
     */
    public function autowireAbstractRepositoryFindMethodCallManipulator(ValueResolver $valueResolver): void
    {
        $this->valueResolver = $valueResolver;
    }

    protected function refactorToRepositoryMethod(MethodCall $methodCall, string $methodName): void
    {
        $methodCall->var = new PropertyFetch(new Variable('this'), 'repository');
        $methodCall->name = new Identifier($methodName);
    }

    protected function findConfigurationByKey(
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

    private function clearStringFromEntityPrefix(String_ $string, string $entityClass): void
    {
        $string->value = Strings::replace($string->value, '#^' . $entityClass . '\.#');
    }
}
