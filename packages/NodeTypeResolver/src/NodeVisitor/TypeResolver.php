<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpCsFixer\DocBlock\DocBlock;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitorAbstract;
use Rector\BetterReflection\Reflector\MethodReflector;
use Rector\Exception\NotImplementedException;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\TypeContext;

/**
 * Inspired by https://github.com/nikic/PHP-Parser/blob/9373a8e9f551516bc8e42aedeacd1b4f635d27fc/lib/PhpParser/NodeVisitor/NameResolver.php.
 */
final class TypeResolver extends NodeVisitorAbstract
{
    /**
     * @var TypeContext
     */
    private $typeContext;

    /**
     * @var callable[]
     */
    private $perNodeResolvers = [];

    /**
     * @var MethodReflector
     */
    private $methodReflector;

    public function __construct(TypeContext $typeContext, MethodReflector $methodReflector)
    {
        $this->typeContext = $typeContext;

        // consider mini subscribers
        $this->perNodeResolvers[Variable::class] = function (Variable $variableNode): void {
            $this->processVariableNode($variableNode);
        };
        $this->perNodeResolvers[Assign::class] = function (Assign $assignNode): void {
            $this->processAssignNode($assignNode);
        };

        // add subtypes for PropertyFetch
        $this->perNodeResolvers[PropertyFetch::class] = function (PropertyFetch $propertyFetchNode): void {
            $this->processPropertyFetch($propertyFetchNode);
        };

        $this->perNodeResolvers[Property::class] = function (Property $propertyNode): void {
            $this->processProperty($propertyNode);
        };

        $this->methodReflector = $methodReflector;
    }

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): void
    {
        $this->typeContext->startFile();
    }

    public function enterNode(Node $node): void
    {
        if ($node instanceof ClassLike) {
            $this->typeContext->enterClass($node);

            return;
        }

        if ($node instanceof FunctionLike) {
            $this->typeContext->enterFunction($node);

            return;
        }

        $nodeClass = get_class($node);

        if (isset($this->perNodeResolvers[$nodeClass])) {
            $this->perNodeResolvers[$nodeClass]($node);
        }
    }

    private function getTypeFromNewNode(New_ $newNode): string
    {
        // e.g. new $someClass;
        if ($newNode->class instanceof Variable) {
            // can be anything (dynamic)
            $variableName = $newNode->class->name;

            return $this->typeContext->getTypeForVariable($variableName);
        }

        // e.g. new SomeClass;
        if ($newNode->class instanceof Name) {
            /** @var FullyQualified $fqnName */
            $fqnName = $newNode->class->getAttribute(Attribute::RESOLVED_NAME);

            return $fqnName->toString();
        }

        // e.g. new $this->templateClass;
        if ($newNode->class instanceof PropertyFetch) {
            if ($newNode->class->var->name !== 'this') {
                throw new NotImplementedException(sprintf(
                    'Not implemented yet. Go to "%s()" and add check for "%s" node for external dependency.',
                    __METHOD__,
                    get_class($newNode->class)
                ));
            }

            // can be anything (dynamic)
            $propertyName = (string) $newNode->class->name;

            return $this->typeContext->getTypeForProperty($propertyName);
        }

        throw new NotImplementedException(sprintf(
            'Not implemented yet. Go to "%s()" and add check for "%s" node.',
            __METHOD__,
            get_class($newNode->class)
        ));
    }

    private function processVariableNode(Variable $variableNode): void
    {
        $variableType = null;

        $parentNode = $variableNode->getAttribute(Attribute::PARENT_NODE);
        if ($parentNode instanceof Assign) {
            $variableType = $this->processVariableTypeForAssign($variableNode, $parentNode);
        } elseif ($variableNode->name instanceof Variable) {
            // nested: ${$type}[$name] - dynamic, unable to resolve type
            return;
        } else {
            $variableType = $this->typeContext->getTypeForVariable((string) $variableNode->name);
        }

        if ($variableNode->name === 'this') {
            $variableType = $variableNode->getAttribute(Attribute::CLASS_NAME);
        }

        if ($variableType) {
            $variableNode->setAttribute(Attribute::TYPE, $variableType);
        }
    }

    private function processAssignNode(Assign $assignNode): void
    {
        if (! $assignNode->var instanceof Variable) {
            return;
        }

        // $var = $anotherVar;
        if ($assignNode->expr instanceof Variable) {
            $this->processAssignVariableNode($assignNode);

            // $var = $anotherVar->method();
        } elseif ($assignNode->expr instanceof MethodCall) {
            $this->processAssignMethodReturn($assignNode);
        }
    }

    private function processPropertyFetch(PropertyFetch $propertyFetchNode): void
    {
        // e.g. $r->getParameters()[0]->name
        if ($propertyFetchNode->var instanceof ArrayDimFetch) {
            $this->processArrayDimMethodCall($propertyFetchNode);

            return;
        }

        if ($propertyFetchNode->var instanceof New_) {
            $propertyType = $propertyFetchNode->var->class->toString();
            $propertyFetchNode->setAttribute(Attribute::TYPE, $propertyType);

            return;
        }

        if ($propertyFetchNode->var->name !== 'this') {
            return;
        }

        $propertyName = $this->resolvePropertyName($propertyFetchNode);
        $propertyType = $this->typeContext->getTypeForProperty($propertyName);

        if ($propertyType) {
            $propertyFetchNode->setAttribute(Attribute::TYPE, $propertyType);
        }
    }

    private function processProperty(Property $propertyNode): void
    {
        $propertyName = (string) $propertyNode->props[0]->name;
        $propertyType = $this->typeContext->getTypeForProperty($propertyName);

        if ($propertyType === null) {
            $propertyType = $this->resolveTypeFromPropertyDocComment($propertyNode);
            if ($propertyType) {
                $this->typeContext->addPropertyType($propertyName, $propertyType);
            }
        }

        if ($propertyType) {
            $propertyNode->setAttribute(Attribute::TYPE, $propertyType);
        }
    }

    private function processArrayDimMethodCall(PropertyFetch $propertyFetchNode): void
    {
        if ($propertyFetchNode->var->var instanceof MethodCall) {
            /** @var Variable $variableNode */
            $variableNode = $propertyFetchNode->var->var->var;
            $variableName = $variableNode->name;
            $variableType = $this->typeContext->getTypeForVariable($variableName);

            $variableNode->setAttribute(Attribute::TYPE, $variableType);
        }
    }

    private function resolvePropertyName(PropertyFetch $propertyFetchNode): string
    {
        if ($propertyFetchNode->name instanceof Variable) {
            return $propertyFetchNode->name->name;
        }

        if ($propertyFetchNode->name instanceof Concat) {
            return '';
        }

        return (string) $propertyFetchNode->name;
    }

    private function processVariableTypeForAssign(Variable $variableNode, Assign $AssignNode): string
    {
        if ($AssignNode->expr instanceof New_) {
            $variableName = $variableNode->name;
            $variableType = $this->getTypeFromNewNode($AssignNode->expr);

            $this->typeContext->addVariableWithType($variableName, $variableType);

            return $variableType;
        }

        if ($variableNode->name instanceof Variable) {
            $name = $variableNode->name->name;

            return $this->typeContext->getTypeForVariable($name);
        }

        $name = (string) $variableNode->name;

        return $this->typeContext->getTypeForVariable($name);
    }

    private function processAssignVariableNode(Assign $assignNode): void
    {
        if ($assignNode->var->name instanceof Variable) {
            $name = $assignNode->var->name->name;
        } else {
            $name = $assignNode->var->name;
        }

        $this->typeContext->addAssign($name, $assignNode->expr->name);

        $variableType = $this->typeContext->getTypeForVariable($name);
        if ($variableType) {
            $assignNode->var->setAttribute(Attribute::TYPE, $variableType);
        }
    }

    private function processAssignMethodReturn(Assign $assignNode): void
    {
        $variableType = null;

        // 1. get $anotherVar type

        /** @var Variable|mixed $methodCallVariable */
        $methodCallVariable = $assignNode->expr->var;

        if (! $methodCallVariable instanceof Variable) {
            return;
        }

        $methodCallVariableName = (string) $methodCallVariable->name;

        $methodCallVariableType = $this->typeContext->getTypeForVariable($methodCallVariableName);

        $methodCallName = $this->resolveMethodCallName($assignNode);

        // 2. get method() return type

        if (! $methodCallVariableType || ! $methodCallName) {
            return;
        }

        $variableType = $this->getMethodReturnType($methodCallVariableType, $methodCallName);

        if ($variableType) {
            $variableName = $assignNode->var->name;
            $this->typeContext->addVariableWithType($variableName, $variableType);
            $methodCallVariable->setAttribute(Attribute::TYPE, $variableType);
        }
    }

    /**
     * Dummy static method call return type that doesn't depend on class reflection.
     */
    private function fallbackStaticType(string $type, string $methodName): ?string
    {
        if ($type === 'Nette\Config\Configurator' && $methodName === 'createContainer') {
            return 'Nette\DI\Container';
        }

        return null;
    }

    private function getMethodReturnType(string $methodCallVariableType, string $methodCallName): ?string
    {
        $methodReflection = $this->methodReflector->reflectClassMethod($methodCallVariableType, $methodCallName);

        if ($methodReflection) {
            $returnType = $methodReflection->getReturnType();
            if ($returnType) {
                return (string) $returnType;
            }
        }

        return $this->fallbackStaticType($methodCallVariableType, $methodCallName);
    }

    private function resolveMethodCallName(Assign $assignNode): ?string
    {
        if ($assignNode->expr->name instanceof Variable) {
            return $assignNode->expr->name->name;
        }

        if ($assignNode->expr->name instanceof PropertyFetch) {
            // not implemented yet
            return null;
        }

        return (string) $assignNode->expr->name;
    }

    private function resolveTypeFromPropertyDocComment(Property $propertyNode): ?string
    {
        $doc = $propertyNode->getDocComment();
        if ($doc === null) {
            return null;
        }

        $docBlock = new DocBlock($doc->getText());
        $varAnnotations = $docBlock->getAnnotationsOfType('var');
        if (! count($varAnnotations)) {
            return null;
        }

        // @todo: resolve non-FQN names using namespace imports
        // $propertyNode->getAttribute(Attribute::USE_STATEMENTS)
        // maybe decouple to service?
        $varTypes = $varAnnotations[0]->getTypes();
        if (! count($varTypes)) {
            return null;
        }

        return $varTypes[0];
    }
}
