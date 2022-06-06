<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\Rector;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Stmt;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PhpParser\Node\Stmt\Nop;
use RectorPrefix20220606\PhpParser\NodeTraverser;
use RectorPrefix20220606\PhpParser\NodeVisitor\ParentConnectingVisitor;
use RectorPrefix20220606\PhpParser\NodeVisitorAbstract;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use RectorPrefix20220606\Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use RectorPrefix20220606\Rector\Core\Application\ChangedNodeScopeRefresher;
use RectorPrefix20220606\Rector\Core\Configuration\CurrentNodeProvider;
use RectorPrefix20220606\Rector\Core\Console\Output\RectorOutputStyle;
use RectorPrefix20220606\Rector\Core\Contract\Rector\PhpRectorInterface;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Core\Exclusion\ExclusionManager;
use RectorPrefix20220606\Rector\Core\Logging\CurrentRectorProvider;
use RectorPrefix20220606\Rector\Core\NodeDecorator\CreatedByRuleDecorator;
use RectorPrefix20220606\Rector\Core\PhpParser\Comparing\NodeComparator;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\BetterNodeFinder;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\NodeFactory;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\Value\ValueResolver;
use RectorPrefix20220606\Rector\Core\ProcessAnalyzer\RectifiedAnalyzer;
use RectorPrefix20220606\Rector\Core\Provider\CurrentFileProvider;
use RectorPrefix20220606\Rector\Core\ValueObject\Application\File;
use RectorPrefix20220606\Rector\Core\ValueObject\RectifiedNode;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\NodeRemoval\NodeRemover;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;
use RectorPrefix20220606\Rector\PostRector\Collector\NodesToAddCollector;
use RectorPrefix20220606\Rector\PostRector\Collector\NodesToRemoveCollector;
use RectorPrefix20220606\Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix20220606\Symfony\Contracts\Service\Attribute\Required;
use RectorPrefix20220606\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
use RectorPrefix20220606\Symplify\Skipper\Skipper\Skipper;
abstract class AbstractRector extends NodeVisitorAbstract implements PhpRectorInterface
{
    /**
     * @var string[]
     */
    private const ATTRIBUTES_TO_MIRROR = [AttributeKey::SCOPE, AttributeKey::RESOLVED_NAME, AttributeKey::PARENT_NODE];
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
     * @var \Rector\PostRector\Collector\NodesToRemoveCollector
     */
    protected $nodesToRemoveCollector;
    /**
     * @var \Rector\Core\ValueObject\Application\File
     */
    protected $file;
    /**
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    protected $nodesToAddCollector;
    /**
     * @var \Rector\Core\Application\ChangedNodeScopeRefresher
     */
    protected $changedNodeScopeRefresher;
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
    public function autowire(NodesToRemoveCollector $nodesToRemoveCollector, NodesToAddCollector $nodesToAddCollector, NodeRemover $nodeRemover, NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeFactory $nodeFactory, PhpDocInfoFactory $phpDocInfoFactory, ExclusionManager $exclusionManager, StaticTypeMapper $staticTypeMapper, CurrentRectorProvider $currentRectorProvider, CurrentNodeProvider $currentNodeProvider, Skipper $skipper, ValueResolver $valueResolver, BetterNodeFinder $betterNodeFinder, NodeComparator $nodeComparator, CurrentFileProvider $currentFileProvider, RectifiedAnalyzer $rectifiedAnalyzer, CreatedByRuleDecorator $createdByRuleDecorator, ChangedNodeScopeRefresher $changedNodeScopeRefresher, RectorOutputStyle $rectorOutputStyle) : void
    {
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->nodesToAddCollector = $nodesToAddCollector;
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
        /** @var Node $originalNode */
        $originalNode = $node->getAttribute(AttributeKey::ORIGINAL_NODE) ?? clone $node;
        $createdByRule = $originalNode->getAttribute(AttributeKey::CREATED_BY_RULE) ?? [];
        if (\in_array(static::class, $createdByRule, \true)) {
            return null;
        }
        $this->currentRectorProvider->changeCurrentRector($this);
        // for PHP doc info factory and change notifier
        $this->currentNodeProvider->setNode($node);
        $originalAttributes = $node->getAttributes();
        $this->printDebugCurrentFileAndRule();
        $node = $this->refactor($node);
        // nothing to change → continue
        if ($node === null) {
            return null;
        }
        if ($node === []) {
            $errorMessage = \sprintf(self::EMPTY_NODE_ARRAY_MESSAGE, static::class);
            throw new ShouldNotHappenException($errorMessage);
        }
        /** @var Node[]|Node $node */
        $this->createdByRuleDecorator->decorate($node, $originalNode, static::class);
        /** @var Node $originalNode */
        $rectorWithLineChange = new RectorWithLineChange(\get_class($this), $originalNode->getLine());
        $this->file->addRectorClassWithLine($rectorWithLineChange);
        /** @var Node $originalNode */
        if (\is_array($node)) {
            $originalNodeHash = \spl_object_hash($originalNode);
            $this->nodesToReturn[$originalNodeHash] = $node;
            \reset($node);
            $firstNodeKey = \key($node);
            $this->mirrorComments($node[$firstNodeKey], $originalNode);
            // will be replaced in leaveNode() the original node must be passed
            return $originalNode;
        }
        // update parents relations - must run before connectParentNodes()
        /** @var Node $node */
        $this->mirrorAttributes($originalAttributes, $node);
        $currentScope = $originalNode->getAttribute(AttributeKey::SCOPE);
        $this->changedNodeScopeRefresher->refresh($node, $this->file->getSmartFileInfo(), $currentScope);
        $this->connectParentNodes($node);
        // is equals node type? return node early
        if (\get_class($originalNode) === \get_class($node)) {
            return $node;
        }
        // search "infinite recursion" in https://github.com/nikic/PHP-Parser/blob/master/doc/component/Walking_the_AST.markdown
        $originalNodeHash = \spl_object_hash($originalNode);
        if ($originalNode instanceof Stmt && $node instanceof Expr) {
            $node = new Expression($node);
        }
        $this->nodesToReturn[$originalNodeHash] = $node;
        return $node;
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
        if ($this->exclusionManager->isNodeSkippedByRector($node, $this)) {
            return \true;
        }
        $smartFileInfo = $this->file->getSmartFileInfo();
        if ($this->skipper->shouldSkipElementAndFileInfo($this, $smartFileInfo)) {
            return \true;
        }
        $rectifiedNode = $this->rectifiedAnalyzer->verify($this, $node, $this->file);
        return $rectifiedNode instanceof RectifiedNode;
    }
    /**
     * @param array<string, mixed> $originalAttributes
     */
    private function mirrorAttributes(array $originalAttributes, Node $newNode) : void
    {
        foreach ($originalAttributes as $attributeName => $oldAttributeValue) {
            if (!\in_array($attributeName, self::ATTRIBUTES_TO_MIRROR, \true)) {
                continue;
            }
            $newNode->setAttribute($attributeName, $oldAttributeValue);
        }
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
