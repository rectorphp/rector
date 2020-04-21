<?php

declare(strict_types=1);

namespace Rector\Core\Rector;

use Nette\Utils\Strings;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Analyser\Scope;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Exclusion\ExclusionManager;
use Rector\Core\Logging\CurrentRectorProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector\AbstractRectorTrait;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\SmartFileInfo;

abstract class AbstractRector extends NodeVisitorAbstract implements PhpRectorInterface
{
    use AbstractRectorTrait;

    /**
     * @var BuilderFactory
     */
    protected $builderFactory;

    /**
     * @var ParameterProvider
     */
    protected $parameterProvider;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var PhpVersionProvider
     */
    protected $phpVersionProvider;

    /**
     * @var ExclusionManager
     */
    private $exclusionManager;

    /**
     * @var PhpDocInfoPrinter
     */
    protected $phpDocInfoPrinter;

    /**
     * @var DocBlockManipulator
     */
    protected $docBlockManipulator;

    /**
     * @var StaticTypeMapper
     */
    protected $staticTypeMapper;

    /**
     * @var CurrentRectorProvider
     */
    private $currentRectorProvider;

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
     * @required
     */
    public function autowireAbstractRectorDependencies(
        SymfonyStyle $symfonyStyle,
        PhpVersionProvider $phpVersionProvider,
        BuilderFactory $builderFactory,
        ExclusionManager $exclusionManager,
        PhpDocInfoPrinter $phpDocInfoPrinter,
        DocBlockManipulator $docBlockManipulator,
        StaticTypeMapper $staticTypeMapper,
        ParameterProvider $parameterProvider,
        CurrentRectorProvider $currentRectorProvider
    ): void {
        $this->symfonyStyle = $symfonyStyle;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->builderFactory = $builderFactory;
        $this->exclusionManager = $exclusionManager;
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
        $this->docBlockManipulator = $docBlockManipulator;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->parameterProvider = $parameterProvider;
        $this->currentRectorProvider = $currentRectorProvider;
    }

    /**
     * @return int|Node|null
     */
    final public function enterNode(Node $node)
    {
        if (! $this->isMatchingNodeType(get_class($node))) {
            return null;
        }

        $this->currentRectorProvider->changeCurrentRector($this);

        // show current Rector class on --debug
        if ($this->symfonyStyle->isDebug()) {
            // indented on purpose to improve log nesting under [refactoring]
            $this->symfonyStyle->writeln('    [applying] ' . static::class);
        }

        // already removed
        if ($this->isNodeRemoved($node)) {
            return null;
        }

        if ($this->exclusionManager->isNodeSkippedByRector($this, $node)) {
            return null;
        }

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
        return $this->phpVersionProvider->isAtLeast($version);
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

    private function keepFileInfoAttribute(Node $node, Node $originalNode): void
    {
        if ($node->getAttribute(AttributeKey::FILE_INFO) instanceof SmartFileInfo) {
            return;
        }

        if ($originalNode->getAttribute(AttributeKey::FILE_INFO) !== null) {
            $node->setAttribute(AttributeKey::FILE_INFO, $originalNode->getAttribute(AttributeKey::FILE_INFO));
        } elseif ($originalNode->getAttribute(AttributeKey::PARENT_NODE) !== null) {
            /** @var Node $parentOriginalNode */
            $parentOriginalNode = $originalNode->getAttribute(AttributeKey::PARENT_NODE);
            $node->setAttribute(AttributeKey::FILE_INFO, $parentOriginalNode->getAttribute(AttributeKey::FILE_INFO));
        }
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

    protected function isAnonymousClass(Node $node): bool
    {
        if (! $node instanceof Class_) {
            return false;
        }

        $className = $this->nodeNameResolver->getName($node);

        return $className === null || Strings::contains($className, 'AnonymousClass');
    }

    private function updateAttributes(Node $node): void
    {
        // update Resolved name attribute if name is changed
        if ($node instanceof Name) {
            $node->setAttribute(AttributeKey::RESOLVED_NAME, $node->toString());
        }
    }

    private function hasNodeChanged(Node $originalNode, Node $node): bool
    {
        if ($this->isNameIdentical($node, $originalNode)) {
            return false;
        }

        return ! $this->areNodesEqual($originalNode, $node);
    }

    protected function createCountedValueName(string $countedValueName, ?Scope $scope): string
    {
        if ($scope === null) {
            return $countedValueName;
        }

        // make sure variable name is unique
        if (! $scope->hasVariableType($countedValueName)->yes()) {
            return $countedValueName;
        }

        // we need to add number suffix until the variable is unique
        $i = 2;
        $countedValueNamePart = $countedValueName;
        while ($scope->hasVariableType($countedValueName)->yes()) {
            $countedValueName = $countedValueNamePart . $i;
            ++$i;
        }

        return $countedValueName;
    }

    protected function mirrorComments(Node $newNode, Node $oldNode): void
    {
        $newNode->setAttribute(AttributeKey::PHP_DOC_INFO, $oldNode->getAttribute(AttributeKey::PHP_DOC_INFO));
        $newNode->setAttribute('comments', $oldNode->getAttribute('comments'));
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
                $ifStmt->setAttribute('comments', $node->getComments());
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

    private function isNameIdentical(Node $node, Node $originalNode): bool
    {
        if (! $originalNode instanceof Name) {
            return false;
        }

        // names are the same
        return $this->areNodesEqual($originalNode->getAttribute('originalName'), $node);
    }

    protected function isOpenSourceProjectType(): bool
    {
        $projectType = $this->parameterProvider->provideParameter(Option::PROJECT_TYPE);

        return in_array(
            $projectType,
            // make it typo proof
            [Option::PROJECT_TYPE_OPEN_SOURCE, Option::PROJECT_TYPE_OPEN_SOURCE_UNDESCORED],
            true
        );
    }
}
