<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
final class PropertyWriteonlyAnalyzer
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeTypeResolver $nodeTypeResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function hasClassDynamicPropertyNames(Class_ $class) : bool
    {
        $isImplementsJsonSerializable = $this->nodeTypeResolver->isObjectType($class, new ObjectType('JsonSerializable'));
        return (bool) $this->betterNodeFinder->findFirst($class, function (Node $node) use($isImplementsJsonSerializable) : bool {
            if ($isImplementsJsonSerializable && $node instanceof FuncCall && $this->nodeNameResolver->isName($node, 'get_object_vars') && !$node->isFirstClassCallable()) {
                $firstArg = $node->getArgs()[0] ?? null;
                if ($firstArg instanceof Arg && $firstArg->value instanceof Variable && $firstArg->value->name === 'this') {
                    return \true;
                }
            }
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
