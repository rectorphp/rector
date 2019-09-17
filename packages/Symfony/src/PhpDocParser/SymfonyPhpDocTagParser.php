<?php declare(strict_types=1);

namespace Rector\Symfony\PhpDocParser;

use JMS\Serializer\Annotation\Type;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocParser\AbstractPhpDocParser;
use Rector\Symfony\PhpDocParser\Ast\PhpDoc\AssertChoiceTagValueNode;
use Rector\Symfony\PhpDocParser\Ast\PhpDoc\AssertTypeTagValueNode;
use Rector\Symfony\PhpDocParser\Ast\PhpDoc\SerializerTypeTagValueNode;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Type as ValidatorType;

final class SymfonyPhpDocTagParser extends AbstractPhpDocParser
{
    public function parse(TokenIterator $tokenIterator, string $tag): ?PhpDocTagValueNode
    {
        $currentPhpNode = $this->getCurrentPhpNode();

        // this is needed to append tokens to the end of annotation, even if not used
        $annotationContent = $this->resolveAnnotationContent($tokenIterator);
        if ($currentPhpNode instanceof Property) {
            if ($tag === AssertChoiceTagValueNode::SHORT_NAME) {
                return $this->createAssertChoiceTagValueNode($currentPhpNode, $annotationContent);
            }

            if ($tag === AssertTypeTagValueNode::SHORT_NAME) {
                return $this->createAssertTypeTagValueNode($currentPhpNode);
            }

            if ($tag === SerializerTypeTagValueNode::SHORT_NAME) {
                return $this->createSerializerTypeTagValueNode($currentPhpNode, $annotationContent);
            }
        }

        return null;
    }

    private function createAssertChoiceTagValueNode(
        Property $property,
        string $annotationContent
    ): AssertChoiceTagValueNode {
        /** @var Choice $choiceAnnotation */
        $choiceAnnotation = $this->nodeAnnotationReader->readPropertyAnnotation(
            $property,
            AssertChoiceTagValueNode::CLASS_NAME
        );

        return new AssertChoiceTagValueNode($choiceAnnotation->callback, $choiceAnnotation->strict, $annotationContent);
    }

    private function createSerializerTypeTagValueNode(
        Property $property,
        string $annotationContent
    ): SerializerTypeTagValueNode {
        /** @var Type $typeAnnotation */
        $typeAnnotation = $this->nodeAnnotationReader->readPropertyAnnotation(
            $property,
            SerializerTypeTagValueNode::CLASS_NAME
        );

        return new SerializerTypeTagValueNode($typeAnnotation->name, $annotationContent);
    }

    private function createAssertTypeTagValueNode(Property $property): AssertTypeTagValueNode
    {
        /** @var ValidatorType $typeAnnotation */
        $typeAnnotation = $this->nodeAnnotationReader->readPropertyAnnotation(
            $property,
            AssertTypeTagValueNode::CLASS_NAME
        );

        return new AssertTypeTagValueNode($typeAnnotation->type);
    }
}
