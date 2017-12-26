<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
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

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return ['Expr_New'];
    }

    /**
     * @param New_ $newNode
     * @return string[]
     */
    public function resolve(Node $newNode): array
    {
        if ($this->shouldDelegate($newNode)) {
            return $this->nodeTypeResolver->resolve($newNode->class);
        }

        // e.g. new $this->templateClass;
        if ($newNode->class instanceof PropertyFetch) {
            if (! $newNode->class->var instanceof Variable) {
                return [];
            }

            /** @var Variable $variableNode */
            $variableNode = $newNode->class->var;

            if ($variableNode->name !== 'this') {
                throw new NotImplementedException(sprintf(
                    'Not implemented yet. Go to "%s()" and add check for "%s" node for external dependency.',
                    __METHOD__,
                    get_class($newNode->class)
                ));
            }

            // can be anything (dynamic)
            /** @var Identifier $identifierNode */
            $identifierNode = $newNode->class->name;

            $propertyName = $identifierNode->toString();

            return $this->typeContext->getTypesForProperty($propertyName);
        }

        return [];
    }

    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    private function shouldDelegate(New_ $newNode): bool
    {
        foreach ([Class_::class, Variable::class, Name::class] as $typeToSkip) {
            if ($newNode->class instanceof $typeToSkip) {
                return true;
            }
        }

        return false;
    }
}
