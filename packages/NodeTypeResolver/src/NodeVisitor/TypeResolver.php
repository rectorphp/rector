<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\TypeContext;

/**
 * Inspired by https://github.com/nikic/PHP-Parser/blob/9373a8e9f551516bc8e42aedeacd1b4f635d27fc/lib/PhpParser/NodeVisitor/NameResolver.php.
 */
final class TypeResolver extends NodeVisitorAbstract
{
    /**
     * @var string
     */
    private const TYPE_ATTRIBUTE = 'type';

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
        /** @var FullyQualified $fqnName */
        $fqnName = $newNode->class->getAttribute('resolvedName');

        return $fqnName->toString();
    }

    private function processVariableNode(Variable $variableNode): void
    {
        $variableType = null;

        $parentNode = $variableNode->getAttribute('parent');
        if ($parentNode instanceof Assign) {
            if ($parentNode->expr instanceof New_) {
                $variableName = $variableNode->name;
                $variableType = $this->getTypeFromNewNode($parentNode->expr);

                $this->typeContext->addVariableWithType($variableName, $variableType);
            }
        } else {
            $variableType = $this->typeContext->getTypeForVariable((string) $variableNode->name);
        }

        if ($variableType) {
            $variableNode->setAttribute(self::TYPE_ATTRIBUTE, $variableType);
        }
    }

    private function processAssignNode(Assign $assignNode): void
    {
        if ($assignNode->var instanceof Variable && $assignNode->expr instanceof Variable) {
            $this->typeContext->addAssign($assignNode->var->name, $assignNode->expr->name);

            $variableType = $this->typeContext->getTypeForVariable($assignNode->var->name);
            if ($variableType) {
                $assignNode->var->setAttribute(self::TYPE_ATTRIBUTE, $variableType);
            }
        }
    }

    private function processPropertyFetch(PropertyFetch $propertyFetchNode): void
    {
        if ($propertyFetchNode->var->name !== 'this') {
            return;
        }

        $propertyName = (string) $propertyFetchNode->name;
        $propertyType = $this->typeContext->getTypeForProperty($propertyName);

        if ($propertyType) {
            $propertyFetchNode->setAttribute(self::TYPE_ATTRIBUTE, $propertyType);
        }
    }

    private function processProperty(Property $propertyNode): void
    {
        $propertyName = (string) $propertyNode->props[0]->name;
        $propertyType = $this->typeContext->getTypeForProperty($propertyName);

        if ($propertyType) {
            $propertyNode->setAttribute(self::TYPE_ATTRIBUTE, $propertyType);
        }
    }
}
