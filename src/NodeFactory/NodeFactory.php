<?php declare(strict_types=1);

namespace Rector\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
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
use PhpParser\Node\Stmt\TraitUse;

final class NodeFactory
{
    /**
     * Creates "$this->propertyName"
     */
    public function createLocalPropertyFetch(string $propertyName): PropertyFetch
    {
        $localVariable = new Variable('this', [
            'name' => $propertyName,
        ]);

        return new PropertyFetch($localVariable, $propertyName);
    }

    public function createNullConstant(): ConstFetch
    {
        return new ConstFetch(new Name('null'));
    }

    public function createFalseConstant(): ConstFetch
    {
        return new ConstFetch(new Name('false'));
    }

    public function createClassConstant(string $className, string $constantName): ClassConstFetch
    {
        $classNameNode = new FullyQualified($className);

        return new ClassConstFetch($classNameNode, $constantName);
    }

    public function createClassConstantReference(string $className): ClassConstFetch
    {
        $nameNode = new FullyQualified($className);

        return new ClassConstFetch($nameNode, 'class');
    }

    public function createMethodCall(string $variableName, string $methodName): MethodCall
    {
        $varNode = new Variable($variableName);

        return new MethodCall($varNode, $methodName);
    }

    public function createTraitUse(string $traitName): TraitUse
    {
        $traitNameNode = new FullyQualified($traitName);

        return new TraitUse([$traitNameNode]);
    }

    /**
     * @param mixed|Node[] ...$items
     */
    public function createArray(...$items): Array_
    {
        $arrayItems = [];

        foreach ($items as $item) {
            if ($item instanceof Variable) {
                $arrayItems[] = new ArrayItem($item);
            } elseif ($item instanceof Identifier) {
                $string = new String_((string) $item);
                $arrayItems[] = new ArrayItem($string);
            }
        }

        return new Array_($arrayItems, [
            'kind' => Array_::KIND_SHORT,
        ]);
    }

    /**
     * @param mixed[] $arguments
     * @return Arg[]
     */
    public function createArgs(...$arguments): array
    {
        $args = [];
        foreach ($arguments as $argument) {
            $args[] = new Arg($argument);
        }

        return $args;
    }
}
