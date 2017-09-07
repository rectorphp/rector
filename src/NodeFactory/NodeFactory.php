<?php declare(strict_types=1);

namespace Rector\NodeFactory;

use Nette\NotImplementedException;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
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

    /**
     * Creates "null"
     */
    public function createNullConstant(): ConstFetch
    {
        return new ConstFetch(new Name('null'));
    }

    /**
     * Creates "false"
     */
    public function createFalseConstant(): ConstFetch
    {
        return new ConstFetch(new Name('false'));
    }

    /**
     * Creates "SomeClass::CONSTANT"
     */
    public function createClassConstant(string $className, string $constantName): ClassConstFetch
    {
        $classNameNode = new FullyQualified($className);

        return new ClassConstFetch($classNameNode, $constantName);
    }

    /**
     * Creates "SomeClass::class"
     */
    public function createClassConstantReference(string $className): ClassConstFetch
    {
        $nameNode = new FullyQualified($className);

        return new ClassConstFetch($nameNode, 'class');
    }

    /**
     * Creates "$method->call();"
     */
    public function createMethodCall(string $variableName, string $methodName): MethodCall
    {
        $varNode = new Variable($variableName);

        return new MethodCall($varNode, $methodName);
    }

    /**
     * Creates "use \SomeTrait;"
     */
    public function createTraitUse(string $traitName): TraitUse
    {
        $traitNameNode = new FullyQualified($traitName);

        return new TraitUse([$traitNameNode]);
    }

    /**
     * Creates "['item', $variable]"
     *
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
            } else {
                throw new NotImplementedException(sprintf(
                    'Not implemented yet. Go to "%s::%s()" and add check for "%s" node.',
                    __CLASS__,
                    __METHOD__,
                    get_class($item)
                ));
            }
        }

        return new Array_($arrayItems, [
            'kind' => Array_::KIND_SHORT,
        ]);
    }

    /**
     * Creates "($args)"
     *
     * @param mixed[] $arguments
     * @return Arg[]
     */
    public function createArgs(array $arguments): array
    {
        $args = [];
        foreach ($arguments as $argument) {
            $args[] = new Arg($argument);
        }

        return $args;
    }

    /**
     * Creates $this->property = $property;
     */
    public function createPropertyAssignment(string $propertyName): Expression
    {
        $variable = new Variable($propertyName, [
            'name' => $propertyName,
        ]);

        $assign = new Assign(
            $this->createLocalPropertyFetch($propertyName),
            $variable
        );

        return new Expression($assign);
    }
}
