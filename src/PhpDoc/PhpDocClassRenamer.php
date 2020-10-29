<?php

declare(strict_types=1);

namespace Rector\Core\PhpDoc;

use PhpParser\Node;
use Rector\BetterPhpDocParser\Contract\Doctrine\DoctrineRelationTagValueNodeInterface;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS\SerializerTypeTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\Validator\Constraints\AssertChoiceTagValueNode;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class PhpDocClassRenamer
{
    /**
     * Covers annotations like @ORM, @Serializer, @Assert etc
     * See https://github.com/rectorphp/rector/issues/1872
     *
     * @param string[] $oldToNewClasses
     */
    public function changeTypeInAnnotationTypes(Node $node, array $oldToNewClasses): void
    {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        $this->processAssertChoiceTagValueNode($oldToNewClasses, $phpDocInfo);
        $this->processDoctrineRelationTagValueNode($oldToNewClasses, $phpDocInfo);
        $this->processSerializerTypeTagValueNode($oldToNewClasses, $phpDocInfo);
    }

    /**
     * @param string[] $oldToNewClasses
     */
    private function processAssertChoiceTagValueNode(array $oldToNewClasses, PhpDocInfo $phpDocInfo): void
    {
        $assertChoiceTagValueNode = $phpDocInfo->getByType(AssertChoiceTagValueNode::class);
        if (! $assertChoiceTagValueNode instanceof AssertChoiceTagValueNode) {
            return;
        }

        foreach ($oldToNewClasses as $oldClass => $newClass) {
            if (! $assertChoiceTagValueNode->isCallbackClass($oldClass)) {
                continue;
            }

            $assertChoiceTagValueNode->changeCallbackClass($newClass);
            break;
        }
    }

    /**
     * @param string[] $oldToNewClasses
     */
    private function processDoctrineRelationTagValueNode(array $oldToNewClasses, PhpDocInfo $phpDocInfo): void
    {
        $doctrineRelationTagValueNode = $phpDocInfo->getByType(DoctrineRelationTagValueNodeInterface::class);
        if (! $doctrineRelationTagValueNode instanceof DoctrineRelationTagValueNodeInterface) {
            return;
        }

        foreach ($oldToNewClasses as $oldClass => $newClass) {
            if ($doctrineRelationTagValueNode->getFullyQualifiedTargetEntity() !== $oldClass) {
                continue;
            }

            $doctrineRelationTagValueNode->changeTargetEntity($newClass);
            break;
        }
    }

    /**
     * @param string[] $oldToNewClasses
     */
    private function processSerializerTypeTagValueNode(array $oldToNewClasses, PhpDocInfo $phpDocInfo): void
    {
        $serializerTypeTagValueNode = $phpDocInfo->getByType(SerializerTypeTagValueNode::class);
        if (! $serializerTypeTagValueNode instanceof SerializerTypeTagValueNode) {
            return;
        }

        foreach ($oldToNewClasses as $oldClass => $newClass) {
            $serializerTypeTagValueNode->replaceName($oldClass, $newClass);
        }
    }
}
