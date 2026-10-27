<?php

declare (strict_types=1);
namespace Rector\Application;

use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\ClosureUse;
use PhpParser\Node\DeclareItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\StaticVar;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Reflection\ClassReflection;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeAnalyzer\ScopeAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Scope\AstClassReflectionFactory;
use Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver;
use Rector\NodeTypeResolver\PHPStan\Scope\ScopeFactory;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
/**
 * In case of changed node, we need to re-traverse the PHPStan Scope to make all the new nodes aware of what is going on.
 */
final class ChangedNodeScopeRefresher
{
    /**
     * @readonly
     */
    private PHPStanNodeScopeResolver $phpStanNodeScopeResolver;
    /**
     * @readonly
     */
    private ScopeAnalyzer $scopeAnalyzer;
    /**
     * @readonly
     */
    private SimpleCallableNodeTraverser $simpleCallableNodeTraverser;
    /**
     * @readonly
     */
    private AstClassReflectionFactory $astClassReflectionFactory;
    /**
     * @readonly
     */
    private ScopeFactory $scopeFactory;
    public function __construct(PHPStanNodeScopeResolver $phpStanNodeScopeResolver, ScopeAnalyzer $scopeAnalyzer, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, AstClassReflectionFactory $astClassReflectionFactory, ScopeFactory $scopeFactory)
    {
        $this->phpStanNodeScopeResolver = $phpStanNodeScopeResolver;
        $this->scopeAnalyzer = $scopeAnalyzer;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->astClassReflectionFactory = $astClassReflectionFactory;
        $this->scopeFactory = $scopeFactory;
    }
    public function refresh(Node $node, string $filePath, ?MutatingScope $mutatingScope): void
    {
        // nothing to refresh
        if (!$this->scopeAnalyzer->isRefreshable($node)) {
            return;
        }
        if (!$mutatingScope instanceof MutatingScope) {
            $errorMessage = sprintf('Node "%s" with is missing scope required for scope refresh', get_class($node));
            throw new ShouldNotHappenException($errorMessage);
        }
        /**
         * The reindex is needed to:
         *      - be used by PHPStan processNodes() that relies on indexed arrays start from 0
         *      - use traverser to avoid issues when multiples rules apply, and higher node remove deep node,
         *        which the next rule use deep node, for example:
         *              - first rule: - Class_ → ClassMethod → remove stmt with index 0
         *              - second rule: - ClassMethod → here fetch the index 0 that no longer exists
         */
        SimpleCallableNodeTraverser::traverse($node, fn(Node $subNode): ?Node => \Rector\Application\NodeAttributeReIndexer::reIndexNodeAttributes($subNode));
        // a class-like scope is already inside the class, so PHPStan cannot enter it again and would fail
        // on the class node, leaving its methods with the class scope; process the class stmts directly instead
        if ($node instanceof ClassLike && $mutatingScope->isInClass()) {
            $mutatingScope = $this->refreshClassReflection($node, $mutatingScope);
            // the class node itself is not part of its stmts, e.g. an Enum_ replaced by a new Class_ has no scope yet
            $node->setAttribute(AttributeKey::SCOPE, $mutatingScope);
            $this->phpStanNodeScopeResolver->processNodes($node->stmts, $filePath, $mutatingScope);
            return;
        }
        $stmts = $this->resolveStmts($node);
        $this->phpStanNodeScopeResolver->processNodes($stmts, $filePath, $mutatingScope);
    }
    /**
     * The in-class scope holds the class reflection of the original file, which does not know about
     * members the rule just added or removed; re-enter the class with the reflection of the changed node
     */
    private function refreshClassReflection(ClassLike $classLike, MutatingScope $mutatingScope): MutatingScope
    {
        if (!$classLike instanceof Class_ && !$classLike instanceof Interface_ && !$classLike instanceof Enum_) {
            return $mutatingScope;
        }
        if ($classLike instanceof Class_ && $classLike->isAnonymous()) {
            return $mutatingScope;
        }
        $classReflection = $this->astClassReflectionFactory->create($classLike, $mutatingScope);
        if (!$classReflection instanceof ClassReflection) {
            return $mutatingScope;
        }
        $currentClassReflection = $mutatingScope->getClassReflection();
        if (!$currentClassReflection instanceof ClassReflection || $currentClassReflection->getName() !== $classReflection->getName()) {
            return $mutatingScope;
        }
        $classOutsideScope = $this->scopeFactory->createFromFile($mutatingScope->getFile());
        if ($mutatingScope->isDeclareStrictTypes()) {
            $classOutsideScope = $classOutsideScope->enterDeclareStrictTypes();
        }
        $namespace = $mutatingScope->getNamespace();
        if ($namespace !== null) {
            $classOutsideScope = $classOutsideScope->enterNamespace($namespace);
        }
        return $classOutsideScope->enterClass($classReflection);
    }
    /**
     * @return Stmt[]
     */
    private function resolveStmts(Node $node): array
    {
        // Else_/ElseIf_ are not processed standalone by PHPStan, only as part of an If_, so their body
        // stays unscoped; wrap into an If_ so processNodes() scopes the real statements they hold
        if ($node instanceof Else_) {
            return [new If_(new ConstFetch(new Name('true')), ['stmts' => $node->stmts])];
        }
        if ($node instanceof ElseIf_) {
            return [new If_($node->cond, ['stmts' => $node->stmts])];
        }
        // Catch_ is not processed standalone by PHPStan, only as part of a TryCatch; an empty try
        // makes it a catch no throw point reaches, which PHPStan still walks with the catch scope
        if ($node instanceof Catch_) {
            return [new TryCatch([], [$node])];
        }
        if ($node instanceof Stmt) {
            return [$node];
        }
        if ($node instanceof Expr) {
            return [new Expression($node)];
        }
        // moved from Expr/Stmt to directly under Node on PHPParser 5
        if ($node instanceof ArrayItem) {
            return [new Expression(new Array_([$node]))];
        }
        if ($node instanceof ClosureUse) {
            $closure = new Closure();
            $closure->uses[] = $node;
            return [new Expression($closure)];
        }
        if ($node instanceof DeclareItem) {
            return [new Declare_([$node])];
        }
        if ($node instanceof PropertyItem) {
            return [new Property(Modifiers::PUBLIC, [$node])];
        }
        if ($node instanceof StaticVar) {
            return [new Static_([$node])];
        }
        if ($node instanceof UseItem) {
            return [new Use_([$node])];
        }
        if ($node instanceof Param) {
            $closure = new Closure();
            $closure->params[] = $node;
            return [new Expression($closure)];
        }
        if ($node instanceof AttributeGroup) {
            $class = new Class_(null);
            $class->attrGroups[] = $node;
            $this->setLineAttributesOnClass($class, $node);
            return [$class];
        }
        if ($node instanceof Attribute) {
            $class = new Class_(null);
            $class->attrGroups[] = new AttributeGroup([$node]);
            $this->setLineAttributesOnClass($class, $node);
            return [$class];
        }
        if ($node instanceof Arg) {
            $class = new Class_(null, [], ['startLine' => $node->getStartLine(), 'endLine' => $node->getEndLine()]);
            $new = new New_($class, [$node]);
            return [new Expression($new)];
        }
        $errorMessage = sprintf('Complete parent node of "%s" be a stmt.', get_class($node));
        throw new ShouldNotHappenException($errorMessage);
    }
    /**
     * @param \PhpParser\Node\Attribute|\PhpParser\Node\AttributeGroup $node
     */
    private function setLineAttributesOnClass(Class_ $class, $node): void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable([$class], function (Node $subNode) use ($node): Node {
            if ($subNode->getStartLine() >= 0 && $subNode->getEndLine() >= 0) {
                return $subNode;
            }
            $subNode->setAttribute('startLine', $node->getStartLine());
            $subNode->setAttribute('endLine', $node->getEndLine());
            return $subNode;
        });
    }
}
