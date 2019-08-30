<?php declare(strict_types=1);

namespace Rector\Sensio\PhpDocParser;

use Doctrine\Common\Annotations\AnnotationException;
use Nette\Utils\Strings;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocParser\AbstractPhpDocParser;
use Rector\Sensio\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

final class SensioPhpDocTagParser extends AbstractPhpDocParser
{
    public function parse(TokenIterator $tokenIterator, string $tag): ?PhpDocTagValueNode
    {
        $currentPhpNode = $this->getCurrentPhpNode();

        // this is needed to append tokens to the end of annotation, even if not used
        $this->resolveAnnotationContent($tokenIterator);

        try {
            if ($currentPhpNode instanceof ClassMethod) {
                if ($tag === TemplateTagValueNode::SHORT_NAME) {
                    return $this->createTemplateTagValueNode($currentPhpNode);
                }
            }
        } catch (AnnotationException $annotationException) {
            // this not an annotation we look for, just having the same ending
            if (Strings::match(
                $annotationException->getMessage(),
                '#\[Semantical Error\] The annotation (.*) in (.*?) was never imported. ' .
                'Did you maybe forget to add a "use" statement for this annotation?#s'
            )
            ) {
                return null;
            }

            throw $annotationException;
        }

        return null;
    }

    private function createTemplateTagValueNode(ClassMethod $classMethod): TemplateTagValueNode
    {
        /** @var Template $templateAnnotation */
        $templateAnnotation = $this->nodeAnnotationReader->readMethodAnnotation(
            $classMethod,
            TemplateTagValueNode::CLASS_NAME
        );

        return new TemplateTagValueNode(
            $templateAnnotation->getTemplate(),
            $templateAnnotation->getOwner(),
            $templateAnnotation->getVars()
        );
    }
}
