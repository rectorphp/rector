<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeVisitorAbstract;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;
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
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(TypeContext $typeContext, NodeTypeResolver $nodeTypeResolver)
    {
        $this->typeContext = $typeContext;
        $this->nodeTypeResolver = $nodeTypeResolver;
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
            $this->typeContext->enterClassLike($node);
        }

        if ($node instanceof FunctionLike) {
            $this->typeContext->enterFunction($node);
        }

        $types = $this->nodeTypeResolver->resolve($node);
        if ($types) {
            $node->setAttribute(Attribute::TYPES, $types);
        }
    }
}
