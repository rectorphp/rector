<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\NodeFactory;

use Nette\Utils\Strings;
use PhpParser\Builder\Class_ as ClassBuilder;
use PhpParser\Builder\Method;
use PhpParser\Builder\Namespace_ as NamespaceBuilder;
use PhpParser\Builder\Property as PropertyBuilder;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\NetteKdyby\Naming\ParamNaming;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class CustomEventFactory
{
    /**
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @var ParamNaming
     */
    private $paramNaming;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    public function __construct(
        ClassNaming $classNaming,
        ParamNaming $paramNaming,
        NodeTypeResolver $nodeTypeResolver,
        StaticTypeMapper $staticTypeMapper
    ) {
        $this->classNaming = $classNaming;
        $this->paramNaming = $paramNaming;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->staticTypeMapper = $staticTypeMapper;
    }

    /**
     * @param Arg[] $args
     */
    public function create(string $className, array $args): Namespace_
    {
        $classBuilder = $this->createEventClassBuilder($className);

        $this->decorateWithConstructorIfHasArgs($classBuilder, $args);

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
            $paramName = $this->paramNaming->resolveParamNameFromArg($arg);

            $param = new Param(new Variable($paramName));

            $argStaticType = $this->nodeTypeResolver->getStaticType($arg->value);
            $phpParserTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($argStaticType);
            if ($phpParserTypeNode !== null) {
                $param->type = $phpParserTypeNode;
            }

            $methodBuilder->addParam($param);

            $assign = new Assign(new PropertyFetch(new Variable('this'), $paramName), new Variable($paramName));
            $methodBuilder->addStmt($assign);
        }

        return $methodBuilder->getNode();
    }

    private function createProperty(Arg $arg): Property
    {
        $paramName = $this->paramNaming->resolveParamNameFromArg($arg);

        $propertyBuilder = new PropertyBuilder($paramName);
        $propertyBuilder->makePrivate();

        return $propertyBuilder->getNode();
    }

    private function createGetterClassMethod(Arg $arg): ClassMethod
    {
        $paramName = $this->paramNaming->resolveParamNameFromArg($arg);

        $methodBuilder = new Method($paramName);

        $return = new Return_(new PropertyFetch(new Variable('this'), $paramName));
        $methodBuilder->addStmt($return);
        $methodBuilder->makePublic();

        return $methodBuilder->getNode();
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

    /**
     * @param Arg[] $args
     */
    private function decorateWithConstructorIfHasArgs(ClassBuilder $classBuilder, array $args): void
    {
        if (count($args) === 0) {
            return;
        }

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
}
