<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareParamTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareVarTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\Type\AttributeAwareIdentifierTypeNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocModifier;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Exception\MissingTagException;
use Rector\NodeTypeResolver\Php\ParamTypeInfo;
use Rector\NodeTypeResolver\Php\ReturnTypeInfo;
use Rector\NodeTypeResolver\Php\VarTypeInfo;
use Rector\Php\TypeAnalyzer;

final class DocBlockManipulator
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
     * @var PhpDocModifier
     */
    private $phpDocModifier;

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    public function __construct(
        PhpDocInfoFactory $phpDocInfoFactory,
        PhpDocInfoPrinter $phpDocInfoPrinter,
        PhpDocModifier $phpDocModifier,
        TypeAnalyzer $typeAnalyzer
    ) {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
        $this->phpDocModifier = $phpDocModifier;
        $this->typeAnalyzer = $typeAnalyzer;
    }

    public function hasTag(Node $node, string $name): bool
    {
        if ($node->getDocComment() === null) {
            return false;
        }

        // simple check
        $pattern = '#@(\\\\)?' . preg_quote(ltrim($name, '@')) . '#';
        if (Strings::match($node->getDocComment()->getText(), $pattern)) {
            return true;
        }

        // advanced check, e.g. for "Namespaced\Annotations\DI"
        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

        return $phpDocInfo->hasTag($name);
    }

    public function removeParamTagByName(Node $node, string $name): void
    {
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);
        $this->phpDocModifier->removeParamTagByParameter($phpDocInfo, $name);

        $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
    }

    public function addTag(Node $node, PhpDocChildNode $phpDocChildNode): void
    {
        if ($node->getDocComment() !== null) {
            $phpDocInfo = $this->createPhpDocInfoFromNode($node);
            $phpDocNode = $phpDocInfo->getPhpDocNode();
            $phpDocNode->children[] = $phpDocChildNode;
            $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
        } else {
            $phpDocNode = new AttributeAwarePhpDocNode([$phpDocChildNode]);
            $node->setDocComment(new Doc($phpDocNode->__toString()));
        }
    }

    public function removeTagFromNode(Node $node, string $name): void
    {
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

        $this->phpDocModifier->removeTagByName($phpDocInfo, $name);
        $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
    }

    public function changeType(Node $node, string $oldType, string $newType): void
    {
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

        $this->phpDocModifier->replacePhpDocTypeByAnother($phpDocInfo->getPhpDocNode(), $oldType, $newType, $node);

        $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
    }

    public function replaceAnnotationInNode(Node $node, string $oldAnnotation, string $newAnnotation): void
    {
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);
        $this->phpDocModifier->replaceTagByAnother($phpDocInfo->getPhpDocNode(), $oldAnnotation, $newAnnotation);

        $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
    }

    public function getReturnTypeInfo(Node $node): ?ReturnTypeInfo
    {
        if ($node->getDocComment() === null) {
            return null;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);
        $types = $phpDocInfo->getShortReturnTypes();
        if ($types === []) {
            return null;
        }

        $fqnTypes = $phpDocInfo->getReturnTypes();

        return new ReturnTypeInfo($types, $this->typeAnalyzer, $fqnTypes);
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

        $fqnTypes = $phpDocInfo->getParamTagValues();

        $paramTypeInfos = [];
        /** @var AttributeAwareParamTagValueNode $paramTagValueNode */
        foreach ($types as $i => $paramTagValueNode) {
            $fqnParamTagValueNode = $fqnTypes[$i];

            $paramTypeInfo = new ParamTypeInfo(
                $paramTagValueNode->parameterName,
                $this->typeAnalyzer,
                $paramTagValueNode->getAttribute(Attribute::TYPE_AS_ARRAY),
                $fqnParamTagValueNode->getAttribute(Attribute::TYPE_AS_ARRAY)
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

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

        return $phpDocInfo->getTagsByName($name);
    }

    public function addVarTag(Node $node, string $type): void
    {
        // there might be no phpdoc at all
        if ($node->getDocComment() !== null) {
            $phpDocInfo = $this->createPhpDocInfoFromNode($node);
            $phpDocNode = $phpDocInfo->getPhpDocNode();

            $varTagValueNode = new AttributeAwareVarTagValueNode(new AttributeAwareIdentifierTypeNode(
                '\\' . $type
            ), '', '');
            $phpDocNode->children[] = new AttributeAwarePhpDocTagNode('@var', $varTagValueNode);

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
        $types = $phpDocInfo->getShortVarTypes();
        if ($types === []) {
            return null;
        }

        $fqnTypes = $phpDocInfo->getVarTypes();

        return new VarTypeInfo($types, $this->typeAnalyzer, $fqnTypes);
    }

    private function updateNodeWithPhpDocInfo(Node $node, PhpDocInfo $phpDocInfo): void
    {
        // skip if has no doc comment
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDoc = $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);
        if ($phpDoc !== '') {
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

        return $this->phpDocInfoFactory->createFromNode($node);
    }
}
