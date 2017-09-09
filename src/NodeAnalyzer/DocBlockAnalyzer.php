<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpCsFixer\DocBlock\DocBlock;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use Rector\Exception\NotImplementedException;

final class DocBlockAnalyzer
{
    public function hasAnnotation(Node $node, string $annotation): bool
    {
        $docBlock = $this->createDocBlockFromNode($node);

        return (bool) $docBlock->getAnnotationsOfType($annotation);
    }

    public function removeAnnotationFromNode(Node $node, string $annotation): void
    {
        $docBlock = $this->createDocBlockFromNode($node);

        $annotations = $docBlock->getAnnotationsOfType($annotation);
        foreach ($annotations as $injectAnnotation) {
            $injectAnnotation->remove();
        }

        $node->setDocComment(new Doc($docBlock->getContent()));
    }

    public function getAnnotationFromNode(Node $node, string $annotation): string
    {
        $docBlock = new DocBlock($node->getDocComment());

        $annotationTags = $docBlock->getAnnotationsOfType($annotation);
        if (count($annotationTags) === 0) {
            return '';
        }

        if (count($annotationTags) === 1) {
            $type = $annotationTags[0]->getTag()->getName();
            if ($type === 'var') {
                return implode('|', $annotationTags[0]->getTypes());
            }

            if ($type === 'deprecated') {
                $content = $annotationTags[0]->getContent();

                return ltrim($content, '* @deprecated ');
            }
        }

        throw new NotImplementedException(sprintf(
            'Not implemented yet. Go to "%s::%s()" and add check for "%s" annotation.',
            __CLASS__,
            __METHOD__,
            $annotation
        ));
    }

    public function getDeprecatedDocComment(Node $node): ?string
    {
        $docBlock = new DocBlock($node->getDocComment());
        $deprecatedTag = $docBlock->getAnnotationsOfType('deprecated');
        if (count($deprecatedTag) === 0) {
            return null;
        }

        $comment = $deprecatedTag[0]->getContent();

        return preg_replace('/[[:blank:]]+/', ' ', $comment);
    }

    private function createDocBlockFromNode(Node $node): DocBlock
    {
        return new DocBlock($node->getDocComment());
    }
}
