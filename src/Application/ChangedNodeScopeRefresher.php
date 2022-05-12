<?php

declare (strict_types=1);
namespace Rector\Core\Application;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\MutatingScope;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * In case of changed node, we need to re-traverse the PHPStan Scope to make all the new nodes aware of what is going on.
 */
final class ChangedNodeScopeRefresher
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver
     */
    private $phpStanNodeScopeResolver;
    public function __construct(\Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver $phpStanNodeScopeResolver)
    {
        $this->phpStanNodeScopeResolver = $phpStanNodeScopeResolver;
    }
    /**
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Stmt|\PhpParser\Node $node
     */
    public function refresh($node, \Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo, \PHPStan\Analyser\MutatingScope $mutatingScope) : void
    {
        // nothing to refresh
        if ($node instanceof \PhpParser\Node\Identifier) {
            return;
        }
        // note from flight: when we traverse ClassMethod, the scope must be already in Class_, otherwise it crashes
        // so we need to somehow get a parent scope that is already in the same place the $node is
        if ($node instanceof \PhpParser\Node\Attribute) {
            // we'll have to fake-traverse 2 layers up, as PHPStan skips Scope for AttributeGroups and consequently Attributes
            $attributeGroup = new \PhpParser\Node\AttributeGroup([$node]);
            $node = new \PhpParser\Node\Stmt\Property(0, [], [], null, [$attributeGroup]);
        }
        // phpstan cannot process for some reason
        if ($node instanceof \PhpParser\Node\Stmt\Enum_) {
            return;
        }
        if ($node instanceof \PhpParser\Node\Stmt) {
            $stmts = [$node];
        } elseif ($node instanceof \PhpParser\Node\Expr) {
            $stmts = [new \PhpParser\Node\Stmt\Expression($node)];
        } else {
            if ($node instanceof \PhpParser\Node\Param) {
                // param type cannot be refreshed
                return;
            }
            if ($node instanceof \PhpParser\Node\Arg) {
                // arg type cannot be refreshed
                return;
            }
            $errorMessage = \sprintf('Complete parent node of "%s" be a stmt.', \get_class($node));
            throw new \Rector\Core\Exception\ShouldNotHappenException($errorMessage);
        }
        $this->phpStanNodeScopeResolver->processNodes($stmts, $smartFileInfo, $mutatingScope);
    }
}
