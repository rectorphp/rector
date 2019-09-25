<?php declare(strict_types=1);

namespace Rector\PhpDoc;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\DoctrineRelationTagValueNodeInterface;
use Rector\Symfony\PhpDocParser\Ast\PhpDoc\AssertChoiceTagValueNode;
use Rector\Symfony\PhpDocParser\Ast\PhpDoc\SerializerTypeTagValueNode;

final class PhpDocClassRenamer
{
    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var PhpDocInfoPrinter
     */
    private $phpDocInfoPrinter;

    /**
     * @var bool
     */
    private $shouldUpdate = false;

    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, PhpDocInfoPrinter $phpDocInfoPrinter)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
    }

    /**
     * Covers annotations like @ORM, @Serializer, @Assert etc
     * See https://github.com/rectorphp/rector/issues/1872
     *
     * @param string[] $oldToNewClasses
     */
    public function changeTypeInAnnotationTypes(Node $node, array $oldToNewClasses): void
    {
        $docComment = $node->getDocComment();
        if ($docComment === null) {
            return;
        }

        $this->shouldUpdate = false;

        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        $this->procesAssertChoiceTagValueNode($oldToNewClasses, $phpDocInfo);
        $this->procesDoctrineRelationTagValueNode($oldToNewClasses, $phpDocInfo);
        $this->processSerializerTypeTagValueNode($oldToNewClasses, $phpDocInfo);

        if ($this->shouldUpdate === false) {
            return;
        }

        $textDocComment = $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);
        $node->setDocComment(new Doc($textDocComment));
    }

    /**
     * @param string[] $oldToNewClasses
     */
    private function procesAssertChoiceTagValueNode(array $oldToNewClasses, PhpDocInfo $phpDocInfo): void
    {
        $choiceTagValueNode = $phpDocInfo->getByType(AssertChoiceTagValueNode::class);
        if (! $choiceTagValueNode instanceof AssertChoiceTagValueNode) {
            return;
        }

        foreach ($oldToNewClasses as $oldClass => $newClass) {
            if (! $choiceTagValueNode->isCallbackClass($oldClass)) {
                continue;
            }

            $choiceTagValueNode->changeCallbackClass($newClass);
            $this->shouldUpdate = true;
            break;
        }
    }

    /**
     * @param string[] $oldToNewClasses
     */
    private function procesDoctrineRelationTagValueNode(array $oldToNewClasses, PhpDocInfo $phpDocInfo): void
    {
        $relationTagValueNode = $phpDocInfo->getByType(DoctrineRelationTagValueNodeInterface::class);
        if (! $relationTagValueNode instanceof DoctrineRelationTagValueNodeInterface) {
            return;
        }

        foreach ($oldToNewClasses as $oldClass => $newClass) {
            if ($relationTagValueNode->getFqnTargetEntity() !== $oldClass) {
                continue;
            }

            $relationTagValueNode->changeTargetEntity($newClass);
            $this->shouldUpdate = true;
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
            if ($serializerTypeTagValueNode->replaceName($oldClass, $newClass)) {
                $this->shouldUpdate = true;
            }
        }
    }
}
