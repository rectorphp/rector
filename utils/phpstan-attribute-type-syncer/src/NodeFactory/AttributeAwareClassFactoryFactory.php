<?php

declare(strict_types=1);

namespace Rector\Utils\PHPStanAttributeTypeSyncer\NodeFactory;

use Nette\Utils\Strings;
use PhpParser\Builder\Param;
use PhpParser\BuilderFactory;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\Node;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeNodeAwareFactoryInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\Utils\PHPStanAttributeTypeSyncer\ClassNaming\AttributeClassNaming;
use Rector\Utils\PHPStanAttributeTypeSyncer\ValueObject\Paths;
use ReflectionClass;

final class AttributeAwareClassFactoryFactory
{
    /**
     * @var string
     */
    private const NODE = 'node';

    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @var AttributeClassNaming
     */
    private $attributeClassNaming;

    public function __construct(BuilderFactory $builderFactory, AttributeClassNaming $attributeClassNaming)
    {
        $this->builderFactory = $builderFactory;
        $this->attributeClassNaming = $attributeClassNaming;
    }

    public function createFromPhpDocParserNodeClass(string $nodeClass): Namespace_
    {
        if (Strings::contains($nodeClass, '\\Type\\')) {
            $namespace = Paths::NAMESPACE_TYPE_NODE_FACTORY;
        } else {
            $namespace = Paths::NAMESPACE_PHPDOC_NODE_FACTORY;
        }

        $namespaceBuilder = $this->builderFactory->namespace($namespace);

        $shortClassName = $this->attributeClassNaming->createAttributeAwareFactoryShortClassName($nodeClass);

        $classBuilder = $this->builderFactory->class($shortClassName);
        $classBuilder->makeFinal();
        $classBuilder->implement(new FullyQualified(AttributeNodeAwareFactoryInterface::class));

        $classMethods = $this->createClassMethods($nodeClass);
        $classBuilder->addStmts($classMethods);

        $namespaceBuilder->addStmt($classBuilder->getNode());

        return $namespaceBuilder->getNode();
    }

    /**
     * @return ClassMethod[]
     */
    private function createClassMethods(string $nodeClass): array
    {
        $classMethods = [];

        $classMethods[] = $this->createGetOriginalNodeClass($nodeClass);

        $nodeParam = $this->builderFactory->param(self::NODE);
        $nodeParam->setType(new FullyQualified(Node::class));

        $classMethods[] = $this->createIsMatchClassMethod($nodeClass, $nodeParam);

        $classMethods[] = $this->createCreateClassMethod($nodeClass, $nodeParam);

        return $classMethods;
    }

    private function createGetOriginalNodeClass(string $nodeClass): ClassMethod
    {
        $getOriginalNodeClassClassMethod = $this->builderFactory->method('getOriginalNodeClass');
        $getOriginalNodeClassClassMethod->makePublic();
        $getOriginalNodeClassClassMethod->setReturnType('string');

        $classReference = $this->createClassReference($nodeClass);
        $getOriginalNodeClassClassMethod->addStmt(new Return_($classReference));

        return $getOriginalNodeClassClassMethod->getNode();
    }

    private function createIsMatchClassMethod(string $nodeClass, Param $param): ClassMethod
    {
        $isMatchClassMethod = $this->builderFactory->method('isMatch');

        $isMatchClassMethod->addParam($param);
        $isMatchClassMethod->makePublic();
        $isMatchClassMethod->setReturnType('bool');

        $isAFuncCall = $this->createIsAFuncCall($nodeClass);
        $isMatchClassMethod->addStmt(new Return_($isAFuncCall));

        return $isMatchClassMethod->getNode();
    }

    private function createCreateClassMethod(string $nodeClass, Param $nodeParam): ClassMethod
    {
        $createClassMethod = $this->builderFactory->method('create');

        $createClassMethod->addParam($nodeParam);
        $createClassMethod->makePublic();
        $createClassMethod->setReturnType(new FullyQualified(AttributeAwareNodeInterface::class));

        // add @param doc with more precise type
        $paramDocBlock = sprintf('/**%s * @param \\%s%s */', PHP_EOL, $nodeClass, PHP_EOL);
        $createClassMethod->setDocComment($paramDocBlock);

        $attributeAwareClassName = $this->attributeClassNaming->createAttributeAwareClassName($nodeClass);

        $new = new New_(new FullyQualified($attributeAwareClassName));

        // complete new args
        $this->completeNewArgs($new, $nodeClass);

        $createClassMethod->addStmt(new Return_($new));

        return $createClassMethod->getNode();
    }

    private function createClassReference(string $nodeClass): ClassConstFetch
    {
        return new ClassConstFetch(new FullyQualified($nodeClass), 'class');
    }

    private function createIsAFuncCall(string $nodeClass): FuncCall
    {
        return new FuncCall(new Name('is_a'), [
            new Variable(self::NODE),
            $this->createClassReference($nodeClass),
            new ConstFetch(new Name('true')),
        ]);
    }

    private function completeNewArgs(New_ $new, string $phpDocParserNodeClass): void
    {
        // ...
        $reflectionClass = new ReflectionClass($phpDocParserNodeClass);
        $constructorReflectionMethod = $reflectionClass->getConstructor();

        // no constructor → no params to add
        if ($constructorReflectionMethod === null) {
            return;
        }

        $phpDocParserNodeVariable = new Variable(self::NODE);

        foreach ($constructorReflectionMethod->getParameters() as $parameter) {
            $parameterName = $parameter->getName();

            $new->args[] = new Arg(new PropertyFetch($phpDocParserNodeVariable, $parameterName));
        }
    }
}
