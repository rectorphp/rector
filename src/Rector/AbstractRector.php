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
use Rector\PostRector\Collector\NodesToAddCollector;
use Rector\PostRector\Collector\NodesToRemoveCollector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix20220531\Symfony\Contracts\Service\Attribute\Required;
use RectorPrefix20220531\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
use RectorPrefix20220531\Symplify\Skipper\Skipper\Skipper;
abstract class AbstractRector extends \PhpParser\NodeVisitorAbstract implements \Rector\Core\Contract\Rector\PhpRectorInterface
{
    /**
     * @var string[]
     */
    private const ATTRIBUTES_TO_MIRROR = [\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE, \Rector\NodeTypeResolver\Node\AttributeKey::RESOLVED_NAME, \Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE];
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
    public function autowire(\Rector\PostRector\Collector\NodesToRemoveCollector $nodesToRemoveCollector, \Rector\PostRector\Collector\NodesToAddCollector $nodesToAddCollector, \Rector\NodeRemoval\NodeRemover $nodeRemover, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \RectorPrefix20220531\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\Core\Exclusion\ExclusionManager $exclusionManager, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\Core\Logging\CurrentRectorProvider $currentRectorProvider, \Rector\Core\Configuration\CurrentNodeProvider $currentNodeProvider, \RectorPrefix20220531\Symplify\Skipper\Skipper\Skipper $skipper, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Core\Provider\CurrentFileProvider $currentFileProvider, \Rector\Core\ProcessAnalyzer\RectifiedAnalyzer $rectifiedAnalyzer, \Rector\Core\NodeDecorator\CreatedByRuleDecorator $createdByRuleDecorator, \Rector\Core\Application\ChangedNodeScopeRefresher $changedNodeScopeRefresher, \Rector\Core\Console\Output\RectorOutputStyle $rectorOutputStyle) : void
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
        if (!$file instanceof \Rector\Core\ValueObject\Application\File) {
            throw new \Rector\Core\Exception\ShouldNotHappenException('File object is missing. Make sure you call $this->currentFileProvider->setFile(...) before traversing.');
        }
        $this->file = $file;
        return parent::beforeTraverse($nodes);
    }
    /**
     * @return Node|null
     */
    public final function enterNode(\PhpParser\Node $node)
    {
        $nodeClass = \get_class($node);
        if (!$this->isMatchingNodeType($nodeClass)) {
            return null;
        }
        if ($this->shouldSkipCurrentNode($node)) {
            return null;
        }
        /** @var Node $originalNode */
        $originalNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE) ?? clone $node;
        $createdByRule = $originalNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CREATED_BY_RULE) ?? [];
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
            throw new \Rector\Core\Exception\ShouldNotHappenException($errorMessage);
        }
        /** @var Node[]|Node $node */
        $this->createdByRuleDecorator->decorate($node, $originalNode, static::class);
        /** @var Node $originalNode */
        $rectorWithLineChange = new \Rector\ChangesReporting\ValueObject\RectorWithLineChange(\get_class($this), $originalNode->getLine());
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
        $currentScope = $originalNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $this->changedNodeScopeRefresher->refresh($node, $this->file->getSmartFileInfo(), $currentScope);
        $this->connectParentNodes($node);
        // is equals node type? return node early
        if (\get_class($originalNode) === \get_class($node)) {
            return $node;
        }
        // search "infinite recursion" in https://github.com/nikic/PHP-Parser/blob/master/doc/component/Walking_the_AST.markdown
        $originalNodeHash = \spl_object_hash($originalNode);
        if ($originalNode instanceof \PhpParser\Node\Stmt && $node instanceof \PhpParser\Node\Expr) {
            $node = new \PhpParser\Node\Stmt\Expression($node);
        }
        $this->nodesToReturn[$originalNodeHash] = $node;
        return $node;
    }
    /**
     * Replacing nodes in leaveNode() method avoids infinite recursion
     * see"infinite recursion" in https://github.com/nikic/PHP-Parser/blob/master/doc/component/Walking_the_AST.markdown
     */
    public function leaveNode(\PhpParser\Node $node)
    {
        $objectHash = \spl_object_hash($node);
        // update parents relations!!!
        return $this->nodesToReturn[$objectHash] ?? $node;
    }
    protected function isName(\PhpParser\Node $node, string $name) : bool
    {
        return $this->nodeNameResolver->isName($node, $name);
    }
    /**
     * @param string[] $names
     */
    protected function isNames(\PhpParser\Node $node, array $names) : bool
    {
        return $this->nodeNameResolver->isNames($node, $names);
    }
    protected function getName(\PhpParser\Node $node) : ?string
    {
        return $this->nodeNameResolver->getName($node);
    }
    protected function isObjectType(\PhpParser\Node $node, \PHPStan\Type\ObjectType $objectType) : bool
    {
        return $this->nodeTypeResolver->isObjectType($node, $objectType);
    }
    /**
     * Use this method for getting expr|node type
     */
    protected function getType(\PhpParser\Node $node) : \PHPStan\Type\Type
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
    protected function mirrorComments(\PhpParser\Node $newNode, \PhpParser\Node $oldNode) : void
    {
        $newNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO, $oldNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO));
        if (!$newNode instanceof \PhpParser\Node\Stmt\Nop) {
            $newNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS, $oldNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS));
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
            $currentArgs[] = new \PhpParser\Node\Arg($appendingArg->value);
        }
        return $currentArgs;
    }
    protected function removeNode(\PhpParser\Node $node) : void
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
    private function shouldSkipCurrentNode(\PhpParser\Node $node) : bool
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
        return $rectifiedNode instanceof \Rector\Core\ValueObject\RectifiedNode;
    }
    /**
     * @param array<string, mixed> $originalAttributes
     */
    private function mirrorAttributes(array $originalAttributes, \PhpParser\Node $newNode) : void
    {
        foreach ($originalAttributes as $attributeName => $oldAttributeValue) {
            if (!\in_array($attributeName, self::ATTRIBUTES_TO_MIRROR, \true)) {
                continue;
            }
            $newNode->setAttribute($attributeName, $oldAttributeValue);
        }
    }
    private function connectParentNodes(\PhpParser\Node $node) : void
    {
        $nodeTraverser = new \PhpParser\NodeTraverser();
        $nodeTraverser->addVisitor(new \PhpParser\NodeVisitor\ParentConnectingVisitor());
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
