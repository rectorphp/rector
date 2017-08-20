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
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\TypeContext;

/**
 * Inspired by https://github.com/nikic/PHP-Parser/blob/9373a8e9f551516bc8e42aedeacd1b4f635d27fc/lib/PhpParser/NodeVisitor/NameResolver.php.
 */
final class ClassLikeTypeResolver extends NodeVisitorAbstract
{
    /**
     * @var string
     */
    private const TYPE_ATTRIBUTE = 'type';

    /**
     * @var TypeContext
     */
    private $typeContext;

    public function __construct(TypeContext $typeContext)
    {
        $this->typeContext = $typeContext;
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
        }

        if ($node instanceof FunctionLike) {
            $this->typeContext->enterFunction($node);
        }

        if ($node instanceof Variable) {
            $this->processVariableNode($node);
        }

        if ($node instanceof Assign) {
            $this->processAssignNode($node);
        }

        if ($node instanceof PropertyFetch) {
            $this->processPropertyFetch($node);
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
}
