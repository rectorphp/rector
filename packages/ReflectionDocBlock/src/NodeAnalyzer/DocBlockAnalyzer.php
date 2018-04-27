<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\NodeAnalyzer;

use Nette\Utils\Strings;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\Exception\NotImplementedException;
use Rector\ReflectionDocBlock\DocBlock\AnnotationRemover;
use Rector\ReflectionDocBlock\DocBlock\DocBlockFactory;
use Rector\ReflectionDocBlock\DocBlock\TidingSerializer;
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
     * @var TidingSerializer
     */
    private $tidingSerializer;

    /**
     * @var AnnotationRemover
     */
    private $annotationRemover;

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

    public function __construct(
        DocBlockFactory $docBlockFactory,
        TidingSerializer $tidingSerializer,
        AnnotationRemover $annotationRemover,
        PhpDocInfoFactory $phpDocInfoFactory,
        PrivatesAccessor $privatesAccessor,
        PhpDocInfoPrinter $phpDocInfoPrinter
    ) {
        $this->docBlockFactory = $docBlockFactory;
        $this->tidingSerializer = $tidingSerializer;
        $this->annotationRemover = $annotationRemover;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->privatesAccessor = $privatesAccessor;
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
    }

    public function hasAnnotation(Node $node, string $annotation): bool
    {
        $docBlock = $this->docBlockFactory->createFromNode($node);

        return $docBlock->hasTag($annotation);
    }

    public function removeAnnotationFromNode(Node $node, string $name, string $content = ''): void
    {
        $docBlock = $this->docBlockFactory->createFromNode($node);

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

//        dump($phpDocNode);
        $docBlock = $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);
//        die;

//        $docBlock = $this->annotationRemover->removeFromDocBlockByNameAndContent($docBlock, $name, $content);
        $this->saveNewDocBlockToNode($node, $docBlock);
    }

    public function renameNullable(Node $node, string $oldType, string $newType): void
    {
        $docBlock = $this->docBlockFactory->createFromNode($node);

        foreach ($docBlock->getTags() as $tag) {
            if ($tag instanceof TolerantVar) {
                // this could be abstracted to replace values
                if ($tag->getType() instanceof Compound) {
                    $this->processCompoundTagType($node, $docBlock, $tag, $tag->getType(), $oldType, $newType);
                }
            }
        }

        // is this still needed?
        $this->replaceInNode($node, sprintf('%s|null', $oldType), sprintf('%s|null', $newType));
        $this->replaceInNode($node, sprintf('null|%s', $oldType), sprintf('null|%s', $newType));
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

    private function replaceInNode(Node $node, string $old, string $new): void
    {
        if (! $node->getDocComment()) {
            return;
        }

        $docComment = $node->getDocComment();
        $content = $docComment->getText();

        $newContent = Strings::replace($content, '#' . preg_quote($old, '#') . '#', $new, 1);

        $doc = new Doc($newContent);
        $node->setDocComment($doc);
    }

    private function saveNewDocBlockToNode(Node $node, string $docBlock): void
    {
        // skip if has no doc comment
        if ($node->getDocComment() === null) {
            return;
        }

        $node->setDocComment(new Doc($docBlock));
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

    private function processCompoundTagType(
        Node $node,
        DocBlock $docBlock,
        TolerantVar $tolerantVar,
        Compound $compound,
        string $oldType,
        string $newType
    ): void {
        $newCompoundTagTypes = [];

        foreach ($compound as $i => $oldTagSubType) {
            if ($oldTagSubType instanceof Object_) {
                $oldTagValue = (string) $oldTagSubType->getFqsen();

                // is this value object to be replaced?
                if (is_a($oldTagValue, $oldType, true)) {
                    $newCompoundTagTypes[] = $this->resolveNewTypeObjectFromString($newType);
                    continue;
                }
            }

            $newCompoundTagTypes[] = $oldTagSubType;
        }

        // nothing to replace
        if (! count($newCompoundTagTypes)) {
            return;
        }

        // use this as new type
        $newCompoundTag = new Compound($newCompoundTagTypes);
        $this->privatesAccessor->setPrivateProperty($tolerantVar, 'type', $newCompoundTag);
        $this->saveNewDocBlockToNode($node, $docBlock);
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
}
