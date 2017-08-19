<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\TypeContext;

// @todo: rename to ClassLikeType, noother types are here
final class TypeResolvingNodeVisitor extends NodeVisitorAbstract
{
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

        $variableType = null;

        if ($node instanceof Variable) {
            $nextNode = $node->getAttribute('next');
            if ($nextNode instanceof New_) {
                $variableType = $nextNode->class->toString();
                $variableName = $node->name;
                $this->typeContext->addLocalVariable($variableName, $variableType);
            } else {
                $variableType = $this->typeContext->getTypeForVariable((string) $node->name);
            }
        }

        if ($variableType) {
            $node->setAttribute('type', $variableType);
        }

        if ($node instanceof Assign && $node->var instanceof Variable && $node->expr instanceof Variable) {
            $this->typeContext->addAssign($node->var->name, $node->expr->name);
        }
    }
}
