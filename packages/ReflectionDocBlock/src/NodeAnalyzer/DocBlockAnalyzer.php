<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\NodeAnalyzer;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Symplify\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Symplify\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Symplify\BetterPhpDocParser\PhpDocParser\TypeResolver;
use Symplify\BetterPhpDocParser\Printer\PhpDocInfoPrinter;

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
     * @var NamespaceAnalyzer
     */
    private $namespaceAnalyzer;

    /**
     * @var TypeResolver
     */
    private $typeResolver;
    /**
     * @var PhpDocInfoFqnTypeDecorator
     */
    private $phpDocInfoFqnTypeDecorator;

    public function __construct(
        PhpDocInfoFactory $phpDocInfoFactory,
        PhpDocInfoPrinter $phpDocInfoPrinter,
        NamespaceAnalyzer $namespaceAnalyzer,
        TypeResolver $typeResolver,
        PhpDocInfoFqnTypeDecorator $phpDocInfoFqnTypeDecorator
    ) {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
        $this->namespaceAnalyzer = $namespaceAnalyzer;
        $this->typeResolver = $typeResolver;
        $this->phpDocInfoFqnTypeDecorator = $phpDocInfoFqnTypeDecorator;
    }

    public function hasAnnotation(Node $node, string $annotation): bool
    {
        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

        return $phpDocInfo->hasTag($annotation);
    }

    public function removeParamTagByName(Node $node, string $name): void
    {
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);
        $phpDocInfo->removeParamTagByParameter($name);

        $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
    }

    public function removeAnnotationFromNode(Node $node, string $name, ?string $content = null): void
    {
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

        if ($content) {
            $phpDocInfo->removeTagByNameAndContent($name, $content);
        } else {
            $phpDocInfo->removeTagByName($name);
        }

        $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
    }

    /**
     * @todo move to PhpDocInfo
     * @todo no need to check for nullable, just replace types
     * @todo check one service above, it
     */
    public function changeType(Node $node, string $oldType, string $newType): void
    {
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

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
     * @todo move to phpdoc info
     * @return string[]|null
     */
    public function getVarTypes(Node $node): ?array
    {
        if ($node->getDocComment() === null) {
            return null;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

        $varType = $phpDocInfo->getVarTypeNode();
        if ($varType === null) {
            return null;
        }

        $typesAsString = $this->typeResolver->resolveDocType($varType);

        $fullyQualifiedTypes = [];
        foreach (explode('|', $typesAsString) as $type) {
            $fullyQualifiedTypes[] = $this->namespaceAnalyzer->resolveTypeToFullyQualified($type, $node);
        }

        return $fullyQualifiedTypes;
    }

    /**
     * @todo add test for Multi|Types
     */
    public function getTypeForParam(Node $node, string $paramName): ?string
    {
        if ($node->getDocComment() === null) {
            return null;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

        return (string) $phpDocInfo->getParamTypeNode($paramName);
    }

    /**
     * @final
     * @return PhpDocTagNode[]
     */
    public function getTagsByName(Node $node, string $name): array
    {
        if ($node->getDocComment() === null) {
            return null;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

        return $phpDocInfo->getTagsByName($name);
    }

    /**
     * @final
     */
    public function getTagByName(Node $node, string $name): ?PhpDocTagNode
    {
        return $this->getTagsByName($node, $name)[0] ?? null;
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
        $this->phpDocInfoFqnTypeDecorator->setCurrentPhpParserNode($node);

        return $this->phpDocInfoFactory->createFrom($node->getDocComment()->getText());
    }
}
