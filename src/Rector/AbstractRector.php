<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;
use Rector\Application\RemovedFilesCollector;
use Rector\Contract\Rector\PhpRectorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php\PhpVersionProvider;
use Rector\PhpParser\Node\Value\ValueResolver;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

abstract class AbstractRector extends NodeVisitorAbstract implements PhpRectorInterface
{
    use AbstractRectorTrait;

    /**
     * @var BuilderFactory
     */
    protected $builderFactory;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var RemovedFilesCollector
     */
    private $removedFilesCollector;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var PhpVersionProvider
     */
    private $phpVersionProvider;

    /**
     * Run once in the every end of one processed file
     */
    protected function tearDown(): void
    {
    }

    /**
     * @required
     */
    public function setAbstractRectorDependencies(
        SymfonyStyle $symfonyStyle,
        ValueResolver $valueResolver,
        RemovedFilesCollector $removedFilesCollector,
        PhpVersionProvider $phpVersionProvider,
        BuilderFactory $builderFactory
    ): void {
        $this->symfonyStyle = $symfonyStyle;
        $this->valueResolver = $valueResolver;
        $this->removedFilesCollector = $removedFilesCollector;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->builderFactory = $builderFactory;
    }

    /**
     * @return int|Node|null
     */
    final public function enterNode(Node $node)
    {
        if (! $this->isMatchingNodeType(get_class($node))) {
            return null;
        }

        // show current Rector class on --debug
        if ($this->symfonyStyle->isDebug()) {
            $this->symfonyStyle->writeln(static::class);
        }

        // already removed
        if ($this->isNodeRemoved($node)) {
            return null;
        }

        $originalNode = clone $node;
        $originalComment = $node->getComments();
        $originalDocComment = $node->getDocComment();
        $node = $this->refactor($node);
        if ($node === null) {
            return null;
        }

        // changed!
        if ($this->hasNodeChanged($originalNode, $node)) {
            $this->mirrorAttributes($originalNode, $node);
            $this->updateAttributes($node);
            $this->keepFileInfoAttribute($node, $originalNode);
            $this->notifyNodeChangeFileInfo($node);

        // doc block has changed
        } elseif ($node->getComments() !== $originalComment || $node->getDocComment() !== $originalDocComment) {
            $this->notifyNodeChangeFileInfo($node);
        }

        if ($originalNode instanceof Stmt && $node instanceof Expr) {
            return new Expression($node);
        }

        return $node;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function afterTraverse(array $nodes): array
    {
        if ($this->nodeAddingCommander->isActive()) {
            $nodes = $this->nodeAddingCommander->traverseNodes($nodes);
        }

        if ($this->propertyAddingCommander->isActive()) {
            $nodes = $this->propertyAddingCommander->traverseNodes($nodes);
        }

        if ($this->nodeRemovingCommander->isActive()) {
            $nodes = $this->nodeRemovingCommander->traverseNodes($nodes);
        }

        $this->tearDown();

        return $nodes;
    }

    protected function removeFile(SmartFileInfo $smartFileInfo): void
    {
        $this->removedFilesCollector->addFile($smartFileInfo);
    }

    /**
     * @return mixed
     */
    protected function getValue(Expr $expr)
    {
        return $this->valueResolver->resolve($expr);
    }

    /**
     * @param mixed $expectedValue
     */
    protected function isValue(Expr $expr, $expectedValue): bool
    {
        return $this->getValue($expr) === $expectedValue;
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
        } elseif ($originalNode->getAttribute(AttributeKey::PARENT_NODE)) {
            /** @var Node $parentOriginalNode */
            $parentOriginalNode = $originalNode->getAttribute(AttributeKey::PARENT_NODE);
            $node->setAttribute(AttributeKey::FILE_INFO, $parentOriginalNode->getAttribute(AttributeKey::FILE_INFO));
        }
    }

    private function mirrorAttributes(Node $oldNode, Node $newNode): void
    {
        $attributesToMirror = [
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

        foreach ($oldNode->getAttributes() as $attributeName => $oldNodeAttributeValue) {
            if (! in_array($attributeName, $attributesToMirror, true)) {
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

    private function hasNodeChanged(Node $originalNode, Node $node): bool
    {
        return ! $this->areNodesEqual($originalNode, $node);
    }
}
