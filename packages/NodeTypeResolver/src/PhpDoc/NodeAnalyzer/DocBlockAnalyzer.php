<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Exception\MissingTagException;
use Rector\NodeTypeResolver\Php\ParamTypeInfo;
use Rector\NodeTypeResolver\Php\ReturnTypeInfo;
use Rector\NodeTypeResolver\Php\VarTypeInfo;
use function Safe\sprintf;
use Symplify\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Symplify\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Symplify\BetterPhpDocParser\PhpDocParser\TypeNodeToStringsConvertor;
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
     * @var PhpDocInfoFqnTypeDecorator
     */
    private $phpDocInfoFqnTypeDecorator;

    /**
     * @var FqnAnnotationTypeDecorator
     */
    private $fqnAnnotationTypeDecorator;

    /**
     * @var TypeNodeToStringsConvertor
     */
    private $typeNodeToStringsConvertor;

    public function __construct(
        PhpDocInfoFactory $phpDocInfoFactory,
        PhpDocInfoPrinter $phpDocInfoPrinter,
        PhpDocInfoFqnTypeDecorator $phpDocInfoFqnTypeDecorator,
        FqnAnnotationTypeDecorator $fqnAnnotationTypeDecorator,
        TypeNodeToStringsConvertor $typeNodeToStringsConvertor
    ) {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
        $this->phpDocInfoFqnTypeDecorator = $phpDocInfoFqnTypeDecorator;
        $this->fqnAnnotationTypeDecorator = $fqnAnnotationTypeDecorator;
        $this->typeNodeToStringsConvertor = $typeNodeToStringsConvertor;
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

    public function getReturnTypeInfo(Node $node): ?ReturnTypeInfo
    {
        if ($node->getDocComment() === null) {
            return null;
        }

        $types = $this->createPhpDocInfoFromNode($node)->getReturnTypes();
        if ($types === []) {
            return null;
        }

        $fqnTypes = $this->createPhpDocInfoWithFqnTypesFromNode($node)->getReturnTypes();

        return new ReturnTypeInfo($types, $fqnTypes);
    }

    /**
     * With "name" as key
     *
     * @return ParamTypeInfo[]
     */
    public function getParamTypeInfos(Node $node): array
    {
        if ($node->getDocComment() === null) {
            return [];
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);
        $types = $phpDocInfo->getParamTagValues();
        if ($types === []) {
            return [];
        }

        $this->fqnAnnotationTypeDecorator->decorate($phpDocInfo, $node);
        $fqnTypes = $phpDocInfo->getParamTagValues();

        $paramTypeInfos = [];
        /** @var ParamTagValueNode $paramTagValueNode */
        foreach ($types as $i => $paramTagValueNode) {
            $fqnParamTagValueNode = $fqnTypes[$i];

            $paramTypeInfo = new ParamTypeInfo(
                $paramTagValueNode->parameterName,
                $this->typeNodeToStringsConvertor->convert($paramTagValueNode->type),
                $this->typeNodeToStringsConvertor->convert($fqnParamTagValueNode->type)
            );

            $paramTypeInfos[$paramTypeInfo->getName()] = $paramTypeInfo;
        }

        return $paramTypeInfos;
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
        // there might be no phpdoc at all
        if ($node->getDocComment()) {
            $phpDocInfo = $this->createPhpDocInfoFromNode($node);
            $phpDocNode = $phpDocInfo->getPhpDocNode();

            $varTagValueNode = new VarTagValueNode(new IdentifierTypeNode('\\' . $type), '', '');
            $phpDocNode->children[] = new PhpDocTagNode('@var', $varTagValueNode);

            $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
        } else {
            // create completely new docblock
            $varDocComment = sprintf("/**\n * @var %s\n */", $type);
            $node->setDocComment(new Doc($varDocComment));
        }
    }

    /**
     * @final
     */
    public function getTagByName(Node $node, string $name): PhpDocTagNode
    {
        if (! $this->hasTag($node, $name)) {
            throw new MissingTagException(sprintf('Tag "%s" was not found at "%s" node.', $name, get_class($node)));
        }

        /** @var PhpDocTagNode[] $foundTags */
        $foundTags = $this->getTagsByName($node, $name);
        return array_shift($foundTags);
    }

    public function getVarTypeInfo(Node $node): ?VarTypeInfo
    {
        if ($node->getDocComment() === null) {
            return null;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);
        $types = $phpDocInfo->getVarTypes();
        if ($types === []) {
            return null;
        }

        $this->phpDocInfoFqnTypeDecorator->decorate($phpDocInfo, $node);
        $fqnTypes = $phpDocInfo->getVarTypes();

        return new VarTypeInfo($types, $fqnTypes);
    }

    private function createPhpDocInfoWithFqnTypesFromNode(Node $node): PhpDocInfo
    {
        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

        $this->phpDocInfoFqnTypeDecorator->decorate($phpDocInfo, $node);

        return $phpDocInfo;
    }

    private function isNamespaced(string $name): bool
    {
        return Strings::contains($name, '\\');
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
        if ($node->getDocComment() === null) {
            throw new ShouldNotHappenException(sprintf(
                'Node must have a comment. Check `$node->getDocComment() !== null` before passing it to %s',
                __METHOD__
            ));
        }

        return $this->phpDocInfoFactory->createFrom($node->getDocComment()->getText());
    }
}
