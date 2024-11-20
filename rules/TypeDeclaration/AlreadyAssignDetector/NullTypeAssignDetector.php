<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\AlreadyAssignDetector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeVisitor;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer;
use Rector\TypeDeclaration\Matcher\PropertyAssignMatcher;
/**
 * Should add extra null type
 */
final class NullTypeAssignDetector
{
    /**
     * @readonly
     */
    private DoctrineTypeAnalyzer $doctrineTypeAnalyzer;
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @readonly
     */
    private PropertyAssignMatcher $propertyAssignMatcher;
    /**
     * @readonly
     */
    private SimpleCallableNodeTraverser $simpleCallableNodeTraverser;
    public function __construct(DoctrineTypeAnalyzer $doctrineTypeAnalyzer, NodeTypeResolver $nodeTypeResolver, PropertyAssignMatcher $propertyAssignMatcher, SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->doctrineTypeAnalyzer = $doctrineTypeAnalyzer;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->propertyAssignMatcher = $propertyAssignMatcher;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    public function detect(ClassLike $classLike, string $propertyName) : bool
    {
        $needsNullType = \false;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classLike->stmts, function (Node $node) use($propertyName, &$needsNullType) : ?int {
            $expr = $this->matchAssignExprToPropertyName($node, $propertyName);
            if (!$expr instanceof Expr) {
                return null;
            }
            // not in doctrine property
            $staticType = $this->nodeTypeResolver->getType($expr);
            if ($this->doctrineTypeAnalyzer->isDoctrineCollectionWithIterableUnionType($staticType)) {
                $needsNullType = \false;
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            return null;
        });
        return $needsNullType;
    }
    private function matchAssignExprToPropertyName(Node $node, string $propertyName) : ?Expr
    {
        if (!$node instanceof Assign) {
            return null;
        }
        return $this->propertyAssignMatcher->matchPropertyAssignExpr($node, $propertyName);
    }
}
