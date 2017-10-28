<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\DocBlock;

use Nette\Utils\Strings;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;
use ReflectionProperty;

final class AnnotationRemover
{
    public function removeFromDocBlockByNameAndContent(DocBlock $docBlock, string $name, string $content): DocBlock
    {
        $annotations = $docBlock->getTagsByName($name);

        $allAnnotations = $docBlock->getTags();
        $annotationsToRemove = [];

        foreach ($annotations as $annotation) {
            if ($content) {
                if (Strings::contains($annotation->render(), $content)) {
                    $annotationsToRemove[] = $annotation;

                    continue;
                }
            } else {
                $annotationsToRemove[] = $annotation;

                continue;
            }
        }

        $annotationsToKeep = array_diff($allAnnotations, $annotationsToRemove);

        return $this->replaceDocBlockAnnotations($docBlock, $annotationsToKeep);
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
}
