<?php

declare (strict_types=1);
namespace Rector\Core\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Core\Application\ChangedNodeScopeRefresher;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Console\Output\RectorOutputStyle;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Exclusion\ExclusionManager;
use Rector\Core\Logging\CurrentRectorProvider;
use Rector\Core\NodeDecorator\CreatedByRuleDecorator;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\ProcessAnalyzer\RectifiedAnalyzer;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\RectifiedNode;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeRemoval\NodeRemover;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PostRector\Collector\NodesToRemoveCollector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix202208\Symfony\Contracts\Service\Attribute\Required;
use RectorPrefix202208\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
use RectorPrefix202208\Symplify\Skipper\Skipper\Skipper;
abstract class AbstractRector extends NodeVisitorAbstract implements PhpRectorInterface
{
    /**
     * @var string
     */
    private const EMPTY_NODE_ARRAY_MESSAGE = <<<CODE_SAMPLE
Array of nodes cannot be empty. Ensure "%s->refactor()" returns non-empty array for Nodes.

A) Return null for no change:

    return null;

B) Remove the Node:

    \$this->removeNode(\$node);
    return \$node;
CODE_SAMPLE;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    protected $nodeNameResolver;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    protected $nodeTypeResolver;
    /**
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    protected $staticTypeMapper;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    protected $phpDocInfoFactory;
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    protected $nodeFactory;
    /**
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    protected $valueResolver;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    protected $betterNodeFinder;
    /**
     * @var \Rector\NodeRemoval\NodeRemover
     */
    protected $nodeRemover;
    /**
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    protected $nodeComparator;
    /**
     * @var \Rector\Core\ValueObject\Application\File
     */
    protected $file;
    /**
     * @var \Rector\Core\Application\ChangedNodeScopeRefresher
     */
    protected $changedNodeScopeRefresher;
    /**
     * @var \Rector\PostRector\Collector\NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;
    /**
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @var \Rector\Core\Exclusion\ExclusionManager
     */
    private $exclusionManager;
    /**
     * @var \Rector\Core\Logging\CurrentRectorProvider
     */
    private $currentRectorProvider;
    /**
     * @var \Rector\Core\Configuration\CurrentNodeProvider
     */
    private $currentNodeProvider;
    /**
     * @var \Symplify\Skipper\Skipper\Skipper
     */
    private $skipper;
    /**
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @var array<string, Node[]|Node>
     */
    private $nodesToReturn = [];
    /**
     * @var \Rector\Core\ProcessAnalyzer\RectifiedAnalyzer
     */
    private $rectifiedAnalyzer;
    /**
     * @var \Rector\Core\NodeDecorator\CreatedByRuleDecorator
     */
    private $createdByRuleDecorator;
    /**
     * @var \Rector\Core\Console\Output\RectorOutputStyle
     */
    private $rectorOutputStyle;
    /**
     * @required
     */
    public function autowire(NodesToRemoveCollector $nodesToRemoveCollector, NodeRemover $nodeRemover, NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeFactory $nodeFactory, PhpDocInfoFactory $phpDocInfoFactory, ExclusionManager $exclusionManager, StaticTypeMapper $staticTypeMapper, CurrentRectorProvider $currentRectorProvider, CurrentNodeProvider $currentNodeProvider, Skipper $skipper, ValueResolver $valueResolver, BetterNodeFinder $betterNodeFinder, NodeComparator $nodeComparator, CurrentFileProvider $currentFileProvider, RectifiedAnalyzer $rectifiedAnalyzer, CreatedByRuleDecorator $createdByRuleDecorator, ChangedNodeScopeRefresher $changedNodeScopeRefresher, RectorOutputStyle $rectorOutputStyle) : void
    {
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->nodeRemover = $nodeRemover;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeFactory = $nodeFactory;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->exclusionManager = $exclusionManager;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->currentRectorProvider = $currentRectorProvider;
        $this->currentNodeProvider = $currentNodeProvider;
        $this->skipper = $skipper;
        $this->valueResolver = $valueResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
        $this->currentFileProvider = $currentFileProvider;
        $this->rectifiedAnalyzer = $rectifiedAnalyzer;
        $this->createdByRuleDecorator = $createdByRuleDecorator;
        $this->changedNodeScopeRefresher = $changedNodeScopeRefresher;
        $this->rectorOutputStyle = $rectorOutputStyle;
    }
    /**
     * @return Node[]|null
     */
    public function beforeTraverse(array $nodes) : ?array
    {
        // workaround for file around refactor()
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            throw new ShouldNotHappenException('File object is missing. Make sure you call $this->currentFileProvider->setFile(...) before traversing.');
        }
        $this->file = $file;
        return parent::beforeTraverse($nodes);
    }
    /**
     * @return Node|null
     */
    public final function enterNode(Node $node)
    {
        $nodeClass = \get_class($node);
        if (!$this->isMatchingNodeType($nodeClass)) {
            return null;
        }
        if ($this->shouldSkipCurrentNode($node)) {
            return null;
        }
        $this->currentRectorProvider->changeCurrentRector($this);
        // for PHP doc info factory and change notifier
        $this->currentNodeProvider->setNode($node);
        $this->printDebugCurrentFileAndRule();
        $refactoredNode = $this->refactor($node);
        // nothing to change → continue
        if ($refactoredNode === null) {
            return null;
        }
        if ($refactoredNode === []) {
            $errorMessage = \sprintf(self::EMPTY_NODE_ARRAY_MESSAGE, static::class);
            throw new ShouldNotHappenException($errorMessage);
        }
        /** @var Node $originalNode */
        $originalNode = $node->getAttribute(AttributeKey::ORIGINAL_NODE) ?? $node;
        /** @var Node[]|Node $refactoredNode */
        $this->createdByRuleDecorator->decorate($refactoredNode, $originalNode, static::class);
        $rectorWithLineChange = new RectorWithLineChange(\get_class($this), $originalNode->getLine());
        $this->file->addRectorClassWithLine($rectorWithLineChange);
        if (\is_array($refactoredNode)) {
            $originalNodeHash = \spl_object_hash($originalNode);
            $this->nodesToReturn[$originalNodeHash] = $refactoredNode;
            \reset($refactoredNode);
            $firstNodeKey = \key($refactoredNode);
            $this->mirrorComments($refactoredNode[$firstNodeKey], $originalNode);
            // will be replaced in leaveNode() the original node must be passed
            return $originalNode;
        }
        if ($node->hasAttribute(AttributeKey::PARENT_NODE)) {
            // update parents relations - must run before connectParentNodes()
            $refactoredNode->setAttribute(AttributeKey::PARENT_NODE, $node->getAttribute(AttributeKey::PARENT_NODE));
            $this->connectParentNodes($refactoredNode);
        }
        $currentScope = $originalNode->getAttribute(AttributeKey::SCOPE);
        $this->changedNodeScopeRefresher->refresh($refactoredNode, $currentScope, $this->file->getSmartFileInfo());
        // is equals node type? return node early
        if (\get_class($originalNode) === \get_class($refactoredNode)) {
            return $refactoredNode;
        }
        // search "infinite recursion" in https://github.com/nikic/PHP-Parser/blob/master/doc/component/Walking_the_AST.markdown
        $originalNodeHash = \spl_object_hash($originalNode);
        $refactoredNode = $originalNode instanceof Stmt && $refactoredNode instanceof Expr ? new Expression($refactoredNode) : $refactoredNode;
        $this->nodesToReturn[$originalNodeHash] = $refactoredNode;
        return $refactoredNode;
    }
    /**
     * Replacing nodes in leaveNode() method avoids infinite recursion
     * see"infinite recursion" in https://github.com/nikic/PHP-Parser/blob/master/doc/component/Walking_the_AST.markdown
     */
    public function leaveNode(Node $node)
    {
        $objectHash = \spl_object_hash($node);
        // update parents relations!!!
        return $this->nodesToReturn[$objectHash] ?? $node;
    }
    protected function isName(Node $node, string $name) : bool
    {
        return $this->nodeNameResolver->isName($node, $name);
    }
    /**
     * @param string[] $names
     */
    protected function isNames(Node $node, array $names) : bool
    {
        return $this->nodeNameResolver->isNames($node, $names);
    }
    protected function getName(Node $node) : ?string
    {
        return $this->nodeNameResolver->getName($node);
    }
    protected function isObjectType(Node $node, ObjectType $objectType) : bool
    {
        return $this->nodeTypeResolver->isObjectType($node, $objectType);
    }
    /**
     * Use this method for getting expr|node type
     */
    protected function getType(Node $node) : Type
    {
        return $this->nodeTypeResolver->getType($node);
    }
    /**
     * @param \PhpParser\Node|mixed[] $nodes
     * @param callable(Node $node): (Node|null|int) $callable
     */
    protected function traverseNodesWithCallable($nodes, callable $callable) : void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($nodes, $callable);
    }
    protected function mirrorComments(Node $newNode, Node $oldNode) : void
    {
        if ($this->nodeComparator->areSameNode($newNode, $oldNode)) {
            return;
        }
        $newNode->setAttribute(AttributeKey::PHP_DOC_INFO, $oldNode->getAttribute(AttributeKey::PHP_DOC_INFO));
        if (!$newNode instanceof Nop) {
            $newNode->setAttribute(AttributeKey::COMMENTS, $oldNode->getAttribute(AttributeKey::COMMENTS));
        }
    }
    /**
     * @param Arg[] $currentArgs
     * @param Arg[] $appendingArgs
     * @return Arg[]
     */
    protected function appendArgs(array $currentArgs, array $appendingArgs) : array
    {
        foreach ($appendingArgs as $appendingArg) {
            $currentArgs[] = new Arg($appendingArg->value);
        }
        return $currentArgs;
    }
    protected function removeNode(Node $node) : void
    {
        $this->nodeRemover->removeNode($node);
    }
    /**
     * @param class-string<Node> $nodeClass
     */
    private function isMatchingNodeType(string $nodeClass) : bool
    {
        foreach ($this->getNodeTypes() as $nodeType) {
            if (\is_a($nodeClass, $nodeType, \true)) {
                return \true;
            }
        }
        return \false;
    }
    private function shouldSkipCurrentNode(Node $node) : bool
    {
        if ($this->nodesToRemoveCollector->isNodeRemoved($node)) {
            return \true;
        }
        if ($this->exclusionManager->isNodeSkippedByRector($node, static::class)) {
            return \true;
        }
        $smartFileInfo = $this->file->getSmartFileInfo();
        if ($this->skipper->shouldSkipElementAndFileInfo($this, $smartFileInfo)) {
            return \true;
        }
        $rectifiedNode = $this->rectifiedAnalyzer->verify(static::class, $node, $smartFileInfo);
        return $rectifiedNode instanceof RectifiedNode;
    }
    private function connectParentNodes(Node $node) : void
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new ParentConnectingVisitor());
        $nodeTraverser->traverse([$node]);
    }
    private function printDebugCurrentFileAndRule() : void
    {
        if (!$this->rectorOutputStyle->isDebug()) {
            return;
        }
        $this->rectorOutputStyle->writeln('[file] ' . $this->file->getRelativeFilePath());
        $this->rectorOutputStyle->writeln('[rule] ' . static::class);
        $this->rectorOutputStyle->newLine(1);
    }
}
