<?php declare(strict_types=1);

namespace Rector\NodeFactory;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;

final class NodeFactory
{
    /**
     * Creates "$this->propertyName"
     */
    public function createLocalPropertyFetch(string $propertyName): PropertyFetch
    {
        return new PropertyFetch(
            new Variable('this', [
                'name' => $propertyName,
            ]),
            $propertyName
        );
    }

    public function createNullConstant(): ConstFetch
    {
        return new ConstFetch(new Name('null'));
    }

    public function createClassConstantReference(string $className): ClassConstFetch
    {
        $nameNode = new Name('\\' . $className);

        return new ClassConstFetch($nameNode, 'class');
    }

    public function createMethodCall(string $variableName, string $methodName): MethodCall
    {
        $varNode = new Variable($variableName);

        return new MethodCall($varNode, $methodName);
    }
}
