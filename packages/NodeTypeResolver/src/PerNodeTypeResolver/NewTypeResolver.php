<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use Rector\Exception\NotImplementedException;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\TypeContext;

final class NewTypeResolver implements PerNodeTypeResolverInterface, NodeTypeResolverAwareInterface
{
    /**
     * @var TypeContext
     */
    private $typeContext;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(TypeContext $typeContext)
    {
        $this->typeContext = $typeContext;
    }

    public function getNodeClass(): string
    {
        return New_::class;
    }

    /**
     * @param New_ $newNode
     */
    public function resolve(Node $newNode): ?string
    {
        if ($this->shouldDelegate($newNode)) {
            return $this->nodeTypeResolver->resolve($newNode->class);
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
            $propertyName = $newNode->class->name->toString();

            return $this->typeContext->getTypeForProperty($propertyName);
        }

        return null;
    }

    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    private function shouldDelegate(New_ $newNode): bool
    {
        $nodeClass = get_class($newNode->class);

        return in_array($nodeClass, [ClassLike::class, Variable::class, Name::class], true);
    }
}
