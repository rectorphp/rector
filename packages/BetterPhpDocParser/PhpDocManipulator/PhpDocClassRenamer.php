<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocManipulator;

use RectorPrefix202312\Nette\Utils\Strings;
use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\Renaming\Collector\RenamedNameCollector;
final class PhpDocClassRenamer
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher
     */
    private $classAnnotationMatcher;
    /**
     * @readonly
     * @var \Rector\Renaming\Collector\RenamedNameCollector
     */
    private $renamedNameCollector;
    public function __construct(ClassAnnotationMatcher $classAnnotationMatcher, RenamedNameCollector $renamedNameCollector)
    {
        $this->classAnnotationMatcher = $classAnnotationMatcher;
        $this->renamedNameCollector = $renamedNameCollector;
    }
    /**
     * Covers annotations like @ORM, @Serializer, @Assert etc
     * See https://github.com/rectorphp/rector/issues/1872
     *
     * @param string[] $oldToNewClasses
     */
    public function changeTypeInAnnotationTypes(Node $node, PhpDocInfo $phpDocInfo, array $oldToNewClasses, bool &$hasChanged) : bool
    {
        $this->processAssertChoiceTagValueNode($oldToNewClasses, $phpDocInfo, $hasChanged);
        $this->processDoctrineRelationTagValueNode($node, $oldToNewClasses, $phpDocInfo, $hasChanged);
        $this->processSerializerTypeTagValueNode($oldToNewClasses, $phpDocInfo, $hasChanged);
        return $hasChanged;
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function processAssertChoiceTagValueNode(array $oldToNewClasses, PhpDocInfo $phpDocInfo, bool &$hasChanged) : void
    {
        $assertChoiceDoctrineAnnotationTagValueNode = $phpDocInfo->findOneByAnnotationClass('Symfony\\Component\\Validator\\Constraints\\Choice');
        if (!$assertChoiceDoctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return;
        }
        $callbackArrayItemNode = $assertChoiceDoctrineAnnotationTagValueNode->getValue('callback');
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
        $classNameStringNode = $classNameArrayItemNode->value;
        if (!$classNameStringNode instanceof StringNode) {
            return;
        }
        foreach ($oldToNewClasses as $oldClass => $newClass) {
            if ($classNameStringNode->value !== $oldClass) {
                continue;
            }
            $this->renamedNameCollector->add($oldClass);
            $classNameStringNode->value = $newClass;
            // trigger reprint
            $classNameArrayItemNode->setAttribute(PhpDocAttributeKey::ORIG_NODE, null);
            $hasChanged = \true;
            break;
        }
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function processDoctrineRelationTagValueNode(Node $node, array $oldToNewClasses, PhpDocInfo $phpDocInfo, bool &$hasChanged) : void
    {
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClasses(['Doctrine\\ORM\\Mapping\\OneToMany', 'Doctrine\\ORM\\Mapping\\ManyToMany', 'Doctrine\\ORM\\Mapping\\Embedded']);
        if (!$doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return;
        }
        $this->processDoctrineToMany($doctrineAnnotationTagValueNode, $node, $oldToNewClasses, $hasChanged);
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function processSerializerTypeTagValueNode(array $oldToNewClasses, PhpDocInfo $phpDocInfo, bool &$hasChanged) : void
    {
        $doctrineAnnotationTagValueNode = $phpDocInfo->findOneByAnnotationClass('JMS\\Serializer\\Annotation\\Type');
        if (!$doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return;
        }
        $classNameArrayItemNode = $doctrineAnnotationTagValueNode->getSilentValue();
        foreach ($oldToNewClasses as $oldClass => $newClass) {
            if ($classNameArrayItemNode instanceof ArrayItemNode && $classNameArrayItemNode->value instanceof StringNode) {
                $classNameStringNode = $classNameArrayItemNode->value;
                if ($classNameStringNode->value === $oldClass) {
                    $classNameStringNode->value = $newClass;
                    continue;
                }
                $this->renamedNameCollector->add($oldClass);
                $classNameStringNode->value = Strings::replace($classNameStringNode->value, '#\\b' . \preg_quote($oldClass, '#') . '\\b#', $newClass);
                $classNameArrayItemNode->setAttribute(PhpDocAttributeKey::ORIG_NODE, null);
                $hasChanged = \true;
            }
            $currentTypeArrayItemNode = $doctrineAnnotationTagValueNode->getValue('type');
            if (!$currentTypeArrayItemNode instanceof ArrayItemNode) {
                continue;
            }
            $currentTypeStringNode = $currentTypeArrayItemNode->value;
            if (!$currentTypeStringNode instanceof StringNode) {
                continue;
            }
            if ($currentTypeStringNode->value === $oldClass) {
                $currentTypeStringNode->value = $newClass;
                $hasChanged = \true;
            }
        }
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function processDoctrineToMany(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, Node $node, array $oldToNewClasses, bool &$hasChanged) : void
    {
        $classKey = $doctrineAnnotationTagValueNode->hasClassName('Doctrine\\ORM\\Mapping\\Embedded') ? 'class' : 'targetEntity';
        $targetEntityArrayItemNode = $doctrineAnnotationTagValueNode->getValue($classKey);
        if (!$targetEntityArrayItemNode instanceof ArrayItemNode) {
            return;
        }
        $targetEntityStringNode = $targetEntityArrayItemNode->value;
        if (!$targetEntityStringNode instanceof StringNode) {
            return;
        }
        $targetEntityClass = $targetEntityStringNode->value;
        // resolve to FQN
        $tagFullyQualifiedName = $this->classAnnotationMatcher->resolveTagFullyQualifiedName($targetEntityClass, $node);
        foreach ($oldToNewClasses as $oldClass => $newClass) {
            if ($tagFullyQualifiedName !== $oldClass) {
                continue;
            }
            $this->renamedNameCollector->add($oldClass);
            $targetEntityStringNode->value = $newClass;
            $targetEntityArrayItemNode->setAttribute(PhpDocAttributeKey::ORIG_NODE, null);
            $hasChanged = \true;
        }
    }
}
