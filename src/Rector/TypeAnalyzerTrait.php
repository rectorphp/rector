<?php declare(strict_types=1);

namespace Rector\Rector;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\NodeTypeAnalyzer;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait TypeAnalyzerTrait
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NodeTypeAnalyzer
     */
    private $nodeTypeAnalyzer;

    /**
     * @required
     */
    public function autowireTypeAnalyzerDependencies(
        NodeTypeResolver $nodeTypeResolver,
        NodeTypeAnalyzer $nodeTypeAnalyzer
    ): void {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeTypeAnalyzer = $nodeTypeAnalyzer;
    }

    protected function isType(Node $node, string $type): bool
    {
        $nodeTypes = $this->getTypes($node);

        // fnmatch support
        if (Strings::contains($type, '*')) {
            foreach ($nodeTypes as $nodeType) {
                if (fnmatch($type, $nodeType, FNM_NOESCAPE)) {
                    return true;
                }
            }

            return false;
        }

        return in_array($type, $nodeTypes, true);
    }

    /**
     * @param string[] $types
     */
    protected function isTypes(Node $node, array $types): bool
    {
        $nodeTypes = $this->getTypes($node);
        return (bool) array_intersect($types, $nodeTypes);
    }

    /**
     * @param string[] $types
     * @return string[]
     */
    protected function matchTypes(Node $node, array $types): array
    {
        return $this->isTypes($node, $types) ? $this->getTypes($node) : [];
    }

    protected function isStringType(Node $node): bool
    {
        return $this->nodeTypeAnalyzer->isStringType($node);
    }

    protected function isStringyType(Node $node): bool
    {
        return $this->nodeTypeAnalyzer->isStringyType($node);
    }

    protected function getStaticType(Node $node): ?Type
    {
        return $this->nodeTypeAnalyzer->getNodeStaticType($node);
    }

    protected function isNullableType(Node $node): bool
    {
        return $this->nodeTypeAnalyzer->isNullableType($node);
    }

    protected function isNullType(Node $node): bool
    {
        return $this->nodeTypeAnalyzer->isNullType($node);
    }

    protected function isBoolType(Node $node): bool
    {
        return $this->nodeTypeAnalyzer->isBoolType($node);
    }

    protected function isCountableType(Node $node): bool
    {
        return $this->nodeTypeAnalyzer->isCountableType($node);
    }

    protected function isArrayType(Node $node): bool
    {
        return $this->nodeTypeAnalyzer->isArrayType($node);
    }

    /**
     * @return string[]
     */
    protected function getTypes(Node $node): array
    {
        // @todo should be resolved by NodeTypeResolver internally
        if ($node instanceof MethodCall || $node instanceof PropertyFetch || $node instanceof ArrayDimFetch) {
            return $this->nodeTypeResolver->resolve($node->var);
        }

        return $this->nodeTypeResolver->resolve($node);
    }
}
