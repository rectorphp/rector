<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Exception\MissingTagException;
use Rector\PhpParser\CurrentNodeProvider;
use Symplify\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Symplify\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Symplify\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use function Safe\sprintf;

final class DocBlockAnalyzer
{
    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var PhpDocInfoPrinter
     */
    private $phpDocInfoPrinter;

    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    /**
     * @var PhpDocInfoFqnTypeDecorator
     */
    private $phpDocInfoFqnTypeDecorator;

    public function __construct(
        PhpDocInfoFactory $phpDocInfoFactory,
        PhpDocInfoPrinter $phpDocInfoPrinter,
        CurrentNodeProvider $currentNodeProvider,
        PhpDocInfoFqnTypeDecorator $phpDocInfoFqnTypeDecorator
    ) {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
        $this->currentNodeProvider = $currentNodeProvider;
        $this->phpDocInfoFqnTypeDecorator = $phpDocInfoFqnTypeDecorator;
    }

    public function hasTag(Node $node, string $name): bool
    {
        if ($node->getDocComment() === null) {
            return false;
        }

        return Strings::contains($node->getDocComment()->getText(), '@' . $name);
    }

    public function removeParamTagByName(Node $node, string $name): void
    {
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDocInfo = $this->createPhpDocInfoWithFqnTypesFromNode($node);
        $phpDocInfo->removeParamTagByParameter($name);

        $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
    }

    public function removeTagFromNode(Node $node, string $name): void
    {
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);
        $phpDocInfo->removeTagByName($name);

        $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
    }

    public function changeType(Node $node, string $oldType, string $newType): void
    {
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDocInfo = $this->createPhpDocInfoWithFqnTypesFromNode($node);

        $phpDocInfo->replacePhpDocTypeByAnother($oldType, $newType);

        $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
    }

    public function replaceAnnotationInNode(Node $node, string $oldAnnotation, string $newAnnotation): void
    {
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);
        $phpDocInfo->replaceTagByAnother($oldAnnotation, $newAnnotation);

        $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
    }

    /**
     * @return string[]
     */
    public function getVarTypes(Node $node): array
    {
        if ($node->getDocComment() === null) {
            return [];
        }

        $phpDocInfo = $this->createPhpDocInfoWithFqnTypesFromNode($node);

        return $phpDocInfo->getVarTypes();
    }

    /**
     * @return string[]
     */
    public function getNonFqnVarTypes(Node $node): array
    {
        if ($node->getDocComment() === null) {
            return [];
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

        return $phpDocInfo->getVarTypes();
    }

    /**
     * @todo add test for Multi|Types
     */
    public function getTypeForParam(Node $node, string $paramName): ?string
    {
        if ($node->getDocComment() === null) {
            return null;
        }

        $phpDocInfo = $this->createPhpDocInfoWithFqnTypesFromNode($node);

        return (string) $phpDocInfo->getParamTypeNode($paramName);
    }

    /**
     * @final
     * @return PhpDocTagNode[]
     */
    public function getTagsByName(Node $node, string $name): array
    {
        if ($node->getDocComment() === null) {
            return [];
        }

        $phpDocInfo = $this->createPhpDocInfoWithFqnTypesFromNode($node);

        return $phpDocInfo->getTagsByName($name);
    }

    /**
     * @final
     */
    public function getTagByName(Node $node, string $name): PhpDocTagNode
    {
        if (! $this->hasTag($node, $name)) {
            throw new MissingTagException('Tag "%s" was not found at "%s" node.', $name, get_class($node));
        }

        return $this->getTagsByName($node, $name)[0];
    }

    private function updateNodeWithPhpDocInfo(Node $node, PhpDocInfo $phpDocInfo): void
    {
        // skip if has no doc comment
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDoc = $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);
        if ($phpDoc) {
            $node->setDocComment(new Doc($phpDoc));
            return;
        }

        // no comments, null
        $node->setAttribute('comments', null);
    }

    private function createPhpDocInfoFromNode(Node $node): PhpDocInfo
    {
        $this->currentNodeProvider->setCurrentNode($node);
        if ($node->getDocComment() === null) {
            throw new ShouldNotHappenException(sprintf(
                'Node must have a comment. Check `$node->getDocComment() !== null` before passing it to %s',
                __METHOD__
            ));
        }

        // $this->phpDocInfoDecorators needs to be nulled, or FQN is made by default due to collector
        // @todo resolve propperty and decouple node traversing from: \Symplify\BetterPhpDocParser\PhpDocInfo\AbstractPhpDocInfoDecorator into a service + remove parent dependcy of
        // @see \Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\PhpDocInfoFqnTypeDecorator
        (new PrivatesAccessor())->setPrivateProperty($this->phpDocInfoFactory, 'phpDocInfoDecorators', []);

        return $this->phpDocInfoFactory->createFrom($node->getDocComment()->getText());
    }

    private function createPhpDocInfoWithFqnTypesFromNode(Node $node): PhpDocInfo
    {
        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

        $this->phpDocInfoFqnTypeDecorator->decorate($phpDocInfo);

        return $phpDocInfo;
    }
}
