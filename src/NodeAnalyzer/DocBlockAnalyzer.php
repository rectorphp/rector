<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpCsFixer\DocBlock\DocBlock;
use PhpParser\Comment\Doc;
use PhpParser\Node;

final class DocBlockAnalyzer
{
    public function hasAnnotation(Node $node, string $annotation): bool
    {
        $docBlock = $this->createDocBlockFromNode($node);

        return (bool) $docBlock->getAnnotationsOfType($annotation);
    }

    public function removeAnnotationFromNode(Node $node, string $annotation): Node
    {
        $docBlock = $this->createDocBlockFromNode($node);

        $annotations = $docBlock->getAnnotationsOfType($annotation);
        foreach ($annotations as $injectAnnotation) {
            $injectAnnotation->remove();
        }

        $node->setDocComment(new Doc($docBlock->getContent()));

        return $node;
    }

    private function createDocBlockFromNode(Node $node): DocBlock
    {
        return new DocBlock($node->getDocComment());
    }
}
