<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocManipulator;

use RectorPrefix202211\Nette\Utils\Strings;
use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
final class PhpDocClassRenamer
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher
     */
    private $classAnnotationMatcher;
    public function __construct(ClassAnnotationMatcher $classAnnotationMatcher)
    {
        $this->classAnnotationMatcher = $classAnnotationMatcher;
    }
    /**
     * Covers annotations like @ORM, @Serializer, @Assert etc
     * See https://github.com/rectorphp/rector/issues/1872
     *
     * @param string[] $oldToNewClasses
     */
    public function changeTypeInAnnotationTypes(Node $node, PhpDocInfo $phpDocInfo, array $oldToNewClasses) : void
    {
        $this->processAssertChoiceTagValueNode($oldToNewClasses, $phpDocInfo);
        $this->processDoctrineRelationTagValueNode($node, $oldToNewClasses, $phpDocInfo);
        $this->processSerializerTypeTagValueNode($oldToNewClasses, $phpDocInfo);
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function processAssertChoiceTagValueNode(array $oldToNewClasses, PhpDocInfo $phpDocInfo) : void
    {
        $assertChoiceTagValueNode = $phpDocInfo->findOneByAnnotationClass('Symfony\\Component\\Validator\\Constraints\\Choice');
        if (!$assertChoiceTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return;
        }
        $callbackArrayItemNode = $assertChoiceTagValueNode->getValue('callback');
        if (!$callbackArrayItemNode instanceof ArrayItemNode) {
            return;
        }
        $callbackClass = $callbackArrayItemNode->value;
        // array is needed for callable
        if (!$callbackClass instanceof CurlyListNode) {
            return;
        }
        $callableCallbackArrayItems = $callbackClass->getValues();
        $classNameArrayItemNode = $callableCallbackArrayItems[0];
        foreach ($oldToNewClasses as $oldClass => $newClass) {
            if ($classNameArrayItemNode->value !== $oldClass) {
                continue;
            }
            $classNameArrayItemNode->value = $newClass;
            // trigger reprint
            $classNameArrayItemNode->setAttribute(PhpDocAttributeKey::ORIG_NODE, null);
            break;
        }
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function processDoctrineRelationTagValueNode(Node $node, array $oldToNewClasses, PhpDocInfo $phpDocInfo) : void
    {
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClasses(['Doctrine\\ORM\\Mapping\\OneToMany', 'Doctrine\\ORM\\Mapping\\ManyToMany', 'Doctrine\\ORM\\Mapping\\Embedded']);
        if (!$doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return;
        }
        $this->processDoctrineToMany($doctrineAnnotationTagValueNode, $node, $oldToNewClasses);
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function processSerializerTypeTagValueNode(array $oldToNewClasses, PhpDocInfo $phpDocInfo) : void
    {
        $doctrineAnnotationTagValueNode = $phpDocInfo->findOneByAnnotationClass('JMS\\Serializer\\Annotation\\Type');
        if (!$doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return;
        }
        $classNameArrayItemNode = $doctrineAnnotationTagValueNode->getSilentValue();
        foreach ($oldToNewClasses as $oldClass => $newClass) {
            if ($classNameArrayItemNode instanceof ArrayItemNode) {
                if ($classNameArrayItemNode->value === $oldClass) {
                    $classNameArrayItemNode->value = $newClass;
                    continue;
                }
                $classNameArrayItemNode->value = Strings::replace($classNameArrayItemNode->value, '#\\b' . \preg_quote($oldClass, '#') . '\\b#', $newClass);
                $classNameArrayItemNode->setAttribute(PhpDocAttributeKey::ORIG_NODE, null);
            }
            $currentTypeArrayItemNode = $doctrineAnnotationTagValueNode->getValue('type');
            if (!$currentTypeArrayItemNode instanceof ArrayItemNode) {
                continue;
            }
            if ($currentTypeArrayItemNode->value === $oldClass) {
                $currentTypeArrayItemNode->value = $newClass;
            }
        }
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function processDoctrineToMany(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, Node $node, array $oldToNewClasses) : void
    {
        $classKey = $doctrineAnnotationTagValueNode->hasClassName('Doctrine\\ORM\\Mapping\\Embedded') ? 'class' : 'targetEntity';
        $targetEntityArrayItemNode = $doctrineAnnotationTagValueNode->getValue($classKey);
        if (!$targetEntityArrayItemNode instanceof ArrayItemNode) {
            return;
        }
        $targetEntityClass = $targetEntityArrayItemNode->value;
        // resolve to FQN
        $tagFullyQualifiedName = $this->classAnnotationMatcher->resolveTagFullyQualifiedName($targetEntityClass, $node);
        foreach ($oldToNewClasses as $oldClass => $newClass) {
            if ($tagFullyQualifiedName !== $oldClass) {
                continue;
            }
            $targetEntityArrayItemNode->value = $newClass;
            $targetEntityArrayItemNode->setAttribute(PhpDocAttributeKey::ORIG_NODE, null);
        }
    }
}
