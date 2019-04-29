<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\InvalidTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\Annotation\AnnotationNaming;
use Rector\BetterPhpDocParser\Ast\NodeTraverser;
use Rector\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactory;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareParamTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareVarTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\Type\AttributeAwareIdentifierTypeNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\Attributes\Contract\Ast\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\NodeDecorator\StringsTypePhpDocNodeDecorator;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
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
     * @var StringsTypePhpDocNodeDecorator
     */
    private $stringsTypePhpDocNodeDecorator;

    /**
     * @var PhpDocInfoPrinter
     */
    private $phpDocInfoPrinter;

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @var AttributeAwareNodeFactory
     */
    private $attributeAwareNodeFactory;

    /**
     * @var NodeTraverser
     */
    private $nodeTraverser;

    public function __construct(
        PhpDocInfoFactory $phpDocInfoFactory,
        PhpDocInfoPrinter $phpDocInfoPrinter,
        TypeAnalyzer $typeAnalyzer,
        AttributeAwareNodeFactory $attributeAwareNodeFactory,
        StringsTypePhpDocNodeDecorator $stringsTypePhpDocNodeDecorator,
        NodeTraverser $nodeTraverser
    ) {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
        $this->typeAnalyzer = $typeAnalyzer;
        $this->attributeAwareNodeFactory = $attributeAwareNodeFactory;
        $this->stringsTypePhpDocNodeDecorator = $stringsTypePhpDocNodeDecorator;
        $this->nodeTraverser = $nodeTraverser;
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
        $this->removeParamTagByParameter($phpDocInfo, $name);

        $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
    }

    public function addTag(Node $node, PhpDocChildNode $phpDocChildNode): void
    {
        $phpDocChildNode = $this->attributeAwareNodeFactory->createFromNode($phpDocChildNode);

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

        $this->removeTagByName($phpDocInfo, $name);
        $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
    }

    public function changeType(Node $node, string $oldType, string $newType): void
    {
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

        $this->replacePhpDocTypeByAnother($phpDocInfo->getPhpDocNode(), $oldType, $newType, $node);

        $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
    }

    public function replaceAnnotationInNode(Node $node, string $oldAnnotation, string $newAnnotation): void
    {
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);
        $this->replaceTagByAnother($phpDocInfo->getPhpDocNode(), $oldAnnotation, $newAnnotation);

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
                $fqnParamTagValueNode->getAttribute(Attribute::RESOLVED_NAMES)
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

    public function addReturnTag(Node $node, string $type): void
    {
        // there might be no phpdoc at all
        if ($node->getDocComment() !== null) {
            $phpDocInfo = $this->createPhpDocInfoFromNode($node);
            $phpDocNode = $phpDocInfo->getPhpDocNode();

            $varTagValueNode = new AttributeAwareVarTagValueNode(new AttributeAwareIdentifierTypeNode(
                '\\' . $type
            ), '', '');
            $phpDocNode->children[] = new AttributeAwarePhpDocTagNode('@return', $varTagValueNode);

            $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
        } else {
            // create completely new docblock
            $varDocComment = sprintf("/**\n * @return %s\n */", $type);
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

    public function removeTagByName(PhpDocInfo $phpDocInfo, string $tagName): void
    {
        $phpDocNode = $phpDocInfo->getPhpDocNode();

        $tagName = AnnotationNaming::normalizeName($tagName);

        $phpDocTagNodes = $phpDocInfo->getTagsByName($tagName);

        foreach ($phpDocTagNodes as $phpDocTagNode) {
            $this->removeTagFromPhpDocNode($phpDocNode, $phpDocTagNode);
        }
    }

    public function removeParamTagByParameter(PhpDocInfo $phpDocInfo, string $parameterName): void
    {
        $phpDocNode = $phpDocInfo->getPhpDocNode();

        /** @var PhpDocTagNode[] $phpDocTagNodes */
        $phpDocTagNodes = $phpDocNode->getTagsByName('@param');

        foreach ($phpDocTagNodes as $phpDocTagNode) {
            /** @var ParamTagValueNode|InvalidTagValueNode $paramTagValueNode */
            $paramTagValueNode = $phpDocTagNode->value;

            $parameterName = '$' . ltrim($parameterName, '$');

            // process invalid tag values
            if ($paramTagValueNode instanceof InvalidTagValueNode) {
                if ($paramTagValueNode->value === $parameterName) {
                    $this->removeTagFromPhpDocNode($phpDocNode, $phpDocTagNode);
                }
                // process normal tag
            } elseif ($paramTagValueNode->parameterName === $parameterName) {
                $this->removeTagFromPhpDocNode($phpDocNode, $phpDocTagNode);
            }
        }
    }

    /**
     * @param PhpDocTagNode|PhpDocTagValueNode $phpDocTagOrPhpDocTagValueNode
     */
    public function removeTagFromPhpDocNode(PhpDocNode $phpDocNode, $phpDocTagOrPhpDocTagValueNode): void
    {
        // remove specific tag
        foreach ($phpDocNode->children as $key => $phpDocChildNode) {
            if ($phpDocChildNode === $phpDocTagOrPhpDocTagValueNode) {
                unset($phpDocNode->children[$key]);
                return;
            }
        }

        // or by type
        foreach ($phpDocNode->children as $key => $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if ($phpDocChildNode->value === $phpDocTagOrPhpDocTagValueNode) {
                unset($phpDocNode->children[$key]);
            }
        }
    }

    public function replaceTagByAnother(PhpDocNode $phpDocNode, string $oldTag, string $newTag): void
    {
        $oldTag = AnnotationNaming::normalizeName($oldTag);
        $newTag = AnnotationNaming::normalizeName($newTag);

        foreach ($phpDocNode->children as $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if ($phpDocChildNode->name === $oldTag) {
                $phpDocChildNode->name = $newTag;
            }
        }
    }

    public function replacePhpDocTypeByAnother(
        AttributeAwarePhpDocNode $attributeAwarePhpDocNode,
        string $oldType,
        string $newType,
        Node $node
    ): AttributeAwarePhpDocNode {
        foreach ($attributeAwarePhpDocNode->children as $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if (! $this->isTagValueNodeWithType($phpDocChildNode)) {
                continue;
            }

            /** @var VarTagValueNode|ParamTagValueNode|ReturnTagValueNode $tagValueNode */
            $tagValueNode = $phpDocChildNode->value;

            $phpDocChildNode->value->type = $this->replaceTypeNode($tagValueNode->type, $oldType, $newType);

            $this->stringsTypePhpDocNodeDecorator->decorate($attributeAwarePhpDocNode, $node);
        }

        return $attributeAwarePhpDocNode;
    }

    /**
     * @param string[]|null $excludedClasses
     */
    public function changeUnderscoreType(Node $node, string $namespacePrefix, ?array $excludedClasses): void
    {
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);
        $phpDocNode = $phpDocInfo->getPhpDocNode();

        $this->nodeTraverser->traverseWithCallable($phpDocNode, function (AttributeAwareNodeInterface $node) use (
            $namespacePrefix,
            $excludedClasses
        ) {
            if (! $node instanceof IdentifierTypeNode) {
                return $node;
            }

            $name = ltrim($node->name, '\\');
            if (! Strings::startsWith($name, $namespacePrefix)) {
                return $node;
            }

            // excluded?
            if (is_array($excludedClasses) && in_array($name, $excludedClasses, true)) {
                return $node;
            }

            // change underscore to \\
            $nameParts = explode('_', $name);
            $node->name = '\\' . implode('\\', $nameParts);

            return $node;
        });

        $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
    }

    private function updateNodeWithPhpDocInfo(Node $node, PhpDocInfo $phpDocInfo): void
    {
        // skip if has no doc comment
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDoc = $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);
        if ($phpDoc !== '') {
            // no change, don't save it
            if ($node->getDocComment()->getText() === $phpDoc) {
                return;
            }

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

    private function isTagValueNodeWithType(PhpDocTagNode $phpDocTagNode): bool
    {
        return $phpDocTagNode->value instanceof ParamTagValueNode ||
            $phpDocTagNode->value instanceof VarTagValueNode ||
            $phpDocTagNode->value instanceof ReturnTagValueNode ||
            $phpDocTagNode->value instanceof ThrowsTagValueNode;
    }

    private function replaceTypeNode(TypeNode $typeNode, string $oldType, string $newType): TypeNode
    {
        // @todo use $this->nodeTraverser->traverseWithCallable here matching "AttributeAwareIdentifierTypeNode"

        if ($typeNode instanceof AttributeAwareIdentifierTypeNode) {
            $nodeType = $this->resolveNodeType($typeNode);

            if (is_a($nodeType, $oldType, true) || ltrim($nodeType, '\\') === $oldType) {
                $newType = $this->forceFqnPrefix($newType);

                return new AttributeAwareIdentifierTypeNode($newType);
            }
        }

        if ($typeNode instanceof UnionTypeNode) {
            foreach ($typeNode->types as $key => $subTypeNode) {
                $typeNode->types[$key] = $this->replaceTypeNode($subTypeNode, $oldType, $newType);
            }
        }

        if ($typeNode instanceof ArrayTypeNode) {
            $typeNode->type = $this->replaceTypeNode($typeNode->type, $oldType, $newType);

            return $typeNode;
        }

        return $typeNode;
    }

    /**
     * @param AttributeAwareNodeInterface&TypeNode $typeNode
     */
    private function resolveNodeType(TypeNode $typeNode): string
    {
        $nodeType = $typeNode->getAttribute('resolved_name');

        if ($nodeType === null) {
            $nodeType = $typeNode->getAttribute(Attribute::TYPE_AS_STRING);
        }

        if ($nodeType === null) {
            $nodeType = $typeNode->name;
        }

        return $nodeType;
    }

    private function forceFqnPrefix(string $newType): string
    {
        if (Strings::contains($newType, '\\')) {
            $newType = '\\' . ltrim($newType, '\\');
        }

        return $newType;
    }
}
