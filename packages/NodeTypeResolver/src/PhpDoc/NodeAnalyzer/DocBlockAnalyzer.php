<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Exception\MissingTagException;
use Rector\PhpParser\CurrentNodeProvider;
use Symplify\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Symplify\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Symplify\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
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

    /**
     * @var FqnAnnotationTypeDecorator
     */
    private $fqnAnnotationTypeDecorator;

    public function __construct(
        PhpDocInfoFactory $phpDocInfoFactory,
        PhpDocInfoPrinter $phpDocInfoPrinter,
        CurrentNodeProvider $currentNodeProvider,
        PhpDocInfoFqnTypeDecorator $phpDocInfoFqnTypeDecorator,
        FqnAnnotationTypeDecorator $fqnAnnotationTypeDecorator
    ) {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
        $this->currentNodeProvider = $currentNodeProvider;
        $this->phpDocInfoFqnTypeDecorator = $phpDocInfoFqnTypeDecorator;
        $this->fqnAnnotationTypeDecorator = $fqnAnnotationTypeDecorator;
    }

    public function hasTag(Node $node, string $name): bool
    {
        if ($node->getDocComment() === null) {
            return false;
        }

        // simple check
        if (Strings::contains($node->getDocComment()->getText(), '@' . $name)) {
            return true;
        }

        // advanced check, e.g. for "Namespaced\Annotations\DI"
        $phpDocInfo = $this->createPhpDocInfoWithFqnTypesFromNode($node);

        // is namespaced annotation?
        if ($this->isNamespaced($name)) {
            $this->fqnAnnotationTypeDecorator->decorate($phpDocInfo, $node);
        }

        return $phpDocInfo->hasTag($name);
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

        if ($this->isNamespaced($name)) {
            $this->fqnAnnotationTypeDecorator->decorate($phpDocInfo, $node);
        }

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

        if ($this->isNamespaced($name)) {
            $this->fqnAnnotationTypeDecorator->decorate($phpDocInfo, $node);
        }

        return $phpDocInfo->getTagsByName($name);
    }

    public function addVarTag(Node $node, string $type): void
    {
        $phpDocInfo = $this->createPhpDocInfoFromNode($node);
        $phpDocNode = $phpDocInfo->getPhpDocNode();

        $varTagValueNode = new VarTagValueNode(new IdentifierTypeNode('\\' . $type), '', '');
        $phpDocNode->children[] = new PhpDocTagNode('@var', $varTagValueNode);

        $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
    }

    /**
     * @final
     */
    public function getTagByName(Node $node, string $name): PhpDocTagNode
    {
        if (! $this->hasTag($node, $name)) {
            throw new MissingTagException('Tag "%s" was not found at "%s" node.', $name, get_class($node));
        }

        /** @var PhpDocTagNode[] $foundTags */
        $foundTags = $this->getTagsByName($node, $name);
        return array_shift($foundTags);
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

        return $this->phpDocInfoFactory->createFrom($node->getDocComment()->getText());
    }

    private function createPhpDocInfoWithFqnTypesFromNode(Node $node): PhpDocInfo
    {
        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

        $this->phpDocInfoFqnTypeDecorator->decorate($phpDocInfo);

        return $phpDocInfo;
    }

    private function isNamespaced(string $name): bool
    {
        return Strings::contains($name, '\\');
    }
}
