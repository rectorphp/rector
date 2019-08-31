<?php declare(strict_types=1);

namespace Rector\Architecture\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php\TypeAnalyzer;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ConstructorInjectionToActionInjectionRector extends AbstractRector
{
    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @var Param[]
     */
    private $propertyFetchToParams = [];

    /**
     * @var Param[]
     */
    private $propertyFetchToParamsToRemoveFromConstructor = [];

    public function __construct(ClassManipulator $classManipulator, TypeAnalyzer $typeAnalyzer)
    {
        $this->classManipulator = $classManipulator;
        $this->typeAnalyzer = $typeAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeController
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function default()
    {
        $products = $this->productRepository->fetchAll();
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeController
{
    public function default(ProductRepository $productRepository)
    {
        $products = $productRepository->fetchAll();
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->reset();

        // only in controllers
        if (! $this->isName($node, '*Controller')) {
            return null;
        }

        if ($node->isAbstract()) {
            return null;
        }

        $constructMethod = $node->getMethod('__construct');
        // no constructor, nothing to do
        if ($constructMethod === null) {
            return null;
        }

        // traverse constructor dependencies and names of their properties
        $this->collectPropertyFetchToParams($constructMethod);

        // replace them in property fetches with particular class methods and use variable instead
        foreach ($node->stmts as $classStmt) {
            if (! $classStmt instanceof ClassMethod) {
                continue;
            }

            if ($this->isName($classStmt, '__construct')) {
                continue;
            }

            if (! $classStmt->isPublic()) {
                continue;
            }

            $this->replacePropertyFetchByInjectedVariables($classStmt);
        }

        // collect all property fetches that are relevant to original constructor properties
        $this->traverseNodesWithCallable($node->stmts, function (Node $node) {
            if (! $node instanceof PropertyFetch) {
                return null;
            }

            // only scan non-action methods
            /** @var ClassMethod $methdoNode */
            $methdoNode = $node->getAttribute(AttributeKey::METHOD_NODE);
            if ($methdoNode->isPublic()) {
                return null;
            }

            $usedPropertyFetchName = $this->getName($node);
            if (isset($this->propertyFetchToParams[$usedPropertyFetchName])) {
                unset($this->propertyFetchToParamsToRemoveFromConstructor[$usedPropertyFetchName]);
            }
        });

        $this->removeUnusedPropertiesAndConstructorParams($node, $constructMethod);

        return $node;
    }

    private function replacePropertyFetchByInjectedVariables(ClassMethod $classMethod): void
    {
        $currentlyAddedLocalVariables = [];

        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use (
            &$currentlyAddedLocalVariables
        ): ?Variable {
            if (! $node instanceof PropertyFetch) {
                return null;
            }

            foreach ($this->propertyFetchToParams as $propertyFetchName => $param) {
                if ($this->isName($node, $propertyFetchName)) {
                    $currentlyAddedLocalVariables[] = $param;

                    /** @var string $paramName */
                    $paramName = $this->getName($param);
                    return new Variable($paramName);
                }
            }

            return null;
        });

        foreach ($currentlyAddedLocalVariables as $param) {
            // is param already present?
            foreach ($classMethod->params as $existingParam) {
                if ($this->areNamesEqual($existingParam, $param)) {
                    continue 2;
                }
            }

            $classMethod->params[] = $param;
        }
    }

    private function collectPropertyFetchToParams(ClassMethod $classMethod): void
    {
        foreach ((array) $classMethod->stmts as $constructorStmt) {
            $propertyToVariable = $this->resolveAssignPropertyToVariableOrNull($constructorStmt);
            if ($propertyToVariable === null) {
                continue;
            }

            [$propertyFetchName, $variableName] = $propertyToVariable;

            $param = $this->classManipulator->findMethodParamByName($classMethod, $variableName);
            if ($param === null) {
                continue;
            }

            // random type, we cannot autowire in action
            if ($param->type === null) {
                continue;
            }

            $paramType = $this->getName($param->type);
            if ($paramType === null) {
                continue;
            }

            if ($this->typeAnalyzer->isPhpReservedType($paramType)) {
                continue;
            }

            // it's a match
            $this->propertyFetchToParams[$propertyFetchName] = $param;
        }

        $this->propertyFetchToParamsToRemoveFromConstructor = $this->propertyFetchToParams;
    }

    private function reset(): void
    {
        $this->propertyFetchToParams = [];
        $this->propertyFetchToParamsToRemoveFromConstructor = [];
    }

    /**
     * @param Node $node
     * @return string[]|null
     */
    private function resolveAssignPropertyToVariableOrNull(Node $node): ?array
    {
        if ($node instanceof Expression) {
            $node = $node->expr;
        }

        if (! $node instanceof Assign) {
            return null;
        }

        if (! $node->var instanceof PropertyFetch) {
            return null;
        }

        if (! $node->expr instanceof Variable) {
            return null;
        }

        $propertyFetchName = $this->getName($node->var);
        $variableName = $this->getName($node->expr);
        if ($propertyFetchName === null) {
            return null;
        }

        if ($variableName === null) {
            return null;
        }

        return [$propertyFetchName, $variableName];
    }

    private function removeUnusedPropertiesAndConstructorParams(Class_ $class, ClassMethod $classMethod): void
    {
        if ($this->propertyFetchToParamsToRemoveFromConstructor === []) {
            return;
        }

        $this->removeAssignsAndParamsFromConstructor($classMethod);
        $this->removeUnusedProperties($class);
        $this->removeEmptyConstruct($class, $classMethod);
    }

    private function removeAssignsAndParamsFromConstructor(ClassMethod $classMethod): void
    {
        foreach ($this->propertyFetchToParamsToRemoveFromConstructor as $propertyFetchToRemove => $paramToRemove) {
            // remove unused params in constructor
            foreach ($classMethod->params as $key => $constructorParam) {
                if (! $this->areNamesEqual($constructorParam, $paramToRemove)) {
                    continue;
                }

                unset($classMethod->params[$key]);
            }

            foreach ((array) $classMethod->stmts as $key => $constructorStmt) {
                $propertyFetchToVariable = $this->resolveAssignPropertyToVariableOrNull($constructorStmt);
                if ($propertyFetchToVariable === null) {
                    continue;
                }

                [$propertyFetchName, ] = $propertyFetchToVariable;
                if ($propertyFetchName !== $propertyFetchToRemove) {
                    continue;
                }

                // remove the assign
                unset($classMethod->stmts[$key]);
            }
        }
    }

    private function removeUnusedProperties(Class_ $class): void
    {
        foreach (array_keys($this->propertyFetchToParamsToRemoveFromConstructor) as $propertyFetchName) {
            /** @var string $propertyFetchName */
            $this->classManipulator->removeProperty($class, $propertyFetchName);
        }
    }

    private function removeEmptyConstruct(Class_ $class, ClassMethod $constructClassMethod): void
    {
        if ($constructClassMethod->stmts !== []) {
            return;
        }

        $this->removeNodeFromStatements($class, $constructClassMethod);
    }
}
