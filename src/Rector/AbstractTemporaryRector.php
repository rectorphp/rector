<?php

declare(strict_types=1);

namespace Rector\Core\Rector;

use PhpParser\Comment;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Exclusion\ExclusionManager;
use Rector\Core\Logging\CurrentRectorProvider;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Core\ValueObject\ProjectType;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeRemoval\NodeRemover;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PostRector\Collector\NodesToAddCollector;
use Rector\PostRector\Collector\NodesToRemoveCollector;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\PostRector\DependencyInjection\PropertyAdder;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\Skipper\Skipper\Skipper;
use Symplify\SmartFileSystem\SmartFileInfo;

abstract class AbstractTemporaryRector extends NodeVisitorAbstract implements PhpRectorInterface
{
    /**
     * @var string[]
     */
    private const ATTRIBUTES_TO_MIRROR = [
        AttributeKey::PARENT_NODE,
        AttributeKey::CLASS_NODE,
        AttributeKey::CLASS_NAME,
        AttributeKey::FILE_INFO,
        AttributeKey::METHOD_NODE,
        AttributeKey::USE_NODES,
        AttributeKey::SCOPE,
        AttributeKey::METHOD_NAME,
        AttributeKey::NAMESPACE_NAME,
        AttributeKey::NAMESPACE_NODE,
        AttributeKey::RESOLVED_NAME,
    ];

    /**
     * @var NodeNameResolver
     */
    protected $nodeNameResolver;

    /**
     * @var NodeTypeResolver
     */
    protected $nodeTypeResolver;

    /**
     * @var BetterStandardPrinter
     */
    protected $betterStandardPrinter;

    /**
     * @var RemovedAndAddedFilesCollector
     */
    protected $removedAndAddedFilesCollector;

    /**
     * @var ParameterProvider
     */
    protected $parameterProvider;

    /**
     * @var PhpVersionProvider
     */
    protected $phpVersionProvider;

    /**
     * @var StaticTypeMapper
     */
    protected $staticTypeMapper;

    /**
     * @var PhpDocInfoFactory
     */
    protected $phpDocInfoFactory;

    /**
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @var VisibilityManipulator
     */
    protected $visibilityManipulator;

    /**
     * @var ValueResolver
     */
    protected $valueResolver;

    /**
     * @var NodeRepository
     */
    protected $nodeRepository;

    /**
     * @var BetterNodeFinder
     */
    protected $betterNodeFinder;

    /**
     * @var ClassAnalyzer
     */
    protected $classNodeAnalyzer;

    /**
     * @var UseNodesToAddCollector
     */
    protected $useNodesToAddCollector;

    /**
     * @var NodeRemover
     */
    protected $nodeRemover;

    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var ExclusionManager
     */
    private $exclusionManager;

    /**
     * @var CurrentRectorProvider
     */
    private $currentRectorProvider;

    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    /**
     * @var Skipper
     */
    private $skipper;

    /**
     * @var string|null
     */
    private $previousAppliedClass;

    /**
     * @var NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;

    /**
     * @var NodesToAddCollector
     */
    private $nodesToAddCollector;

    /**
     * @var PropertyToAddCollector
     */
    private $propertyToAddCollector;

    /**
     * @var RectorChangeCollector
     */
    private $rectorChangeCollector;

    /**
     * @var PropertyAdder
     */
    private $propertyAdder;

    /**
     * @required
     */
    public function autowireAbstractTemporaryRector(
        NodesToRemoveCollector $nodesToRemoveCollector,
        PropertyToAddCollector $propertyToAddCollector,
        UseNodesToAddCollector $useNodesToAddCollector,
        NodesToAddCollector $nodesToAddCollector,
        RectorChangeCollector $rectorChangeCollector,
        NodeRemover $nodeRemover,
        PropertyAdder $propertyAdder,
        RemovedAndAddedFilesCollector $removedAndAddedFilesCollector,
        BetterStandardPrinter $betterStandardPrinter,
        NodeNameResolver $nodeNameResolver,
        ClassNaming $classNaming,
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
        ClassAnalyzer $classAnalyzer,
        CurrentNodeProvider $currentNodeProvider,
        Skipper $skipper,
        ValueResolver $valueResolver,
        NodeRepository $nodeRepository,
        BetterNodeFinder $betterNodeFinder
    ): void {
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->propertyToAddCollector = $propertyToAddCollector;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
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
        $this->classNodeAnalyzer = $classAnalyzer;
        $this->currentNodeProvider = $currentNodeProvider;
        $this->skipper = $skipper;
        $this->valueResolver = $valueResolver;
        $this->nodeRepository = $nodeRepository;
        $this->betterNodeFinder = $betterNodeFinder;
    }

    /**
     * @return Node[]|null
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->previousAppliedClass = null;

        return parent::beforeTraverse($nodes);
    }

    /**
     * @return Expression|Node|null
     */
    final public function enterNode(Node $node)
    {
        $nodeClass = get_class($node);
        if (! $this->isMatchingNodeType($nodeClass)) {
            return null;
        }

        $this->currentRectorProvider->changeCurrentRector($this);
        // for PHP doc info factory and change notifier
        $this->currentNodeProvider->setNode($node);

        // already removed
        if ($this->shouldSkipCurrentNode($node)) {
            return null;
        }

        // show current Rector class on --debug
        $this->printDebugApplying();

        $originalNode = $node->getAttribute(AttributeKey::ORIGINAL_NODE) ?? clone $node;
        $originalNodeWithAttributes = clone $node;
        $node = $this->refactor($node);

        // nothing to change → continue
        if (! $node instanceof Node) {
            return null;
        }

        // changed!
        if ($this->hasNodeChanged($originalNode, $node)) {
            $this->mirrorAttributes($originalNodeWithAttributes, $node);
            $this->updateAttributes($node);
            $this->keepFileInfoAttribute($node, $originalNode);
            $this->rectorChangeCollector->notifyNodeFileInfo($node);
        }

        // if stmt ("$value;") was replaced by expr ("$value"), add the ending ";" (Expression) to prevent breaking the code
        if ($originalNode instanceof Stmt && $node instanceof Expr) {
            return new Expression($node);
        }

        return $node;
    }

    protected function isName(Node $node, string $name): bool
    {
        return $this->nodeNameResolver->isName($node, $name);
    }

    protected function areNamesEqual(Node $firstNode, Node $secondNode): bool
    {
        return $this->nodeNameResolver->areNamesEqual($firstNode, $secondNode);
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

    protected function isFuncCallName(Node $node, string $name): bool
    {
        return $this->nodeNameResolver->isFuncCallName($node, $name);
    }

    protected function isStaticCallNamed(Node $node, string $className, string $methodName): bool
    {
        return $this->nodeNameResolver->isStaticCallNamed($node, $className, $methodName);
    }

    protected function isVariableName(Node $node, string $name): bool
    {
        return $this->nodeNameResolver->isVariableName($node, $name);
    }

    /**
     * @param ObjectType|string $type
     */
    protected function isObjectType(Node $node, $type): bool
    {
        return $this->nodeTypeResolver->isObjectType($node, $type);
    }

    /**
     * @param string[]|ObjectType[] $requiredTypes
     */
    protected function isObjectTypes(Node $node, array $requiredTypes): bool
    {
        return $this->nodeTypeResolver->isObjectTypes($node, $requiredTypes);
    }

    protected function isNumberType(Node $node): bool
    {
        return $this->nodeTypeResolver->isNumberType($node);
    }

    protected function isStaticType(Node $node, string $staticTypeClass): bool
    {
        return $this->nodeTypeResolver->isStaticType($node, $staticTypeClass);
    }

    protected function getStaticType(Node $node): Type
    {
        return $this->nodeTypeResolver->getStaticType($node);
    }

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

    /**
     * Removes all comments from both nodes
     *
     * @param Node|Node[]|null $firstNode
     * @param Node|Node[]|null $secondNode
     */
    protected function areNodesEqual($firstNode, $secondNode): bool
    {
        return $this->betterStandardPrinter->areNodesEqual($firstNode, $secondNode);
    }

    protected function getNextExpression(Node $node): ?Node
    {
        $currentExpression = $node->getAttribute(AttributeKey::CURRENT_STATEMENT);
        if (! $currentExpression instanceof Expression) {
            return null;
        }

        return $currentExpression->getAttribute(AttributeKey::NEXT_NODE);
    }

    /**
     * @param Expr[]|null[] $nodes
     * @param mixed[] $expectedValues
     */
    protected function areValues(array $nodes, array $expectedValues): bool
    {
        return $this->valueResolver->areValues($nodes, $expectedValues);
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

    protected function rollbackComments(Node $node, Comment $comment): void
    {
        $node->setAttribute(AttributeKey::COMMENTS, null);
        $node->setDocComment(new Doc($comment->getText()));
        $node->setAttribute(AttributeKey::PHP_DOC_INFO, null);
    }

    /**
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

    protected function isOnClassMethodCall(Node $node, string $type, string $methodName): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $this->isObjectType($node->var, $type)) {
            return false;
        }

        return $this->isName($node->name, $methodName);
    }

    protected function isOpenSourceProjectType(): bool
    {
        $projectType = $this->parameterProvider->provideParameter(Option::PROJECT_TYPE);

        return in_array(
            $projectType,
            // make it typo proof
            [ProjectType::OPEN_SOURCE, ProjectType::OPEN_SOURCE_UNDESCORED],
            true
        );
    }

    /**
     * @param Arg[] $newArgs
     * @param Arg[] $appendingArgs
     * @return Arg[]
     */
    protected function appendArgs(array $newArgs, array $appendingArgs): array
    {
        foreach ($appendingArgs as $oldArgument) {
            $newArgs[] = new Arg($oldArgument->value);
        }

        return $newArgs;
    }

    protected function unwrapExpression(Stmt $stmt): Node
    {
        if ($stmt instanceof Expression) {
            return $stmt->expr;
        }

        return $stmt;
    }

    /**
     * @param Node[] $newNodes
     */
    protected function addNodesAfterNode(array $newNodes, Node $positionNode): void
    {
        $this->nodesToAddCollector->addNodesAfterNode($newNodes, $positionNode);
    }

    /**
     * @param Node[] $newNodes
     */
    protected function addNodesBeforeNode(array $newNodes, Node $positionNode): void
    {
        $this->nodesToAddCollector->addNodesBeforeNode($newNodes, $positionNode);
    }

    protected function addNodeAfterNode(Node $newNode, Node $positionNode): void
    {
        $this->nodesToAddCollector->addNodeAfterNode($newNode, $positionNode);
    }

    protected function addNodeBeforeNode(Node $newNode, Node $positionNode): void
    {
        $this->nodesToAddCollector->addNodeBeforeNode($newNode, $positionNode);
    }

    protected function addPropertyToCollector(Property $property): void
    {
        $this->propertyAdder->addPropertyToCollector($property);
    }

    protected function addServiceConstructorDependencyToClass(Class_ $class, string $className): void
    {
        $this->propertyAdder->addServiceConstructorDependencyToClass($class, $className);
    }

    protected function addConstructorDependencyToClass(
        Class_ $class,
        ?Type $propertyType,
        string $propertyName,
        int $propertyFlags = 0
    ): void {
        $this->propertyAdder->addConstructorDependencyToClass($class, $propertyType, $propertyName, $propertyFlags);
    }

    protected function addConstantToClass(Class_ $class, ClassConst $classConst): void
    {
        $this->propertyToAddCollector->addConstantToClass($class, $classConst);
    }

    protected function addPropertyToClass(Class_ $class, ?Type $propertyType, string $propertyName): void
    {
        $this->propertyToAddCollector->addPropertyWithoutConstructorToClass($propertyName, $propertyType, $class);
    }

    protected function removeNode(Node $node): void
    {
        $this->nodeRemover->removeNode($node);
    }

    /**
     * @param Class_|ClassMethod|Function_ $nodeWithStatements
     */
    protected function removeNodeFromStatements(Node $nodeWithStatements, Node $nodeToRemove): void
    {
        $this->nodeRemover->removeNodeFromStatements($nodeWithStatements, $nodeToRemove);
    }

    protected function isNodeRemoved(Node $node): bool
    {
        return $this->nodesToRemoveCollector->isNodeRemoved($node);
    }

    /**
     * @param Node[] $nodes
     */
    protected function removeNodes(array $nodes): void
    {
        $this->nodeRemover->removeNodes($nodes);
    }

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
        if ($this->isNodeRemoved($node)) {
            return true;
        }

        if ($this->exclusionManager->isNodeSkippedByRector($node, $this)) {
            return true;
        }

        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        if (! $fileInfo instanceof SmartFileInfo) {
            return false;
        }

        return $this->skipper->shouldSkipElementAndFileInfo($this, $fileInfo);
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

    private function hasNodeChanged(Node $originalNode, Node $node): bool
    {
        if ($this->isNameIdentical($node, $originalNode)) {
            return false;
        }

        return ! $this->areNodesEqual($originalNode, $node);
    }

    private function mirrorAttributes(Node $oldNode, Node $newNode): void
    {
        foreach ($oldNode->getAttributes() as $attributeName => $oldNodeAttributeValue) {
            if (! in_array($attributeName, self::ATTRIBUTES_TO_MIRROR, true)) {
                continue;
            }

            $newNode->setAttribute($attributeName, $oldNodeAttributeValue);
        }
    }

    private function updateAttributes(Node $node): void
    {
        // update Resolved name attribute if name is changed
        if ($node instanceof Name) {
            $node->setAttribute(AttributeKey::RESOLVED_NAME, $node->toString());
        }
    }

    private function keepFileInfoAttribute(Node $node, Node $originalNode): void
    {
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        if ($fileInfo instanceof SmartFileInfo) {
            return;
        }
        $fileInfo = $originalNode->getAttribute(AttributeKey::FILE_INFO);

        $originalParent = $originalNode->getAttribute(AttributeKey::PARENT_NODE);

        if ($fileInfo !== null) {
            $node->setAttribute(AttributeKey::FILE_INFO, $originalNode->getAttribute(AttributeKey::FILE_INFO));
        } elseif ($originalParent instanceof Node) {
            $node->setAttribute(AttributeKey::FILE_INFO, $originalParent->getAttribute(AttributeKey::FILE_INFO));
        }
    }

    private function isNameIdentical(Node $node, Node $originalNode): bool
    {
        if (! $originalNode instanceof Name) {
            return false;
        }

        // names are the same
        return $this->areNodesEqual($originalNode->getAttribute(AttributeKey::ORIGINAL_NAME), $node);
    }
}
