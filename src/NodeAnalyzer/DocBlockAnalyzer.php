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

        if (count($annotationTags) === 1 && $annotationTags[0]->getTag()->getName() === 'var') {
            return implode('|' , $annotationTags[0]->getTypes());
        }

        throw new NotImplementedException(sprintf(
            'Not implemented yet. Go to "%s::%s()" and add check for "%s" annotation.',
            __CLASS__,
            __METHOD__,
            $annotation
        ));
    }

    private function createDocBlockFromNode(Node $node): DocBlock
    {
        return new DocBlock($node->getDocComment());
    }
}
