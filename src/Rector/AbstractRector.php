<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;
use Rector\Application\AppliedRectorCollector;
use Rector\Application\RemovedFilesCollector;
use Rector\Contract\Rector\PhpRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Value\ValueResolver;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

abstract class AbstractRector extends NodeVisitorAbstract implements PhpRectorInterface
{
    use TypeAnalyzerTrait;
    use NameResolverTrait;
    use ConstFetchAnalyzerTrait;
    use BetterStandardPrinterTrait;
    use NodeCommandersTrait;
    use NodeFactoryTrait;
    use VisibilityTrait;

    /**
     * @var AppliedRectorCollector
     */
    private $appliedRectorCollector;

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
     * @required
     */
    public function setAbstractRectorDependencies(
        AppliedRectorCollector $appliedRectorCollector,
        SymfonyStyle $symfonyStyle,
        ValueResolver $valueResolver,
        RemovedFilesCollector $removedFilesCollector
    ): void {
        $this->appliedRectorCollector = $appliedRectorCollector;
        $this->symfonyStyle = $symfonyStyle;
        $this->valueResolver = $valueResolver;
        $this->removedFilesCollector = $removedFilesCollector;
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

        $originalNode = $node;
        $node = $this->refactor($node);
        if ($node === null) {
            return null;
        }

        // changed!
        if ($originalNode !== $node) {
            $this->mirrorAttributes($originalNode, $node);

            $this->keepFileInfoAttribute($node, $originalNode);
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

    protected function notifyNodeChangeFileInfo(Node $node): void
    {
        $fileInfo = $node->getAttribute(Attribute::FILE_INFO);
        if ($fileInfo === null) {
            throw new ShouldNotHappenException(sprintf(
                'Node is missing "%s" attribute.%sYou probably created a new node and forgot to move attributes of old one in "%s".',
                Attribute::FILE_INFO,
                PHP_EOL,
                static::class
            ));
        }

        $this->appliedRectorCollector->addRectorClass(static::class, $fileInfo);
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
        if ($node->getAttribute(Attribute::FILE_INFO) instanceof SmartFileInfo) {
            return;
        }

        if ($originalNode->getAttribute(Attribute::FILE_INFO) !== null) {
            $node->setAttribute(Attribute::FILE_INFO, $originalNode->getAttribute(Attribute::FILE_INFO));
        } elseif ($originalNode->getAttribute(Attribute::PARENT_NODE)) {
            /** @var Node $parentOriginalNode */
            $parentOriginalNode = $originalNode->getAttribute(Attribute::PARENT_NODE);
            $node->setAttribute(Attribute::FILE_INFO, $parentOriginalNode->getAttribute(Attribute::FILE_INFO));
        }
    }

    private function mirrorAttributes(Node $oldNode, Node $newNode): void
    {
        $attributesToMirror = [
            Attribute::CLASS_NODE,
            Attribute::CLASS_NAME,
            Attribute::FILE_INFO,
            Attribute::METHOD_NODE,
            Attribute::USE_NODES,
            Attribute::SCOPE,
            Attribute::METHOD_NAME,
            Attribute::NAMESPACE_NAME,
            Attribute::NAMESPACE_NODE,
            Attribute::RESOLVED_NAME,
        ];

        foreach ($oldNode->getAttributes() as $attributeName => $oldNodeAttributeValue) {
            if (! in_array($attributeName, $attributesToMirror, true)) {
                continue;
            }

            $newNode->setAttribute($attributeName, $oldNodeAttributeValue);
        }
    }
}
