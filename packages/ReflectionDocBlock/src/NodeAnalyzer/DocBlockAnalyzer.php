<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\NodeAnalyzer;

use Nette\Utils\Strings;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Serializer;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Deprecated;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\Types\Object_;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use Rector\ReflectionDocBlock\DocBlock\DocBlockFactory;
use Rector\ReflectionDocBlock\DocBlock\TidingSerializer;
use ReflectionProperty;

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

    public function __construct(DocBlockFactory $docBlockFactory, TidingSerializer $tidingSerializer)
    {
        $this->docBlockFactory = $docBlockFactory;
        $this->tidingSerializer = $tidingSerializer;
    }

    public function hasAnnotation(Node $node, string $annotation): bool
    {
        $docBlock = $this->docBlockFactory->createFromNode($node);

        return (bool) $docBlock->hasTag($annotation);
    }

    public function removeAnnotationFromNode(Node $node, string $annotationName, string $annotationContent = ''): void
    {
        $docBlock = $this->docBlockFactory->createFromNode($node);

        $annotations = $docBlock->getTagsByName($annotationName);

        $allAnnotations = $docBlock->getTags();
        $annotationsToRemove = [];

        foreach ($annotations as $annotation) {
            if ($annotationContent) {
                if (Strings::contains($annotation->render(), $annotationContent)) {
                    $annotationsToRemove[] = $annotation;

                    continue;
                }
            } else {
                $annotationsToRemove[] = $annotation;

                continue;
            }
        }

        $annotationsToKeep = array_diff($allAnnotations, $annotationsToRemove);

        $docBlock = $this->replaceDocBlockAnnotations($docBlock, $annotationsToKeep);

        $this->saveNewDocBlockToNode($node, $docBlock);
    }

    public function getVarTypes(Node $node): ?string
    {
        /** @var Var_[] $varTags */
        $varTags = $this->getTagsByName($node, 'var');
        if ($varTags === null) {
            return null;
        }

        return ltrim((string) $varTags[0]->getType(), '\\');
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
        /** @var Param[] $paramTags */
        $paramTags = $this->getTagsByName($node, 'param');
        if ($paramTags === null) {
            return null;
        }

        foreach ($paramTags as $paramTag) {
            if ($paramTag->getVariableName() === $paramName) {
                $type = $paramTag->getType();
                if ($type instanceof Object_) {
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
     * Magic untill it is possible to remove tag
     * https://github.com/phpDocumentor/ReflectionDocBlock/issues/124
     *
     * @param Tag[] $annnotations
     */
    private function replaceDocBlockAnnotations(DocBlock $docBlock, array $annnotations): DocBlock
    {
        $tagsPropertyReflection = new ReflectionProperty(get_class($docBlock), 'tags');
        $tagsPropertyReflection->setAccessible(true);
        $tagsPropertyReflection->setValue($docBlock, $annnotations);

        return $docBlock;
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
}
