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
     * @var Serializer
     */
    private $serializer;

    public function __construct(DocBlockFactory $docBlockFactory, Serializer $serializer)
    {
        $this->docBlockFactory = $docBlockFactory;
        $this->serializer = $serializer;
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

        $annotationsToKeep = [];

        foreach ($annotations as $annotation) {
            if ($annotationContent) {
                if (Strings::contains($annotation->render(), $annotationContent)) {
                    continue;
                }
            } else {
                continue;
            }

            $annotationsToKeep[] = $annotation;
        }

        $docBlock = $this->replaceDocBlockAnnotations($docBlock, $annotationsToKeep);

        $this->saveNewDocBlockToNode($node, $docBlock);
    }

    public function getVarTypes(Node $node): ?string
    {
        $docBlock = $this->docBlockFactory->createFromNode($node);

        /** @var Var_[] $varTags */
        $varTags = $docBlock->getTagsByName('var');
        if (count($varTags) === 0) {
            return null;
        }

        return (string) $varTags[0]->getType();
    }

    public function getDeprecatedDocComment(Node $node): ?string
    {
        $docBlock = $this->docBlockFactory->createFromNode($node);

        /** @var Deprecated[] $deprecatedTags */
        $deprecatedTags = $docBlock->getTagsByName('deprecated');
        if (count($deprecatedTags) === 0) {
            return null;
        }

        return $deprecatedTags[0]->getDescription()->render();
    }

    public function getParamTypeFor(Node $node, string $paramName): ?string
    {
        $docBlock = $this->docBlockFactory->createFromNode($node);

        /** @var Param[] $paramTags */
        $paramTags = $docBlock->getTagsByName('param');
        if (count($paramTags) === 0) {
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
        $docContent = $this->serializer->getDocComment($docBlock);

        if ($this->isDocContentEmpty($docContent)) {
            $docContent = '';
        }

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
     * Inspiration https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/b23b5e0a4877a1c97d58f260ea0eb66fbff30e04/Symfony/CS/Fixer/Symfony/NoEmptyPhpdocFixer.php#L35
     */
    private function isDocContentEmpty(string $docContent): bool
    {
        return (bool) preg_match('#^/\*\*[\s\*]*\*/$#', $docContent);
    }
}
