<?php

declare(strict_types=1);

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
use Rector\Core\Configuration\Option;
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
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\Validation\InfiniteLoopValidator;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\ProjectType;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeRemoval\NodeRemover;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PostRector\Collector\NodesToAddCollector;
use Rector\PostRector\Collector\NodesToRemoveCollector;
use Rector\PostRector\DependencyInjection\PropertyAdder;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Service\Attribute\Required;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\Skipper\Skipper\Skipper;

/**
 * @see \Rector\Testing\PHPUnit\AbstractRectorTestCase
 */
abstract class AbstractRector extends NodeVisitorAbstract implements PhpRectorInterface
{
    /**
     * @var string[]
     */
    private const ATTRIBUTES_TO_MIRROR = [
        AttributeKey::CLASS_NODE,
        AttributeKey::CLASS_NAME,
        AttributeKey::METHOD_NODE,
        AttributeKey::USE_NODES,
        AttributeKey::SCOPE,
        AttributeKey::RESOLVED_NAME,
        AttributeKey::PARENT_NODE,
        AttributeKey::CURRENT_STATEMENT,
        AttributeKey::PREVIOUS_STATEMENT,
    ];

    protected NodeNameResolver $nodeNameResolver;

    protected NodeTypeResolver $nodeTypeResolver;

    protected BetterStandardPrinter $betterStandardPrinter;

    protected RemovedAndAddedFilesCollector $removedAndAddedFilesCollector;

    protected ParameterProvider $parameterProvider;

    protected PhpVersionProvider $phpVersionProvider;

    protected StaticTypeMapper $staticTypeMapper;

    protected PhpDocInfoFactory $phpDocInfoFactory;

    protected NodeFactory $nodeFactory;

    protected VisibilityManipulator $visibilityManipulator;

    protected ValueResolver $valueResolver;

    protected NodeRepository $nodeRepository;

    protected BetterNodeFinder $betterNodeFinder;

    protected NodeRemover $nodeRemover;

    protected RectorChangeCollector $rectorChangeCollector;

    protected NodeComparator $nodeComparator;

    protected PropertyAdder $propertyAdder;

    protected NodesToRemoveCollector $nodesToRemoveCollector;

    protected File $file;

    private SimpleCallableNodeTraverser $simpleCallableNodeTraverser;

    private SymfonyStyle $symfonyStyle;

    private ExclusionManager $exclusionManager;

    private CurrentRectorProvider $currentRectorProvider;

    private CurrentNodeProvider $currentNodeProvider;

    private Skipper $skipper;

    /**
     * @var string|null
     */
    private $previousAppliedClass;

    private NodesToAddCollector $nodesToAddCollector;

    private CurrentFileProvider $currentFileProvider;

    private ChangedNodeAnalyzer $changedNodeAnalyzer;

    /**
     * @var array<string, Node[]|Node>
     */
    private array $nodesToReturn = [];

    private InfiniteLoopValidator $infiniteLoopValidator;

    #[Required]
    public function autowireAbstractRector(
        NodesToRemoveCollector $nodesToRemoveCollector,
        NodesToAddCollector $nodesToAddCollector,
        RectorChangeCollector $rectorChangeCollector,
        NodeRemover $nodeRemover,
        PropertyAdder $propertyAdder,
        RemovedAndAddedFilesCollector $removedAndAddedFilesCollector,
        BetterStandardPrinter $betterStandardPrinter,
        NodeNameResolver $nodeNameResolver,
        NodeTypeResolver $nodeTypeResolver,
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        VisibilityManipulator $visibilityManipulator,
        NodeFactory $nodeFactory,
        PhpDocInfoFactory $phpDocInfoFactory,
        SymfonyStyle $symfonyStyle,
        PhpVersionProvider $phpVersionProvider,
        ExclusionManager $exclusionManager,
        StaticTypeMapper $staticTypeMapper,
        ParameterProvider $parameterProvider,
        CurrentRectorProvider $currentRectorProvider,
        CurrentNodeProvider $currentNodeProvider,
        Skipper $skipper,
        ValueResolver $valueResolver,
        NodeRepository $nodeRepository,
        BetterNodeFinder $betterNodeFinder,
        NodeComparator $nodeComparator,
        CurrentFileProvider $currentFileProvider,
        ChangedNodeAnalyzer $changedNodeAnalyzer,
        InfiniteLoopValidator $infiniteLoopValidator
    ): void {
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->nodesToAddCollector = $nodesToAddCollector;
        $this->rectorChangeCollector = $rectorChangeCollector;
        $this->nodeRemover = $nodeRemover;
        $this->propertyAdder = $propertyAdder;
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
        $this->nodeRepository = $nodeRepository;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
        $this->currentFileProvider = $currentFileProvider;
        $this->changedNodeAnalyzer = $changedNodeAnalyzer;
        $this->infiniteLoopValidator = $infiniteLoopValidator;
    }

    /**
     * @return Node[]|null
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->previousAppliedClass = null;

        // workaround for file around refactor()
        $file = $this->currentFileProvider->getFile();
        if (! $file instanceof File) {
            throw new ShouldNotHappenException('File is missing');
        }

        $this->file = $file;

        return parent::beforeTraverse($nodes);
    }

    /**
     * @return Expression|Node|Node[]|int|null
     */
    final public function enterNode(Node $node)
    {
        $nodeClass = get_class($node);
        if (! $this->isMatchingNodeType($nodeClass)) {
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
        $originalNode = $node->getAttribute(AttributeKey::ORIGINAL_NODE) ?? clone $node;

        $node = $this->refactor($node);

        if (is_array($node)) {
            $originalNodeHash = spl_object_hash($originalNode);
            $this->nodesToReturn[$originalNodeHash] = $node;

            if ($node !== []) {
                $firstNodeKey = array_key_first($node);
                $this->mirrorComments($node[$firstNodeKey], $originalNode);
            }

            // will be replaced in leaveNode() the original node must be passed
            return $originalNode;
        }

        // nothing to change → continue
        if (! $node instanceof Node) {
            return null;
        }

        // changed!
        if ($this->changedNodeAnalyzer->hasNodeChanged($originalNode, $node)) {
            $rectorWithLineChange = new RectorWithLineChange($this, $originalNode->getLine());
            $this->file->addRectorClassWithLine($rectorWithLineChange);

            // update parents relations - must run before connectParentNodes()
            $this->mirrorAttributes($originalAttributes, $node);
            $this->connectParentNodes($node);

            // is different node type? do not traverse children to avoid looping
            if (get_class($originalNode) !== get_class($node)) {
                $this->infiniteLoopValidator->process($node, $originalNode, static::class);

                // search "infinite recursion" in https://github.com/nikic/PHP-Parser/blob/master/doc/component/Walking_the_AST.markdown
                $originalNodeHash = spl_object_hash($originalNode);

                if ($originalNode instanceof Stmt && $node instanceof Expr) {
                    $node = new Expression($node);
                }

                $this->nodesToReturn[$originalNodeHash] = $node;

                return $node;
            }
        }

        // if Stmt ("$value;") was replaced by Expr ("$value"), add Expression (the ending ";") to prevent breaking the code
        if ($originalNode instanceof Stmt && $node instanceof Expr) {
            $node = new Expression($node);
        }

        return $node;
    }

    /**
     * Replacing nodes in leaveNode() method avoids infinite recursion
     * see"infinite recursion" in https://github.com/nikic/PHP-Parser/blob/master/doc/component/Walking_the_AST.markdown
     */
    public function leaveNode(Node $node)
    {
        $objectHash = spl_object_hash($node);

        // update parents relations!!!
        return $this->nodesToReturn[$objectHash] ?? $node;
    }

    protected function isName(Node $node, string $name): bool
    {
        return $this->nodeNameResolver->isName($node, $name);
    }

    /**
     * @param string[] $names
     */
    protected function isNames(Node $node, array $names): bool
    {
        return $this->nodeNameResolver->isNames($node, $names);
    }

    protected function getName(Node $node): ?string
    {
        return $this->nodeNameResolver->getName($node);
    }

    protected function isObjectType(Node $node, ObjectType $objectType): bool
    {
        return $this->nodeTypeResolver->isObjectType($node, $objectType);
    }

    protected function getStaticType(Node $node): Type
    {
        return $this->nodeTypeResolver->getStaticType($node);
    }

    /**
     * @deprecated
     * Use getStaticType() instead, as single method to get types
     */
    protected function getObjectType(Node $node): Type
    {
        return $this->nodeTypeResolver->resolve($node);
    }

    /**
     * @param Node|Node[] $nodes
     */
    protected function traverseNodesWithCallable($nodes, callable $callable): void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($nodes, $callable);
    }

    /**
     * @param Node|Node[]|null $node
     */
    protected function print($node): string
    {
        return $this->betterStandardPrinter->print($node);
    }

    protected function isAtLeastPhpVersion(int $version): bool
    {
        return $this->phpVersionProvider->isAtLeastPhpVersion($version);
    }

    protected function mirrorComments(Node $newNode, Node $oldNode): void
    {
        $newNode->setAttribute(AttributeKey::PHP_DOC_INFO, $oldNode->getAttribute(AttributeKey::PHP_DOC_INFO));
        $newNode->setAttribute(AttributeKey::COMMENTS, $oldNode->getAttribute(AttributeKey::COMMENTS));
    }

    /**
     * @deprecated Return array of stmts directly
     * @param Stmt[] $stmts
     */
    protected function unwrapStmts(array $stmts, Node $node): void
    {
        // move /* */ doc block from if to first element to keep it
        $currentPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        foreach ($stmts as $key => $ifStmt) {
            if ($key === 0) {
                $ifStmt->setAttribute(AttributeKey::PHP_DOC_INFO, $currentPhpDocInfo);

                // move // comments
                $ifStmt->setAttribute(AttributeKey::COMMENTS, $node->getComments());
            }

            $this->addNodeAfterNode($ifStmt, $node);
        }
    }

    protected function isOpenSourceProjectType(): bool
    {
        $projectType = $this->parameterProvider->provideStringParameter(Option::PROJECT_TYPE);
        return $projectType === ProjectType::OPEN_SOURCE;
    }

    /**
     * @param Arg[] $currentArgs
     * @param Arg[] $appendingArgs
     * @return Arg[]
     */
    protected function appendArgs(array $currentArgs, array $appendingArgs): array
    {
        foreach ($appendingArgs as $appendingArg) {
            $currentArgs[] = new Arg($appendingArg->value);
        }

        return $currentArgs;
    }

    protected function unwrapExpression(Stmt $stmt): Node
    {
        if ($stmt instanceof Expression) {
            return $stmt->expr;
        }

        return $stmt;
    }

    /**
     * @deprecated Use refactor() return of [] or directly $nodesToAddCollector
     * @param Node[] $newNodes
     */
    protected function addNodesAfterNode(array $newNodes, Node $positionNode): void
    {
        $this->nodesToAddCollector->addNodesAfterNode($newNodes, $positionNode);
    }

    /**
     * @param Node[] $newNodes
     * @deprecated Use refactor() return of [] or directly $nodesToAddCollector
     */
    protected function addNodesBeforeNode(array $newNodes, Node $positionNode): void
    {
        $this->nodesToAddCollector->addNodesBeforeNode($newNodes, $positionNode);
    }

    /**
     * @deprecated Use refactor() return of [] or directly $nodesToAddCollector
     */
    protected function addNodeAfterNode(Node $newNode, Node $positionNode): void
    {
        $this->nodesToAddCollector->addNodeAfterNode($newNode, $positionNode);
    }

    /**
     * @deprecated Use refactor() return of [] or directly $nodesToAddCollector
     */
    protected function addNodeBeforeNode(Node $newNode, Node $positionNode): void
    {
        $this->nodesToAddCollector->addNodeBeforeNode($newNode, $positionNode);
    }

    protected function addConstructorDependencyToClass(
        Class_ $class,
        Type $propertyType,
        string $propertyName,
        int $propertyFlags = 0
    ): void {
        $this->propertyAdder->addConstructorDependencyToClass($class, $propertyType, $propertyName, $propertyFlags);
    }

    protected function removeNode(Node $node): void
    {
        $this->nodeRemover->removeNode($node);
    }

    protected function removeNodeFromStatements(
        Class_ | ClassMethod | Function_ $nodeWithStatements,
        Node $toBeRemovedNode
    ): void {
        $this->nodeRemover->removeNodeFromStatements($nodeWithStatements, $toBeRemovedNode);
    }

    /**
     * @param Node[] $nodes
     */
    protected function removeNodes(array $nodes): void
    {
        $this->nodeRemover->removeNodes($nodes);
    }

    /**
     * @param class-string<Node> $nodeClass
     */
    private function isMatchingNodeType(string $nodeClass): bool
    {
        foreach ($this->getNodeTypes() as $nodeType) {
            if (is_a($nodeClass, $nodeType, true)) {
                return true;
            }
        }

        return false;
    }

    private function shouldSkipCurrentNode(Node $node): bool
    {
        if ($this->nodesToRemoveCollector->isNodeRemoved($node)) {
            return true;
        }

        if ($this->exclusionManager->isNodeSkippedByRector($node, $this)) {
            return true;
        }

        $smartFileInfo = $this->file->getSmartFileInfo();
        return $this->skipper->shouldSkipElementAndFileInfo($this, $smartFileInfo);
    }

    private function printDebugApplying(): void
    {
        if (! $this->symfonyStyle->isDebug()) {
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
    private function mirrorAttributes(array $originalAttributes, Node $newNode): void
    {
        if ($newNode instanceof Name) {
            $newNode->setAttribute(AttributeKey::RESOLVED_NAME, $newNode->toString());
        }

        foreach ($originalAttributes as $attributeName => $oldAttributeValue) {
            if (! in_array($attributeName, self::ATTRIBUTES_TO_MIRROR, true)) {
                continue;
            }

            $newNode->setAttribute($attributeName, $oldAttributeValue);
        }
    }

    /**
     * @param Node|Node[] $node
     */
    private function connectParentNodes($node): void
    {
        $nodes = is_array($node) ? $node : [$node];

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new ParentConnectingVisitor());
        $nodeTraverser->traverse($nodes);
    }
}
