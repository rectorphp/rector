<?php

declare(strict_types=1);

namespace Rector\Core\Rector;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitorAbstract;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Exclusion\ExclusionManager;
use Rector\Core\Logging\CurrentRectorProvider;
use Rector\Core\NodeAnalyzer\ClassNodeAnalyzer;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector\AbstractRectorTrait;
use Rector\Core\Skip\Skipper;
use Rector\Core\ValueObject\ProjectType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\SmartFileInfo;

abstract class AbstractRector extends NodeVisitorAbstract implements PhpRectorInterface
{
    use AbstractRectorTrait;

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
     * @var string
     */
    private const COMMENTS = 'comments';

    /**
     * @var BuilderFactory
     */
    protected $builderFactory;

    /**
     * @var ParameterProvider
     */
    protected $parameterProvider;

    /**
     * @var PhpVersionProvider
     */
    protected $phpVersionProvider;

    /**
     * @var DocBlockManipulator
     */
    protected $docBlockManipulator;

    /**
     * @var StaticTypeMapper
     */
    protected $staticTypeMapper;

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
     * @var ClassNodeAnalyzer
     */
    private $classNodeAnalyzer;

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
     * @required
     */
    public function autowireAbstractRector(
        SymfonyStyle $symfonyStyle,
        PhpVersionProvider $phpVersionProvider,
        BuilderFactory $builderFactory,
        ExclusionManager $exclusionManager,
        DocBlockManipulator $docBlockManipulator,
        StaticTypeMapper $staticTypeMapper,
        ParameterProvider $parameterProvider,
        CurrentRectorProvider $currentRectorProvider,
        ClassNodeAnalyzer $classNodeAnalyzer,
        CurrentNodeProvider $currentNodeProvider,
        Skipper $skipper
    ): void {
        $this->symfonyStyle = $symfonyStyle;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->builderFactory = $builderFactory;
        $this->exclusionManager = $exclusionManager;
        $this->docBlockManipulator = $docBlockManipulator;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->parameterProvider = $parameterProvider;
        $this->currentRectorProvider = $currentRectorProvider;
        $this->classNodeAnalyzer = $classNodeAnalyzer;
        $this->currentNodeProvider = $currentNodeProvider;
        $this->skipper = $skipper;
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
     * @param string[] $types
     */
    public function hasParentTypes(Node $node, array $types): bool
    {
        foreach ($types as $type) {
            if (! is_a($type, Node::class, true)) {
                throw new ShouldNotHappenException(__METHOD__);
            }

            if ($this->hasParentType($node, $type)) {
                return true;
            }
        }

        return false;
    }

    public function hasParentType(Node $node, string $type): bool
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent === null) {
            return false;
        }

        return is_a($parent, $type, true);
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
        // mostly for PHP doc and change notifications
        $this->currentNodeProvider->setNode($node);

        // already removed
        if ($this->isNodeRemoved($node)) {
            return null;
        }

        if ($this->exclusionManager->isNodeSkippedByRector($this, $node)) {
            return null;
        }

        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        if ($fileInfo instanceof SmartFileInfo && $this->skipper->shouldSkipFileInfoAndRule($fileInfo, $this)) {
            return null;
        }

        // show current Rector class on --debug
        $this->printDebugApplying();

        $originalNode = $node->getAttribute(AttributeKey::ORIGINAL_NODE) ?? clone $node;
        $originalNodeWithAttributes = clone $node;
        $node = $this->refactor($node);

        // nothing to change → continue
        if ($node === null) {
            return null;
        }

        // changed!
        if ($this->hasNodeChanged($originalNode, $node)) {
            $this->mirrorAttributes($originalNodeWithAttributes, $node);
            $this->updateAttributes($node);
            $this->keepFileInfoAttribute($node, $originalNode);
            $this->notifyNodeFileInfo($node);
        }

        // if stmt ("$value;") was replaced by expr ("$value"), add the ending ";" (Expression) to prevent breaking the code
        if ($originalNode instanceof Stmt && $node instanceof Expr) {
            return new Expression($node);
        }

        return $node;
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
        foreach ($nodes as $i => $node) {
            if ($node !== null && $this->isValue($node, $expectedValues[$i])) {
                continue;
            }

            return false;
        }

        return true;
    }

    protected function isAtLeastPhpVersion(string $version): bool
    {
        return $this->phpVersionProvider->isAtLeastPhpVersion($version);
    }

    protected function isAnonymousClass(Node $node): bool
    {
        return $this->classNodeAnalyzer->isAnonymousClass($node);
    }

    protected function mirrorComments(Node $newNode, Node $oldNode): void
    {
        $newNode->setAttribute(AttributeKey::PHP_DOC_INFO, $oldNode->getAttribute(AttributeKey::PHP_DOC_INFO));
        $newNode->setAttribute(self::COMMENTS, $oldNode->getAttribute(self::COMMENTS));
    }

    /**
     * @param Stmt[] $stmts
     */
    protected function unwrapStmts(array $stmts, Node $node): void
    {
        foreach ($stmts as $key => $ifStmt) {
            if ($key === 0) {
                // move /* */ doc block from if to first element to keep it
                $currentPhpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
                $ifStmt->setAttribute(AttributeKey::PHP_DOC_INFO, $currentPhpDocInfo);

                // move // comments
                $ifStmt->setAttribute(self::COMMENTS, $node->getComments());
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
     * @param Expr $expr
     */
    protected function createBoolCast(?Node $parentNode, Node $expr): Bool_
    {
        if ($parentNode instanceof Return_ && $expr instanceof Assign) {
            $expr = $expr->expr;
        }

        return new Bool_($expr);
    }

    /**
     * @param string[] $allowedTypes
     */
    protected function isAllowedType(string $currentType, array $allowedTypes): bool
    {
        foreach ($allowedTypes as $allowedType) {
            if (is_a($currentType, $allowedType, true)) {
                return true;
            }

            if (fnmatch($allowedType, $currentType, FNM_NOESCAPE)) {
                return true;
            }
        }

        return false;
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

    private function isMatchingNodeType(string $nodeClass): bool
    {
        foreach ($this->getNodeTypes() as $nodeType) {
            if (is_a($nodeClass, $nodeType, true)) {
                return true;
            }
        }

        return false;
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

        if ($fileInfo !== null) {
            $node->setAttribute(AttributeKey::FILE_INFO, $originalNode->getAttribute(AttributeKey::FILE_INFO));
        } elseif ($originalNode->getAttribute(AttributeKey::PARENT_NODE) !== null) {
            /** @var Node $parentOriginalNode */
            $parentOriginalNode = $originalNode->getAttribute(AttributeKey::PARENT_NODE);
            $node->setAttribute(AttributeKey::FILE_INFO, $parentOriginalNode->getAttribute(AttributeKey::FILE_INFO));
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
