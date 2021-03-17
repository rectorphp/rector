<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\NodeFactory;

use Nette\Utils\Strings;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\MethodName;
use Rector\NetteKdyby\BlueprintFactory\VariableWithTypesFactory;
use Rector\NetteKdyby\ValueObject\VariableWithType;
use Rector\NodeNameResolver\NodeNameResolver;
use Symplify\Astral\ValueObject\NodeBuilder\ClassBuilder;
use Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use Symplify\Astral\ValueObject\NodeBuilder\NamespaceBuilder;

/**
 * @todo decouple to generic object factory for better re-use, e.g. this is just value object pattern
 */
final class EventValueObjectClassFactory
{
    /**
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @var VariableWithTypesFactory
     */
    private $variableWithTypesFactory;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        ClassNaming $classNaming,
        NodeFactory $nodeFactory,
        NodeNameResolver $nodeNameResolver,
        VariableWithTypesFactory $variableWithTypesFactory
    ) {
        $this->classNaming = $classNaming;
        $this->variableWithTypesFactory = $variableWithTypesFactory;
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
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

    private function createEventClassBuilder(string $className): ClassBuilder
    {
        $shortClassName = $this->classNaming->getShortName($className);

        $classBuilder = new ClassBuilder($shortClassName);
        $classBuilder->makeFinal();
        $classBuilder->extend(new FullyQualified('Symfony\Contracts\EventDispatcher\Event'));

        return $classBuilder;
    }

    /**
     * @param Arg[] $args
     */
    private function decorateWithConstructorIfHasArgs(ClassBuilder $classBuilder, array $args): void
    {
        if ($args === []) {
            return;
        }

        $variablesWithTypes = $this->variableWithTypesFactory->createVariablesWithTypesFromArgs($args);

        $this->ensureVariablesAreUnique($variablesWithTypes, $classBuilder);

        $classMethod = $this->createConstructClassMethod($variablesWithTypes);
        $classBuilder->addStmt($classMethod);

        // add properties
        foreach ($variablesWithTypes as $variableWithType) {
            $property = $this->nodeFactory->createPrivatePropertyFromNameAndType(
                $variableWithType->getName(),
                $variableWithType->getType()
            );

            $classBuilder->addStmt($property);
        }

        // add getters
        foreach ($variablesWithTypes as $variableWithType) {
            $getterClassMethod = $this->nodeFactory->createGetterClassMethodFromNameAndType(
                $variableWithType->getName(),
                $variableWithType->getPhpParserTypeNode()
            );

            $classBuilder->addStmt($getterClassMethod);
        }
    }

    private function wrapClassToNamespace(string $className, Class_ $class): Namespace_
    {
        $namespace = Strings::before($className, '\\', -1);

        $namespaceBuilder = new NamespaceBuilder($namespace);
        $namespaceBuilder->addStmt($class);

        return $namespaceBuilder->getNode();
    }

    /**
     * @param VariableWithType[] $variablesWithTypes
     */
    private function ensureVariablesAreUnique(array $variablesWithTypes, ClassBuilder $classBuilder): void
    {
        $usedVariableNames = [];

        foreach ($variablesWithTypes as $variablesWithType) {
            if (in_array($variablesWithType->getName(), $usedVariableNames, true)) {
                $className = $this->nodeNameResolver->getName($classBuilder->getNode());

                $message = sprintf(
                    'Variable "$%s" is duplicated in to be created "%s" class',
                    $variablesWithType->getName(),
                    $className
                );

                throw new ShouldNotHappenException($message);
            }

            $usedVariableNames[] = $variablesWithType->getName();
        }
    }

    /**
     * @param VariableWithType[] $variableWithTypes
     */
    private function createConstructClassMethod(array $variableWithTypes): ClassMethod
    {
        $methodBuilder = new MethodBuilder(MethodName::CONSTRUCT);
        $methodBuilder->makePublic();

        foreach ($variableWithTypes as $variableWithType) {
            $param = new Param(new Variable($variableWithType->getName()));

            if ($variableWithType->getPhpParserTypeNode() !== null) {
                $param->type = $variableWithType->getPhpParserTypeNode();
            }

            $methodBuilder->addParam($param);

            $assign = $this->nodeFactory->createPropertyAssignment($variableWithType->getName());
            $methodBuilder->addStmt($assign);
        }

        return $methodBuilder->getNode();
    }
}
