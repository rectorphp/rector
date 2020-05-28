<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\NodeFactory;

use Nette\Utils\Strings;
use PhpParser\Builder\Class_ as ClassBuilder;
use PhpParser\Builder\Method;
use PhpParser\Builder\Namespace_ as NamespaceBuilder;
use PhpParser\Builder\Property as PropertyBuilder;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast\String_ as StringCast;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\Exception\NotImplementedException;
use Rector\NodeNameResolver\NodeNameResolver;

final class CustomEventFactory
{
    /**
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(ClassNaming $classNaming, NodeNameResolver $nodeNameResolver)
    {
        $this->classNaming = $classNaming;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @param Arg[] $args
     */
    public function create(string $className, array $args): Namespace_
    {
        $classBuilder = $this->createEventClassBuilder($className);

        // 1. add __construct if args?
        // 2. add getters
        // 3. add property

        if (count($args) > 0) {
            $methodBuilder = $this->createConstructClassMethod($args);
            $classBuilder->addStmt($methodBuilder);

            // add properties
            foreach ($args as $arg) {
                $property = $this->createProperty($arg);
                $classBuilder->addStmt($property);
            }

            // add getters
            foreach ($args as $arg) {
                $getterClassMethod = $this->createGetterClassMethod($arg);
                $classBuilder->addStmt($getterClassMethod);
            }
        }

        $class = $classBuilder->getNode();

        return $this->wrapClassToNamespace($className, $class);
    }

    /**
     * @param Arg[] $args
     */
    private function createConstructClassMethod(array $args): ClassMethod
    {
        $methodBuilder = new Method('__construct');
        $methodBuilder->makePublic();

        foreach ($args as $arg) {
            $paramName = $this->resolveParamNameFromArg($arg);

            $param = new Param(new Variable($paramName));
            $methodBuilder->addParam($param);

            $assign = new Assign(new PropertyFetch(new Variable('this'), $paramName), new Variable($paramName));
            $methodBuilder->addStmt($assign);
        }

        return $methodBuilder->getNode();
    }

    private function createProperty(Arg $arg): Property
    {
        $paramName = $this->resolveParamNameFromArg($arg);

        $propertyBuilder = new PropertyBuilder($paramName);
        $propertyBuilder->makePrivate();

        return $propertyBuilder->getNode();
    }

    private function createGetterClassMethod(Arg $arg): ClassMethod
    {
        $paramName = $this->resolveParamNameFromArg($arg);

        $methodBuilder = new Method($paramName);

        $return = new Return_(new PropertyFetch(new Variable('this'), $paramName));
        $methodBuilder->addStmt($return);
        $methodBuilder->makePublic();

        return $methodBuilder->getNode();
    }

    private function resolveParamNameFromArg(Arg $arg): string
    {
        $value = $arg->value;
        if ($value instanceof StringCast) {
            $value = $value->expr;
        }

        if ($value instanceof Ternary) {
            $value = $value->if;
        }

        while ($value instanceof ArrayDimFetch) {
            $value = $value->var;
        }

        if ($value instanceof PropertyFetch) {
            return $this->resolveParamNameFromPropertyFetch($value);
        }

        if ($value instanceof MethodCall) {
            return $this->resolveParamNameFromMethodCall($value);
        }

        if ($value === null) {
            throw new NotImplementedException();
        }

        $paramName = $this->nodeNameResolver->getName($value);
        if ($paramName !== null) {
            return $paramName;
        }

        if ($value instanceof String_) {
            return $value->value;
        }

        throw new NotImplementedException();
    }

    private function resolveParamNameFromPropertyFetch(PropertyFetch $propertyFetch): string
    {
        $varName = $this->nodeNameResolver->getName($propertyFetch->var);
        if (! is_string($varName)) {
            throw new NotImplementedException();
        }

        $propertyName = $this->nodeNameResolver->getName($propertyFetch->name);
        if (! is_string($propertyName)) {
            throw new NotImplementedException();
        }

        return $varName . ucfirst($propertyName);
    }

    private function resolveParamNameFromMethodCall(MethodCall $methodCall): string
    {
        $varName = $this->nodeNameResolver->getName($methodCall->var);
        if (! is_string($varName)) {
            throw new NotImplementedException();
        }

        $methodName = $this->nodeNameResolver->getName($methodCall->name);
        if (! is_string($methodName)) {
            throw new NotImplementedException();
        }

        return $varName . ucfirst($methodName);
    }

    private function createEventClassBuilder(string $className): ClassBuilder
    {
        $shortClassName = $this->classNaming->getShortName($className);

        $classBuilder = new ClassBuilder($shortClassName);
        $classBuilder->makeFinal();
        $classBuilder->extend(new FullyQualified('Symfony\Contracts\EventDispatcher\Event'));

        return $classBuilder;
    }

    private function wrapClassToNamespace(string $className, Class_ $class): Namespace_
    {
        $namespace = Strings::before($className, '\\', -1);
        $namespaceBuilder = new NamespaceBuilder($namespace);
        $namespaceBuilder->addStmt($class);

        return $namespaceBuilder->getNode();
    }
}
