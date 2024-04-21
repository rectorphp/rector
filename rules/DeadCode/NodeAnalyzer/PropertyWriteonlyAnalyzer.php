<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\BetterNodeFinder;
final class PropertyWriteonlyAnalyzer
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function hasClassDynamicPropertyNames(Class_ $class) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($class, static function (Node $node) : bool {
            if (!$node instanceof PropertyFetch && !$node instanceof NullsafePropertyFetch) {
                return \false;
            }
            // has dynamic name - could be anything
            return $node->name instanceof Expr;
        });
    }
    /**
     * The property fetches are always only assigned to, nothing else
     *
     * @param array<PropertyFetch|StaticPropertyFetch|NullsafePropertyFetch> $propertyFetches
     */
    public function arePropertyFetchesExclusivelyBeingAssignedTo(array $propertyFetches) : bool
    {
        foreach ($propertyFetches as $propertyFetch) {
            if ((bool) $propertyFetch->getAttribute(AttributeKey::IS_MULTI_ASSIGN, \false)) {
                return \false;
            }
            if ((bool) $propertyFetch->getAttribute(AttributeKey::IS_ASSIGNED_TO, \false)) {
                return \false;
            }
            if ((bool) $propertyFetch->getAttribute(AttributeKey::IS_BEING_ASSIGNED, \false)) {
                continue;
            }
            return \false;
        }
        return \true;
    }
}
