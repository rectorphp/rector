<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeVisitorAbstract;
use Rector\BetterReflection\Reflector\MethodReflector;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\ClassAnalyzer;
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
     * @var callable[]
     */
    private $perNodeResolvers = [];

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        TypeContext $typeContext,
        MethodReflector $methodReflector,
        ClassAnalyzer $classAnalyzer,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->typeContext = $typeContext;

        // add subtypes for PropertyFetch
        $this->perNodeResolvers[PropertyFetch::class] = function (PropertyFetch $propertyFetchNode): void {
            $this->processPropertyFetch($propertyFetchNode);
        };

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

        $type = $this->nodeTypeResolver->resolve($node);
        if ($type) {
            $node->setAttribute(Attribute::TYPE, $type);
        }
    }

    private function processPropertyFetch(PropertyFetch $propertyFetchNode): void
    {
        // e.g. $r->getParameters()[0]->name
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
}
