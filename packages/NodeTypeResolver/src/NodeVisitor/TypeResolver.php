<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
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

    public function __construct(TypeContext $typeContext)
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
            if ($parentNode->expr instanceof New_) {
                $variableName = $variableNode->name;
                $variableType = $this->getTypeFromNewNode($parentNode->expr);

                $this->typeContext->addVariableWithType($variableName, $variableType);
            } else {
                if ($variableNode->name instanceof Variable) {
                    $name = $variableNode->name->name;
                } else {
                    $name = (string) $variableNode->name;
                }

                $variableType = $this->typeContext->getTypeForVariable($name);
            }
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
        if ($assignNode->var instanceof Variable && $assignNode->expr instanceof Variable) {
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

        return (string) $propertyFetchNode->name;
    }
}
