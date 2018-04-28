<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\NodeAnalyzer;

use Nette\Utils\Strings;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\Exception\NotImplementedException;
use Rector\ReflectionDocBlock\DocBlock\DocBlockFactory;
use Symplify\BetterPhpDocParser\PhpDocParser\PhpDocInfo;
use Symplify\BetterPhpDocParser\PhpDocParser\PhpDocInfoFactory;
use Symplify\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Symplify\BetterReflectionDocBlock\Tag\TolerantVar;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;

final class DocBlockAnalyzer
{
    /**
     * @var string[]
     */
    private $typesToObjects = [
        'string' => String_::class,
        'int' => Integer::class,
        'bool' => Boolean::class,
        'null' => Null_::class,
    ];

    /**
     * @var DocBlockFactory
     */
    private $docBlockFactory;

    /**
     * @var PrivatesAccessor
     */
    private $privatesAccessor;

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

    public function __construct(
        DocBlockFactory $docBlockFactory,
        PhpDocInfoFactory $phpDocInfoFactory,
        PrivatesAccessor $privatesAccessor,
        PhpDocInfoPrinter $phpDocInfoPrinter,
        NamespaceAnalyzer $namespaceAnalyzer
    ) {
        $this->docBlockFactory = $docBlockFactory;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->privatesAccessor = $privatesAccessor;
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
        $this->namespaceAnalyzer = $namespaceAnalyzer;
    }

    public function hasAnnotation(Node $node, string $annotation): bool
    {
        $docBlock = $this->docBlockFactory->createFromNode($node);

        return $docBlock->hasTag($annotation);
    }

    /**
     * @todo move to PhpDocInfo
     */
    public function removeAnnotationFromNode(Node $node, string $name, string $content = ''): void
    {
        // no doc block? skip
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFrom($node->getDocComment()->getText());

        // add PhpDocInfoManipulator ? - add logic to the Symplify core, starting with test first
        $phpDocNode = $phpDocInfo->getPhpDocNode();

        $tagsByName = $phpDocNode->getTagsByName('@' . $name);

        foreach ($tagsByName as $tagByName) {
            if ($content) {
                if ($tagByName->value === $content) {
                    $this->removeTagFromPhpDocNode($phpDocNode, $tagByName);
                }
            } else {
                $this->removeTagFromPhpDocNode($phpDocNode, $tagByName);
            }
        }

        $docBlock = $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);

        $this->saveNewDocBlockToNode($node, $docBlock);
    }

    /**
     * @todo move to PhpDocInfo
     * @todo no need to check for nullable, just replace types
     * @todo check one service above, it
     */
    public function renameNullable(Node $node, string $oldType, string $newType): void
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
        /** @var TolerantVar[] $varTags */
        $varTags = $this->getTagsByName($node, 'var');
        if (! count($varTags)) {
            return null;
        }

        $varTag = array_shift($varTags);

        $types = explode('|', (string) $varTag);

        return $this->normalizeTypes($types);
    }

    public function getTypeForParam(Node $node, string $paramName): ?string
    {
        // @todo should be array as well, use same approach as for @getVarTypes()

        /** @var Param[] $paramTags */
        $paramTags = $this->getTagsByName($node, 'param');
        if (! count($paramTags)) {
            return null;
        }

        foreach ($paramTags as $paramTag) {
            if ($paramTag->getVariableName() === $paramName) {
                $type = $paramTag->getType();
                if ($type instanceof Object_ && $type->getFqsen()) {
                    return $type->getFqsen()->getName();
                }
            }
        }

        return null;
    }

    /**
     * @return Tag[]|string[]
     */
    public function getTagsByName(Node $node, string $name): array
    {
        $docBlock = $this->docBlockFactory->createFromNode($node);

        return $docBlock->getTagsByName($name);
    }

    public function replaceVarType(Node $node, string $to): void
    {
        $docBlock = $this->docBlockFactory->createFromNode($node);

        $tags = $docBlock->getTags();
        foreach ($tags as $tag) {
            if (! $tag instanceof TolerantVar) {
                continue;
            }

            $newType = $this->resolveNewTypeObjectFromString($to);

            $this->privatesAccessor->setPrivateProperty($tag, 'type', $newType);

            break;
        }

        $this->saveNewDocBlockToNode($node, $docBlock);
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
     * @param string[] $types
     * @return string[]
     */
    private function normalizeTypes(array $types): array
    {
        // remove preslash: {\]SomeClass
        $types = array_map(function (string $type) {
            return ltrim(trim($type), '\\');
        }, $types);

        // remove arrays: Type{[][][]}
        $types = array_map(function (string $type) {
            return rtrim($type, '[]');
        }, $types);

        return $types;
    }

    private function resolveNewTypeObjectFromString(string $type): Type
    {
        if (isset($this->typesToObjects[$type])) {
            return new $this->typesToObjects[$type]();
        }

        throw new NotImplementedException(__METHOD__);
    }

    /**
     * @todo move to PhpDocInfo
     */
    private function removeTagFromPhpDocNode(PhpDocNode $phpDocNode, PhpDocTagNode $phpDocTagNode): void
    {
        foreach ($phpDocNode->children as $key => $phpDocChildNode) {
            if ($phpDocChildNode === $phpDocTagNode) {
                unset($phpDocNode->children[$key]);
            }
        }
    }

    /**
     * @param VarTagValueNode|ParamTagValueNode|ReturnTagValueNode $phpDocTagValueNode
     */
    private function replacePhpDocType(PhpDocTagValueNode $phpDocTagValueNode, string $oldType, string $newType): void
    {
        $phpDocTagValueNode->type = $this->replaceTypeNode($phpDocTagValueNode->type, $oldType, $newType);
    }

    private function replaceTypeNode(TypeNode $typeNode, string $oldType, string $newType): TypeNode
    {
        if ($typeNode instanceof UnionTypeNode) {
            foreach ($typeNode->types as $key => $subTypeNode) {
                $typeNode->types[$key] = $this->replaceTypeNode($subTypeNode, $oldType, $newType);
            }

            return $typeNode;
        }

        if ($typeNode instanceof IdentifierTypeNode) {
            $fqnType = $this->namespaceAnalyzer->resolveTypeToFullyQualified([$typeNode->name], $this->node);
            if (is_a($fqnType, $oldType, true)) {
                return new IdentifierTypeNode($newType);
            }
        }

        return $typeNode;
    }
}
