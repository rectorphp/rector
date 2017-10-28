<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Covers part of https://github.com/nikic/PHP-Parser/blob/master/UPGRADE-4.0.md
 */
final class IdentifierRector extends AbstractRector
{
    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    /**
     * @var string[][]
     */
    private $typeToPropertiesMap = [
        'PhpParser\Node\Const_' => ['name'],
        'PhpParser\Node\NullableType' => ['type'], // sometimes only
        'PhpParser\Node\Param' => ['type'],  // sometimes only
        'PhpParser\Node\Expr\ClassConstFetch' => ['name'],
        'PhpParser\Node\Expr\Closure' => ['returnType'], // sometimes only
        'PhpParser\Node\Expr\MethodCall' => ['name'],
        'PhpParser\Node\Expr\PropertyFetch' => ['name'],
        'PhpParser\Node\Expr\StaticCall' => ['name'],
        'PhpParser\Node\Expr\StaticPropertyFetch' => ['name'],
        'PhpParser\Node\Stmt\Class_' => ['name'],
        'PhpParser\Node\Stmt\ClassMethod' => ['name', 'returnType' /* sometimes only */],
        'PhpParser\Node\Stmt\Function' => ['name', 'returnType' /* sometimes only */],
        'PhpParser\Node\Stmt\Goto_' => ['name'],
        'PhpParser\Node\Stmt\Interface_' => ['name'],
        'PhpParser\Node\Stmt\Label' => ['name'],
        'PhpParser\Node\Stmt\PropertyProperty' => ['name'],
        'PhpParser\Node\Stmt\TraitUseAdaptation\Alias' => ['method', 'newName'],
        'PhpParser\Node\Stmt\TraitUseAdaptation\Precedence' => ['method'],
        'PhpParser\Node\Stmt\Trait_' => ['name'],
        'PhpParser\Node\Stmt\UseUse' => ['alias'],
    ];

    public function __construct(PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->propertyFetchAnalyzer->isTypes($node, array_keys($this->typeToPropertiesMap))) {
            return false;
        }

        /** @var PropertyFetch $node */
        $nodeTypes = $node->var->getAttribute(Attribute::TYPES);

        $properties = $this->matchTypeToProperties($nodeTypes);

        return $this->propertyFetchAnalyzer->isProperties($node, $properties);
    }

    /**
     * @param PropertyFetch $propertyFetchNode
     */
    public function refactor(Node $propertyFetchNode): ?Node
    {
        $parentNode = $propertyFetchNode->getAttribute(Attribute::PARENT_NODE);
        if ($parentNode instanceof MethodCall) {
            return $propertyFetchNode;
        }

        $methodCallNode = new MethodCall($propertyFetchNode, 'toString');
        $propertyFetchNode->setAttribute(Attribute::PARENT_NODE, $methodCallNode);

        return $methodCallNode;
    }

    /**
     * @param string[] $nodeTypes
     * @return string[]
     */
    private function matchTypeToProperties(array $nodeTypes): array
    {
        foreach ($nodeTypes as $nodeType) {
            if ($this->typeToPropertiesMap[$nodeType]) {
                return $this->typeToPropertiesMap[$nodeType];
            }
        }

        return [];
    }
}
