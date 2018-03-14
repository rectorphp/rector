<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\NodeAnalyzer;

use Nette\Utils\Strings;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use Rector\Exception\NotImplementedException;
use Rector\ReflectionDocBlock\DocBlock\AnnotationRemover;
use Rector\ReflectionDocBlock\DocBlock\DocBlockFactory;
use Rector\ReflectionDocBlock\DocBlock\TidingSerializer;
use ReflectionProperty;
use Symplify\BetterReflectionDocBlock\Tag\TolerantVar;

final class DocBlockAnalyzer
{
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

    public function __construct(
        DocBlockFactory $docBlockFactory,
        TidingSerializer $tidingSerializer,
        AnnotationRemover $annotationRemover
    ) {
        $this->docBlockFactory = $docBlockFactory;
        $this->tidingSerializer = $tidingSerializer;
        $this->annotationRemover = $annotationRemover;
    }

    public function hasAnnotation(Node $node, string $annotation): bool
    {
        $docBlock = $this->docBlockFactory->createFromNode($node);

        return $docBlock->hasTag($annotation);
    }

    public function removeAnnotationFromNode(Node $node, string $name, string $content = ''): void
    {
        $docBlock = $this->docBlockFactory->createFromNode($node);
        $docBlock = $this->annotationRemover->removeFromDocBlockByNameAndContent($docBlock, $name, $content);
        $this->saveNewDocBlockToNode($node, $docBlock);
    }

    public function renameNullable(Node $node, string $oldType, string $newType): void
    {
        $docBlock = $this->docBlockFactory->createFromNode($node);

        foreach ($docBlock->getTags() as $tag) {
            if ($tag instanceof TolerantVar) { // @todo: use own writeable Var
                dump($tag);
                die;
            }
        }

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
        $types = $this->normalizeTypes($types);

        return $types;
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

            $this->setPrivatePropertyValue($tag, 'type', $newType);

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

    private function saveNewDocBlockToNode(Node $node, DocBlock $docBlock): void
    {
        $docContent = $this->tidingSerializer->getDocComment($docBlock);
        $doc = new Doc($docContent);
        $node->setDocComment($doc);
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

    /**
     * Magic untill, since object is read only
     *
     * @param object $object
     * @param mixed $value
     */
    private function setPrivatePropertyValue($object, string $name, $value): void
    {
        $propertyReflection = new ReflectionProperty(get_class($object), $name);
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($object, $value);
    }

    private function resolveNewTypeObjectFromString(string $type): Type
    {
        if ($type === 'string') {
            return new String_();
        }

        if ($type === 'int') {
            return new Integer();
        }

        if ($type === 'bool') {
            return new Boolean();
        }

        throw new NotImplementedException(__METHOD__);
    }
}
