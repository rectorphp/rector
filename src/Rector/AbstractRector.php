<?php

declare (strict_types=1);
namespace Rector\Core\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Core\Application\ChangedNodeScopeRefresher;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Logging\CurrentRectorProvider;
use Rector\Core\Logging\RectorOutput;
use Rector\Core\NodeDecorator\CreatedByRuleDecorator;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\Skipper\Skipper\Skipper;
use Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix202308\Symfony\Contracts\Service\Attribute\Required;
abstract class AbstractRector extends NodeVisitorAbstract implements PhpRectorInterface
{
    /**
     * @var string
     */
    private const EMPTY_NODE_ARRAY_MESSAGE = <<<CODE_SAMPLE
Array of nodes cannot be empty. Ensure "%s->refactor()" returns non-empty array for Nodes.

A) Direct return null for no change:

    return null;

B) Remove the Node:

    return NodeTraverser::REMOVE_NODE;
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
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    protected $nodeComparator;
    /**
     * @var \Rector\Core\ValueObject\Application\File
     */
    protected $file;
    /**
     * @var \PhpParser\Node\Stmt|null
     */
    protected $currentStmt;
    /**
     * @var \Rector\Core\Application\ChangedNodeScopeRefresher
     */
    private $changedNodeScopeRefresher;
    /**
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @var \Rector\Core\Logging\CurrentRectorProvider
     */
    private $currentRectorProvider;
    /**
     * @var \Rector\Core\Configuration\CurrentNodeProvider
     */
    private $currentNodeProvider;
    /**
     * @var \Rector\Skipper\Skipper\Skipper
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
     * @var \Rector\Core\NodeDecorator\CreatedByRuleDecorator
     */
    private $createdByRuleDecorator;
    /**
     * @var \Rector\Core\Logging\RectorOutput
     */
    private $rectorOutput;
    /**
     * @var string|null
     */
    private $toBeRemovedNodeHash;
    /**
     * @required
     */
    public function autowire(NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeFactory $nodeFactory, PhpDocInfoFactory $phpDocInfoFactory, StaticTypeMapper $staticTypeMapper, CurrentRectorProvider $currentRectorProvider, CurrentNodeProvider $currentNodeProvider, Skipper $skipper, ValueResolver $valueResolver, BetterNodeFinder $betterNodeFinder, NodeComparator $nodeComparator, CurrentFileProvider $currentFileProvider, CreatedByRuleDecorator $createdByRuleDecorator, ChangedNodeScopeRefresher $changedNodeScopeRefresher, RectorOutput $rectorOutput) : void
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeFactory = $nodeFactory;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->currentRectorProvider = $currentRectorProvider;
        $this->currentNodeProvider = $currentNodeProvider;
        $this->skipper = $skipper;
        $this->valueResolver = $valueResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
        $this->currentFileProvider = $currentFileProvider;
        $this->createdByRuleDecorator = $createdByRuleDecorator;
        $this->changedNodeScopeRefresher = $changedNodeScopeRefresher;
        $this->rectorOutput = $rectorOutput;
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
    public final function enterNode(Node $node)
    {
        if (!$this->isMatchingNodeType($node)) {
            return null;
        }
        $filePath = $this->file->getFilePath();
        if ($this->skipper->shouldSkipCurrentNode($this, $filePath, static::class, $node)) {
            return null;
        }
        $isDebug = $this->rectorOutput->isDebug();
        $this->currentRectorProvider->changeCurrentRector($this);
        // for PHP doc info factory and change notifier
        $this->currentNodeProvider->setNode($node);
        if ($isDebug) {
            $this->rectorOutput->printCurrentFileAndRule($filePath, static::class);
        }
        $this->changedNodeScopeRefresher->reIndexNodeAttributes($node);
        if ($isDebug) {
            $this->rectorOutput->startConsumptions();
        }
        // ensure origNode pulled before refactor to avoid changed during refactor, ref https://3v4l.org/YMEGN
        $originalNode = $node->getAttribute(AttributeKey::ORIGINAL_NODE) ?? $node;
        $refactoredNode = $this->refactor($node);
        if ($isDebug) {
            $this->rectorOutput->printConsumptions();
        }
        // @see NodeTraverser::* codes, e.g. removal of node of stopping the traversing
        if ($refactoredNode === NodeTraverser::REMOVE_NODE) {
            $this->toBeRemovedNodeHash = \spl_object_hash($originalNode);
            // notify this rule changing code
            $rectorWithLineChange = new RectorWithLineChange(static::class, $originalNode->getLine());
            $this->file->addRectorClassWithLine($rectorWithLineChange);
            return $originalNode;
        }
        if (\is_int($refactoredNode)) {
            $this->createdByRuleDecorator->decorate($node, $originalNode, static::class);
            // notify this rule changing code
            $rectorWithLineChange = new RectorWithLineChange(static::class, $originalNode->getLine());
            $this->file->addRectorClassWithLine($rectorWithLineChange);
            return $refactoredNode;
        }
        // nothing to change → continue
        if ($refactoredNode === null) {
            return null;
        }
        if ($refactoredNode === []) {
            $errorMessage = \sprintf(self::EMPTY_NODE_ARRAY_MESSAGE, static::class);
            throw new ShouldNotHappenException($errorMessage);
        }
        return $this->postRefactorProcess($originalNode, $node, $refactoredNode, $filePath);
    }
    /**
     * Replacing nodes in leaveNode() method avoids infinite recursion
     * see"infinite recursion" in https://github.com/nikic/PHP-Parser/blob/master/doc/component/Walking_the_AST.markdown
     */
    public function leaveNode(Node $node)
    {
        if ($this->toBeRemovedNodeHash !== null && $this->toBeRemovedNodeHash === \spl_object_hash($node)) {
            $this->toBeRemovedNodeHash = null;
            return NodeTraverser::REMOVE_NODE;
        }
        $objectHash = \spl_object_hash($node);
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
        if ($oldNode instanceof InlineHTML) {
            return;
        }
        $newNode->setAttribute(AttributeKey::PHP_DOC_INFO, $oldNode->getAttribute(AttributeKey::PHP_DOC_INFO));
        if (!$newNode instanceof Nop) {
            $newNode->setAttribute(AttributeKey::COMMENTS, $oldNode->getAttribute(AttributeKey::COMMENTS));
        }
    }
    /**
     * @param \PhpParser\Node|mixed[]|int $refactoredNode
     */
    private function postRefactorProcess(Node $originalNode, Node $node, $refactoredNode, string $filePath) : Node
    {
        /** @var non-empty-array<Node>|Node $refactoredNode */
        $this->createdByRuleDecorator->decorate($refactoredNode, $originalNode, static::class);
        $rectorWithLineChange = new RectorWithLineChange(static::class, $originalNode->getLine());
        $this->file->addRectorClassWithLine($rectorWithLineChange);
        /** @var MutatingScope|null $currentScope */
        $currentScope = $node->getAttribute(AttributeKey::SCOPE);
        // search "infinite recursion" in https://github.com/nikic/PHP-Parser/blob/master/doc/component/Walking_the_AST.markdown
        $originalNodeHash = \spl_object_hash($originalNode);
        if (\is_array($refactoredNode)) {
            $firstNode = \current($refactoredNode);
            $this->mirrorComments($firstNode, $originalNode);
            $this->refreshScopeNodes($refactoredNode, $filePath, $currentScope);
            // will be replaced in leaveNode() the original node must be passed
            $this->nodesToReturn[$originalNodeHash] = $refactoredNode;
            return $originalNode;
        }
        $this->refreshScopeNodes($refactoredNode, $filePath, $currentScope);
        $this->nodesToReturn[$originalNodeHash] = $refactoredNode;
        return $refactoredNode;
    }
    /**
     * @param mixed[]|\PhpParser\Node $node
     */
    private function refreshScopeNodes($node, string $filePath, ?MutatingScope $mutatingScope) : void
    {
        $nodes = $node instanceof Node ? [$node] : $node;
        foreach ($nodes as $node) {
            $this->changedNodeScopeRefresher->refresh($node, $mutatingScope, $filePath, $this->currentStmt);
        }
    }
    private function isMatchingNodeType(Node $node) : bool
    {
        $nodeClass = \get_class($node);
        foreach ($this->getNodeTypes() as $nodeType) {
            if (!\is_a($nodeClass, $nodeType, \true)) {
                if ($node instanceof Stmt) {
                    $this->currentStmt = $node;
                }
                continue;
            }
            return \true;
        }
        return \false;
    }
}
