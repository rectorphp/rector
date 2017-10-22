<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use Nette\Utils\Strings;
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

    public function removeAnnotationFromNode(Node $node, string $annotationName, string $annotationContent = ''): void
    {
        $docBlock = $this->createDocBlockFromNode($node);

        $annotations = $docBlock->getAnnotationsOfType($annotationName);

        foreach ($annotations as $annotation) {
            if ($annotationContent) {
                if (Strings::contains($annotation->getContent(), $annotationContent)) {
                    $annotation->remove();
                }
            } else {
                $annotation->remove();
            }
        }

        $this->saveNewDocBlockToNode($node, $docBlock);
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
                // @todo: resolve non-FQN names using namespace imports
                // $propertyNode->getAttribute(Attribute::USE_STATEMENTS)
                // maybe decouple to service?
                return implode('|', $annotationTags[0]->getTypes());
            }

            if ($type === 'deprecated') {
                $content = $annotationTags[0]->getContent();

                return trim(ltrim($content, '* @deprecated '));
            }
        }

        throw new NotImplementedException(sprintf(
            'Not implemented yet. Go to "%s()" and add check for "%s" annotation.',
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

    private function saveNewDocBlockToNode(Node $node, DocBlock $docBlock): void
    {
        $docContent = $docBlock->getContent();

        if (strlen($docBlock->getContent()) <= 7) {
            $docContent = '';
        }

        $doc = new Doc($docContent);
        $node->setDocComment($doc);
    }
}
