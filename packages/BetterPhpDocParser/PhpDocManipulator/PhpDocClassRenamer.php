<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocManipulator;

use RectorPrefix202208\Nette\Utils\Strings;
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
        $callback = $assertChoiceTagValueNode->getValueWithoutQuotes('callback');
        if (!$callback instanceof CurlyListNode) {
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
        foreach ($oldToNewClasses as $oldClass => $newClass) {
            $className = $doctrineAnnotationTagValueNode->getSilentValue();
            if (\is_string($className)) {
                if ($className === $oldClass) {
                    $doctrineAnnotationTagValueNode->changeSilentValue($newClass);
                    continue;
                }
                $newContent = Strings::replace($className, '#\\b' . \preg_quote($oldClass, '#') . '\\b#', $newClass);
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
    private function processDoctrineToMany(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, Node $node, array $oldToNewClasses) : void
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
