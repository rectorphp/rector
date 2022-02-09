<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocManipulator;

use RectorPrefix20220209\Nette\Utils\Strings;
use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
final class PhpDocClassRenamer
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher
     */
    private $classAnnotationMatcher;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher $classAnnotationMatcher)
    {
        $this->classAnnotationMatcher = $classAnnotationMatcher;
    }
    /**
     * Covers annotations like @ORM, @Serializer, @Assert etc
     * See https://github.com/rectorphp/rector/issues/1872
     *
     * @param string[] $oldToNewClasses
     */
    public function changeTypeInAnnotationTypes(\PhpParser\Node $node, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, array $oldToNewClasses) : void
    {
        $this->processAssertChoiceTagValueNode($oldToNewClasses, $phpDocInfo);
        $this->processDoctrineRelationTagValueNode($node, $oldToNewClasses, $phpDocInfo);
        $this->processSerializerTypeTagValueNode($oldToNewClasses, $phpDocInfo);
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function processAssertChoiceTagValueNode(array $oldToNewClasses, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : void
    {
        $assertChoiceTagValueNode = $phpDocInfo->findOneByAnnotationClass('Symfony\\Component\\Validator\\Constraints\\Choice');
        if (!$assertChoiceTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            return;
        }
        $callback = $assertChoiceTagValueNode->getValueWithoutQuotes('callback');
        if (!$callback instanceof \Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode) {
            return;
        }
        $callbackClass = $callback->getValueWithoutQuotes(0);
        foreach ($oldToNewClasses as $oldClass => $newClass) {
            if ($callbackClass !== $oldClass) {
                continue;
            }
            $callback->changeValue('0', $newClass);
            $assertChoiceTagValueNode->changeValue('callback', $callback);
            break;
        }
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function processDoctrineRelationTagValueNode(\PhpParser\Node $node, array $oldToNewClasses, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : void
    {
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClasses(['Doctrine\\ORM\\Mapping\\OneToMany', 'Doctrine\\ORM\\Mapping\\ManyToMany', 'Doctrine\\ORM\\Mapping\\Embedded']);
        if (!$doctrineAnnotationTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            return;
        }
        $this->processDoctrineToMany($doctrineAnnotationTagValueNode, $node, $oldToNewClasses);
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function processSerializerTypeTagValueNode(array $oldToNewClasses, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : void
    {
        $doctrineAnnotationTagValueNode = $phpDocInfo->findOneByAnnotationClass('JMS\\Serializer\\Annotation\\Type');
        if (!$doctrineAnnotationTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            return;
        }
        foreach ($oldToNewClasses as $oldClass => $newClass) {
            $className = $doctrineAnnotationTagValueNode->getSilentValue();
            if (\is_string($className)) {
                if ($className === $oldClass) {
                    $doctrineAnnotationTagValueNode->changeSilentValue($newClass);
                    continue;
                }
                $newContent = \RectorPrefix20220209\Nette\Utils\Strings::replace($className, '#\\b' . \preg_quote($oldClass, '#') . '\\b#', $newClass);
                if ($newContent === $className) {
                    continue;
                }
                $doctrineAnnotationTagValueNode->changeSilentValue($newContent);
                continue;
            }
            $currentType = $doctrineAnnotationTagValueNode->getValueWithoutQuotes('type');
            if ($currentType === $oldClass) {
                $doctrineAnnotationTagValueNode->changeValue('type', $newClass);
                continue;
            }
        }
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function processDoctrineToMany(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, \PhpParser\Node $node, array $oldToNewClasses) : void
    {
        $classKey = $doctrineAnnotationTagValueNode->hasClassName('Doctrine\\ORM\\Mapping\\Embedded') ? 'class' : 'targetEntity';
        $targetEntity = $doctrineAnnotationTagValueNode->getValueWithoutQuotes($classKey);
        if ($targetEntity === null) {
            return;
        }
        // resolve to FQN
        $tagFullyQualifiedName = $this->classAnnotationMatcher->resolveTagFullyQualifiedName($targetEntity, $node);
        foreach ($oldToNewClasses as $oldClass => $newClass) {
            if ($tagFullyQualifiedName !== $oldClass) {
                continue;
            }
            $doctrineAnnotationTagValueNode->changeValue($classKey, $newClass);
        }
    }
}
