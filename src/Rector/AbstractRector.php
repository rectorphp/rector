<?php

declare (strict_types=1);
namespace Rector\Core\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Exclusion\ExclusionManager;
use Rector\Core\Logging\CurrentRectorProvider;
use Rector\Core\NodeAnalyzer\ChangedNodeAnalyzer;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Core\ProcessAnalyzer\RectifiedAnalyzer;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\Validation\InfiniteLoopValidator;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\RectifiedNode;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeRemoval\NodeRemover;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PostRector\Collector\NodesToAddCollector;
use Rector\PostRector\Collector\NodesToRemoveCollector;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix20211020\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20211020\Symfony\Contracts\Service\Attribute\Required;
use RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
use RectorPrefix20211020\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20211020\Symplify\Skipper\Skipper\Skipper;
/**
 * @see \Rector\Testing\PHPUnit\AbstractRectorTestCase
 */
abstract class AbstractRector extends \PhpParser\NodeVisitorAbstract implements \Rector\Core\Contract\Rector\PhpRectorInterface
{
    /**
     * @var string[]
     */
    private const ATTRIBUTES_TO_MIRROR = [\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE, \Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME, \Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NODE, \Rector\NodeTypeResolver\Node\AttributeKey::USE_NODES, \Rector\NodeTypeResolver\Node\AttributeKey::SCOPE, \Rector\NodeTypeResolver\Node\AttributeKey::RESOLVED_NAME, \Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE, \Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT, \Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_STATEMENT];
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    protected $nodeNameResolver;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    protected $nodeTypeResolver;
    /**
     * @var \Rector\Core\PhpParser\Printer\BetterStandardPrinter
     */
    protected $betterStandardPrinter;
    /**
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector
     */
    protected $removedAndAddedFilesCollector;
    /**
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    protected $parameterProvider;
    /**
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    protected $phpVersionProvider;
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
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    protected $visibilityManipulator;
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
     * @var \Rector\ChangesReporting\Collector\RectorChangeCollector
     */
    protected $rectorChangeCollector;
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
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
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
     * @var string|null
     */
    private $previousAppliedClass;
    /**
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @var \Rector\Core\NodeAnalyzer\ChangedNodeAnalyzer
     */
    private $changedNodeAnalyzer;
    /**
     * @var array<string, Node[]|Node>
     */
    private $nodesToReturn = [];
    /**
     * @var \Rector\Core\Validation\InfiniteLoopValidator
     */
    private $infiniteLoopValidator;
    /**
     * @var \Rector\Core\ProcessAnalyzer\RectifiedAnalyzer
     */
    private $rectifiedAnalyzer;
    /**
     * @required
     */
    public function autowireAbstractRector(\Rector\PostRector\Collector\NodesToRemoveCollector $nodesToRemoveCollector, \Rector\PostRector\Collector\NodesToAddCollector $nodesToAddCollector, \Rector\ChangesReporting\Collector\RectorChangeCollector $rectorChangeCollector, \Rector\NodeRemoval\NodeRemover $nodeRemover, \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector $removedAndAddedFilesCollector, \Rector\Core\PhpParser\Printer\BetterStandardPrinter $betterStandardPrinter, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\Privatization\NodeManipulator\VisibilityManipulator $visibilityManipulator, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \RectorPrefix20211020\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider, \Rector\Core\Exclusion\ExclusionManager $exclusionManager, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \RectorPrefix20211020\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider, \Rector\Core\Logging\CurrentRectorProvider $currentRectorProvider, \Rector\Core\Configuration\CurrentNodeProvider $currentNodeProvider, \RectorPrefix20211020\Symplify\Skipper\Skipper\Skipper $skipper, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Core\Provider\CurrentFileProvider $currentFileProvider, \Rector\Core\NodeAnalyzer\ChangedNodeAnalyzer $changedNodeAnalyzer, \Rector\Core\Validation\InfiniteLoopValidator $infiniteLoopValidator, \Rector\Core\ProcessAnalyzer\RectifiedAnalyzer $rectifiedAnalyzer) : void
    {
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->nodesToAddCollector = $nodesToAddCollector;
        $this->rectorChangeCollector = $rectorChangeCollector;
        $this->nodeRemover = $nodeRemover;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->visibilityManipulator = $visibilityManipulator;
        $this->nodeFactory = $nodeFactory;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->symfonyStyle = $symfonyStyle;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->exclusionManager = $exclusionManager;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->parameterProvider = $parameterProvider;
        $this->currentRectorProvider = $currentRectorProvider;
        $this->currentNodeProvider = $currentNodeProvider;
        $this->skipper = $skipper;
        $this->valueResolver = $valueResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
        $this->currentFileProvider = $currentFileProvider;
        $this->changedNodeAnalyzer = $changedNodeAnalyzer;
        $this->infiniteLoopValidator = $infiniteLoopValidator;
        $this->rectifiedAnalyzer = $rectifiedAnalyzer;
    }
    /**
     * @return Node[]|null
     */
    public function beforeTraverse(array $nodes) : ?array
    {
        $this->previousAppliedClass = null;
        // workaround for file around refactor()
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof \Rector\Core\ValueObject\Application\File) {
            throw new \Rector\Core\Exception\ShouldNotHappenException('File is missing');
        }
        $this->file = $file;
        return parent::beforeTraverse($nodes);
    }
    /**
     * @return Expression|Node|Node[]|int|null
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
        $this->currentRectorProvider->changeCurrentRector($this);
        // for PHP doc info factory and change notifier
        $this->currentNodeProvider->setNode($node);
        // show current Rector class on --debug
        $this->printDebugApplying();
        $originalAttributes = $node->getAttributes();
        $originalNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE) ?? clone $node;
        $node = $this->refactor($node);
        if (\is_array($node)) {
            $originalNodeHash = \spl_object_hash($originalNode);
            $this->nodesToReturn[$originalNodeHash] = $node;
            if ($node !== []) {
                \reset($node);
                $firstNodeKey = \key($node);
                $this->mirrorComments($node[$firstNodeKey], $originalNode);
            }
            // will be replaced in leaveNode() the original node must be passed
            return $originalNode;
        }
        // nothing to change → continue
        if (!$node instanceof \PhpParser\Node) {
            return null;
        }
        // changed!
        if ($this->changedNodeAnalyzer->hasNodeChanged($originalNode, $node)) {
            $rectorWithLineChange = new \Rector\ChangesReporting\ValueObject\RectorWithLineChange($this, $originalNode->getLine());
            $this->file->addRectorClassWithLine($rectorWithLineChange);
            // update parents relations - must run before connectParentNodes()
            $this->mirrorAttributes($originalAttributes, $node);
            $this->connectParentNodes($node);
            // is different node type? do not traverse children to avoid looping
            if (\get_class($originalNode) !== \get_class($node)) {
                $this->infiniteLoopValidator->process($node, $originalNode, static::class);
                // search "infinite recursion" in https://github.com/nikic/PHP-Parser/blob/master/doc/component/Walking_the_AST.markdown
                $originalNodeHash = \spl_object_hash($originalNode);
                if ($originalNode instanceof \PhpParser\Node\Stmt && $node instanceof \PhpParser\Node\Expr) {
                    $node = new \PhpParser\Node\Stmt\Expression($node);
                }
                $this->nodesToReturn[$originalNodeHash] = $node;
                return $node;
            }
        }
        // if Stmt ("$value;") was replaced by Expr ("$value"), add Expression (the ending ";") to prevent breaking the code
        if ($originalNode instanceof \PhpParser\Node\Stmt && $node instanceof \PhpParser\Node\Expr) {
            $node = new \PhpParser\Node\Stmt\Expression($node);
        }
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
     * @deprecated
     * Use @see AbstractRector::getType() instead, as single method to get types
     */
    protected function getObjectType(\PhpParser\Node $node) : \PHPStan\Type\Type
    {
        return $this->nodeTypeResolver->getType($node);
    }
    /**
     * @deprecated
     * Use @see AbstractRector::getType() instead, as single method to get types
     */
    protected function getStaticType(\PhpParser\Node $node) : \PHPStan\Type\Type
    {
        return $this->nodeTypeResolver->getType($node);
    }
    /**
     * @param Node|Node[] $nodes
     */
    protected function traverseNodesWithCallable($nodes, callable $callable) : void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($nodes, $callable);
    }
    /**
     * @param Node|Node[]|null $node
     */
    protected function print($node) : string
    {
        return $this->betterStandardPrinter->print($node);
    }
    protected function mirrorComments(\PhpParser\Node $newNode, \PhpParser\Node $oldNode) : void
    {
        $newNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO, $oldNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO));
        $newNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS, $oldNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS));
    }
    /**
     * @deprecated Return array of stmts directly
     * @param Stmt[] $stmts
     */
    protected function unwrapStmts(array $stmts, \PhpParser\Node $node) : void
    {
        // move /* */ doc block from if to first element to keep it
        $currentPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        foreach ($stmts as $key => $ifStmt) {
            if ($key === 0) {
                $ifStmt->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO, $currentPhpDocInfo);
                // move // comments
                $ifStmt->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS, $node->getComments());
            }
            $this->nodesToAddCollector->addNodeAfterNode($ifStmt, $node);
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
    /**
     * @return \PhpParser\Node\Expr|\PhpParser\Node\Stmt
     */
    protected function unwrapExpression(\PhpParser\Node\Stmt $stmt)
    {
        if ($stmt instanceof \PhpParser\Node\Stmt\Expression) {
            return $stmt->expr;
        }
        return $stmt;
    }
    /**
     * @deprecated Use refactor() return of [] or directly $nodesToAddCollector
     * @param Node[] $newNodes
     */
    protected function addNodesAfterNode(array $newNodes, \PhpParser\Node $positionNode) : void
    {
        $this->nodesToAddCollector->addNodesAfterNode($newNodes, $positionNode);
    }
    /**
     * @param Node[] $newNodes
     * @deprecated Use refactor() return of [] or directly $nodesToAddCollector
     */
    protected function addNodesBeforeNode(array $newNodes, \PhpParser\Node $positionNode) : void
    {
        $this->nodesToAddCollector->addNodesBeforeNode($newNodes, $positionNode);
    }
    /**
     * @deprecated Use refactor() return of [] or directly $nodesToAddCollector
     */
    protected function addNodeAfterNode(\PhpParser\Node $newNode, \PhpParser\Node $positionNode) : void
    {
        $this->nodesToAddCollector->addNodeAfterNode($newNode, $positionNode);
    }
    /**
     * @deprecated Use refactor() return of [] or directly $nodesToAddCollector
     */
    protected function addNodeBeforeNode(\PhpParser\Node $newNode, \PhpParser\Node $positionNode) : void
    {
        $this->nodesToAddCollector->addNodeBeforeNode($newNode, $positionNode);
    }
    protected function removeNode(\PhpParser\Node $node) : void
    {
        $this->nodeRemover->removeNode($node);
    }
    /**
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $nodeWithStatements
     */
    protected function removeNodeFromStatements($nodeWithStatements, \PhpParser\Node $toBeRemovedNode) : void
    {
        $this->nodeRemover->removeNodeFromStatements($nodeWithStatements, $toBeRemovedNode);
    }
    /**
     * @param Node[] $nodes
     */
    protected function removeNodes(array $nodes) : void
    {
        $this->nodeRemover->removeNodes($nodes);
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
    private function printDebugApplying() : void
    {
        if (!$this->symfonyStyle->isDebug()) {
            return;
        }
        if ($this->previousAppliedClass === static::class) {
            return;
        }
        // prevent spamming with the same class over and over
        // indented on purpose to improve log nesting under [refactoring]
        $this->symfonyStyle->writeln('    [applying] ' . static::class);
        $this->previousAppliedClass = static::class;
    }
    /**
     * @param array<string, mixed> $originalAttributes
     */
    private function mirrorAttributes(array $originalAttributes, \PhpParser\Node $newNode) : void
    {
        if ($newNode instanceof \PhpParser\Node\Name) {
            $newNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::RESOLVED_NAME, $newNode->toString());
        }
        foreach ($originalAttributes as $attributeName => $oldAttributeValue) {
            if (!\in_array($attributeName, self::ATTRIBUTES_TO_MIRROR, \true)) {
                continue;
            }
            $newNode->setAttribute($attributeName, $oldAttributeValue);
        }
    }
    /**
     * @param Node|Node[] $node
     */
    private function connectParentNodes($node) : void
    {
        $nodes = \is_array($node) ? $node : [$node];
        $nodeTraverser = new \PhpParser\NodeTraverser();
        $nodeTraverser->addVisitor(new \PhpParser\NodeVisitor\ParentConnectingVisitor());
        $nodeTraverser->traverse($nodes);
    }
}
