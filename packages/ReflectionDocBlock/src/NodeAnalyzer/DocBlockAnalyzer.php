<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Symplify\BetterPhpDocParser\PhpDocParser\PhpDocInfoFactory;
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
     * @var Node
     */
    private $node;

    /**
     * @var TypeResolver
     */
    private $typeResolver;

    public function __construct(
        PhpDocInfoFactory $phpDocInfoFactory,
        PhpDocInfoPrinter $phpDocInfoPrinter,
        NamespaceAnalyzer $namespaceAnalyzer,
        TypeResolver $typeResolver
    ) {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
        $this->namespaceAnalyzer = $namespaceAnalyzer;
        $this->typeResolver = $typeResolver;
    }

    public function hasAnnotation(Node $node, string $annotation): bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFrom($node->getDocComment()->getText());

        return $phpDocInfo->hasTag($annotation);
    }

    public function removeAnnotationFromNode(Node $node, string $name, ?string $content = null): void
    {
        // no doc block? skip
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFrom($node->getDocComment()->getText());

        if ($content === null) {
            $phpDocInfo->removeTagByName($name);
        } elseif ($name === 'param') {
            $phpDocInfo->removeParamTagByParameter($content);
        }

        $docBlock = $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);
        $this->saveNewDocBlockToNode($node, $docBlock);
    }

    /**
     * @todo move to PhpDocInfo
     * @todo no need to check for nullable, just replace types
     * @todo check one service above, it
     */
    public function changeType(Node $node, string $oldType, string $newType): void
    {
        $this->node = $node;

        if ($node->getDocComment() === null) {
            return;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFrom($node->getDocComment()->getText());

        foreach ($phpDocInfo->getPhpDocNode()->children as $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if ($phpDocChildNode->value instanceof VarTagValueNode || $phpDocChildNode->value instanceof ParamTagValueNode || $phpDocChildNode->value instanceof ReturnTagValueNode) {
                $this->replacePhpDocType($phpDocChildNode->value, $oldType, $newType);
            }
        }

        $docBlock = $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);
        $this->saveNewDocBlockToNode($node, $docBlock);
    }

    public function replaceAnnotationInNode(Node $node, string $oldAnnotation, string $newAnnotation): void
    {
        if (! $node->getDocComment()) {
            return;
        }

        $oldContent = $node->getDocComment()->getText();

        $oldAnnotationPattern = preg_quote(sprintf('#@%s#', $oldAnnotation), '\\');

        $newContent = Strings::replace($oldContent, $oldAnnotationPattern, '@' . $newAnnotation, 1);

        $doc = new Doc($newContent);
        $node->setDocComment($doc);
    }

    /**
     * @return string[]|null
     */
    public function getVarTypes(Node $node): ?array
    {
        if ($node->getDocComment() === null) {
            return null;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFrom($node->getDocComment()->getText());

        $varTagValue = $phpDocInfo->getVarTagValue();
        if ($varTagValue === null) {
            return null;
        }

        $typesAsString = $this->typeResolver->resolveDocType($varTagValue->type);

        $fullyQualifiedTypes = [];
        foreach (explode('|', $typesAsString) as $type) {
            $fullyQualifiedTypes[] = $this->namespaceAnalyzer->resolveTypeToFullyQualified($type, $node);
        }

        return $fullyQualifiedTypes;
    }

    /**
     * @todo move to PhpDocInfo
     * @todo add test for Multi|Types
     */
    public function getTypeForParam(Node $node, string $paramName): ?string
    {
        // @todo should be array as well, use same approach as for @getVarTypes()
        if ($node->getDocComment() === null) {
            return null;
        }

        $paramTagsValues = $this->phpDocInfoFactory->createFrom($node->getDocComment()->getText())
            ->getParamTagValues();

        if (! count($paramTagsValues)) {
            return null;
        }

        foreach ($paramTagsValues as $paramTagsValue) {
            // @todo validate '$(name)'
            if ($paramTagsValue->parameterName === '$' . $paramName) {
                // @todo should be array of found types
                return (string) $paramTagsValue->type;
            }
        }

        // @todo local type resolve here!

        return null;
    }

    /**
     * @return PhpDocTagNode[]
     */
    public function getTagsByName(Node $node, string $name): array
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFrom($node->getDocComment()->getText());

        return $phpDocInfo->getTagsByName($name);
    }

    public function getTagByName(Node $node, string $name): ?PhpDocTagNode
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFrom($node->getDocComment()->getText());

        return $phpDocInfo->getTagsByName($name)[0] ?? null;
    }

    private function saveNewDocBlockToNode(Node $node, string $docBlock): void
    {
        // skip if has no doc comment
        if ($node->getDocComment() === null) {
            return;
        }

        if (empty($docBlock)) {
            $node->setAttribute('comments', null);
        } else {
            $node->setDocComment(new Doc($docBlock));
        }
    }

    /**
     * @param VarTagValueNode|ParamTagValueNode|ReturnTagValueNode $phpDocTagValueNode
     */
    private function replacePhpDocType(PhpDocTagValueNode $phpDocTagValueNode, string $oldType, string $newType): void
    {
        $phpDocTagValueNode->type = $this->replaceTypeNode($phpDocTagValueNode->type, $oldType, $newType);
    }

    /**
     * @todo move to PhpDocManipulator
     */
    private function replaceTypeNode(TypeNode $typeNode, string $oldType, string $newType): TypeNode
    {
        if ($typeNode instanceof UnionTypeNode) {
            foreach ($typeNode->types as $key => $subTypeNode) {
                $typeNode->types[$key] = $this->replaceTypeNode($subTypeNode, $oldType, $newType);
            }

            return $typeNode;
        }

        if ($typeNode instanceof IdentifierTypeNode) {
            $fqnType = $this->namespaceAnalyzer->resolveTypeToFullyQualified($typeNode->name, $this->node);
            if (is_a($fqnType, $oldType, true)) {
                return new IdentifierTypeNode($newType);
            }
        }

        return $typeNode;
    }
}
