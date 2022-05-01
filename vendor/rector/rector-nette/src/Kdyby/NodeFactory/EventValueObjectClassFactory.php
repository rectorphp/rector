<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\NodeFactory;

use RectorPrefix20220501\Nette\Utils\Strings;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\MethodName;
use Rector\Nette\Kdyby\BlueprintFactory\VariableWithTypesFactory;
use Rector\Nette\Kdyby\ValueObject\VariableWithType;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\ClassBuilder;
use RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\NamespaceBuilder;
/**
 * @todo decouple to generic object factory for better re-use, e.g. this is just value object pattern
 */
final class EventValueObjectClassFactory
{
    /**
     * @readonly
     * @var \Rector\CodingStyle\Naming\ClassNaming
     */
    private $classNaming;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Nette\Kdyby\BlueprintFactory\VariableWithTypesFactory
     */
    private $variableWithTypesFactory;
    public function __construct(\Rector\CodingStyle\Naming\ClassNaming $classNaming, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Nette\Kdyby\BlueprintFactory\VariableWithTypesFactory $variableWithTypesFactory)
    {
        $this->classNaming = $classNaming;
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->variableWithTypesFactory = $variableWithTypesFactory;
    }
    /**
     * @param Arg[] $args
     */
    public function create(string $className, array $args) : \PhpParser\Node\Stmt\Namespace_
    {
        $classBuilder = $this->createEventClassBuilder($className);
        $this->decorateWithConstructorIfHasArgs($classBuilder, $args);
        $class = $classBuilder->getNode();
        // traverse with node name resolver, to to comply with PHPStan default parser
        $nameResolver = new \PhpParser\NodeVisitor\NameResolver(null, ['replaceNodes' => \false, 'preserveOriginalNames' => \true]);
        $nodeTraverser = new \PhpParser\NodeTraverser();
        $nodeTraverser->addVisitor($nameResolver);
        $nodeTraverser->traverse([$class]);
        return $this->wrapClassToNamespace($className, $class);
    }
    private function createEventClassBuilder(string $className) : \RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\ClassBuilder
    {
        $shortClassName = $this->classNaming->getShortName($className);
        $classBuilder = new \RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\ClassBuilder($shortClassName);
        $classBuilder->makeFinal();
        $classBuilder->extend(new \PhpParser\Node\Name\FullyQualified('Symfony\\Contracts\\EventDispatcher\\Event'));
        return $classBuilder;
    }
    /**
     * @param Arg[] $args
     */
    private function decorateWithConstructorIfHasArgs(\RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\ClassBuilder $classBuilder, array $args) : void
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
            $property = $this->nodeFactory->createPrivatePropertyFromNameAndType($variableWithType->getName(), $variableWithType->getType());
            $classBuilder->addStmt($property);
        }
        // add getters
        foreach ($variablesWithTypes as $variableWithType) {
            $getterClassMethod = $this->nodeFactory->createGetterClassMethod($variableWithType->getName(), $variableWithType->getType());
            $classBuilder->addStmt($getterClassMethod);
        }
    }
    private function wrapClassToNamespace(string $className, \PhpParser\Node\Stmt\Class_ $class) : \PhpParser\Node\Stmt\Namespace_
    {
        $namespace = \RectorPrefix20220501\Nette\Utils\Strings::before($className, '\\', -1);
        $namespaceBuilder = new \RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\NamespaceBuilder($namespace);
        $namespaceBuilder->addStmt($class);
        return $namespaceBuilder->getNode();
    }
    /**
     * @param VariableWithType[] $variablesWithTypes
     */
    private function ensureVariablesAreUnique(array $variablesWithTypes, \RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\ClassBuilder $classBuilder) : void
    {
        $usedVariableNames = [];
        foreach ($variablesWithTypes as $variablesWithType) {
            if (\in_array($variablesWithType->getName(), $usedVariableNames, \true)) {
                $className = $this->nodeNameResolver->getName($classBuilder->getNode());
                $message = \sprintf('Variable "$%s" is duplicated in to be created "%s" class', $variablesWithType->getName(), $className);
                throw new \Rector\Core\Exception\ShouldNotHappenException($message);
            }
            $usedVariableNames[] = $variablesWithType->getName();
        }
    }
    /**
     * @param VariableWithType[] $variableWithTypes
     */
    private function createConstructClassMethod(array $variableWithTypes) : \PhpParser\Node\Stmt\ClassMethod
    {
        $methodBuilder = new \RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        $methodBuilder->makePublic();
        foreach ($variableWithTypes as $variableWithType) {
            $param = new \PhpParser\Node\Param(new \PhpParser\Node\Expr\Variable($variableWithType->getName()));
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
