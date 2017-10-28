<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeFinder;
use Rector\Node\Attribute;
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

    /**
     * @var NodeFinder
     */
    private $nodeFinder;

    public function __construct(PropertyFetchAnalyzer $propertyFetchAnalyzer, NodeFinder $nodeFinder)
    {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->nodeFinder = $nodeFinder;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Expression) {
            return false;
        }

        $typedNode = $this->nodeFinder->findFirst($node, function (Node $node) {
            return $this->propertyFetchAnalyzer->isTypes($node, array_keys($this->typeToPropertiesMap));
        });

        if ($typedNode === null) {
            return false;
        }

        // his parent should not be method call
        $parentNode = $typedNode->getAttribute(Attribute::PARENT_NODE);
        if ($parentNode instanceof MethodCall) {
            return false;
        }

        /** @var PropertyFetch $typedNode */
        $nodeTypes = $typedNode->var->getAttribute(Attribute::TYPES);

        $properties = $this->matchTypeToProperties($nodeTypes);

        return $this->propertyFetchAnalyzer->isProperties($typedNode, $properties);
    }

    /**
     * @param Expression $expressionNode
     */
    public function refactor(Node $expressionNode): ?Node
    {
        return new Expression(
            new MethodCall($expressionNode->expr, 'toString')
        );
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
