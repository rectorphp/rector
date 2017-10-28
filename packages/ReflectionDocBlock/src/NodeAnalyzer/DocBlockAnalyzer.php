<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\NodeAnalyzer;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Deprecated;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\Types\Object_;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use Rector\ReflectionDocBlock\DocBlock\AnnotationRemover;
use Rector\ReflectionDocBlock\DocBlock\DocBlockFactory;
use Rector\ReflectionDocBlock\DocBlock\TidingSerializer;

/**
 * @todo Make use of phpdocumentor/type-resolver, to return FQN names
 * @see https://github.com/Roave/BetterReflection/blob/a6f46b13307f751a0123ad3b830db2105f263867/src/TypesFinder/FindPropertyType.php#L52
 */
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

        return (bool) $docBlock->hasTag($annotation);
    }

    public function removeAnnotationFromNode(Node $node, string $name, string $content = ''): void
    {
        $docBlock = $this->docBlockFactory->createFromNode($node);
        $docBlock = $this->annotationRemover->removeFromDocBlockByNameAndContent($docBlock, $name, $content);
        $this->saveNewDocBlockToNode($node, $docBlock);
    }

    /**
     * @return string[]|null
     */
    public function getVarTypes(Node $node): ?array
    {
        /** @var Var_[] $varTags */
        $varTags = $this->getTagsByName($node, 'var');
        if ($varTags === null) {
            return null;
        }

        $varTag = array_shift($varTags);

        $types = explode('|', (string) $varTag);
        $types = $this->normalizeTypes($types);

        return $types;
    }

    public function getDeprecatedDocComment(Node $node): ?string
    {
        /** @var Deprecated[] $deprecatedTags */
        $deprecatedTags = $this->getTagsByName($node, 'deprecated');
        if ($deprecatedTags === null) {
            return null;
        }

        if ($deprecatedTags[0]->getDescription()) {
            return $deprecatedTags[0]->getDescription()
                ->render();
        }

        return $deprecatedTags[0]->getName();
    }

    public function getTypeForParam(Node $node, string $paramName): ?string
    {
        // @todo should be array as well, use same approach as for @getVarTypes()

        /** @var Param[] $paramTags */
        $paramTags = $this->getTagsByName($node, 'param');
        if ($paramTags === null) {
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

    private function saveNewDocBlockToNode(Node $node, DocBlock $docBlock): void
    {
        $docContent = $this->tidingSerializer->getDocComment($docBlock);
        $doc = new Doc($docContent);
        $node->setDocComment($doc);
    }

    /**
     * @return Tag[]|null
     */
    private function getTagsByName(Node $node, string $name): ?array
    {
        $docBlock = $this->docBlockFactory->createFromNode($node);

        $tags = $docBlock->getTagsByName($name);
        if (count($tags) === 0) {
            return null;
        }

        return $tags;
    }

    /**
     * @param string[] $types
     * @return string[]
     */
    private function normalizeTypes(array $types): array
    {
        return array_map(function (string $type) {
            return ltrim(trim($type), '\\');
        }, $types);
    }
}
