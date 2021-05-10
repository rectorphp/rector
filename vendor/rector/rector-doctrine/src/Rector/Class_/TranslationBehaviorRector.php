<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\Core\NodeManipulator\ClassManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Doctrine\NodeFactory\TranslationClassNodeFactory;
use Rector\FileSystemRector\ValueObject\AddedFileWithNodes;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/Atlantic18/DoctrineExtensions/blob/v2.4.x/doc/translatable.md
 * @see https://github.com/KnpLabs/DoctrineBehaviors/blob/4e0677379dd4adf84178f662d08454a9627781a8/docs/translatable.md
 *
 * @see https://lab.axioma.lv/symfony2/pagebundle/commit/062f9f87add5740ea89072e376dd703f3188d2ce
 *
 * @see \Rector\Doctrine\Tests\Rector\Class_\TranslationBehaviorRector\TranslationBehaviorRectorTest
 */
final class TranslationBehaviorRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var ClassManipulator
     */
    private $classManipulator;
    /**
     * @var ClassInsertManipulator
     */
    private $classInsertManipulator;
    /**
     * @var TranslationClassNodeFactory
     */
    private $translationClassNodeFactory;
    /**
     * @var PhpDocTagRemover
     */
    private $phpDocTagRemover;
    public function __construct(\Rector\Core\NodeManipulator\ClassInsertManipulator $classInsertManipulator, \Rector\Core\NodeManipulator\ClassManipulator $classManipulator, \Rector\Doctrine\NodeFactory\TranslationClassNodeFactory $translationClassNodeFactory, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover $phpDocTagRemover)
    {
        $this->classManipulator = $classManipulator;
        $this->classInsertManipulator = $classInsertManipulator;
        $this->translationClassNodeFactory = $translationClassNodeFactory;
        $this->phpDocTagRemover = $phpDocTagRemover;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change Translation from gedmo/doctrine-extensions to knplabs/doctrine-behaviors', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Table
 */
class Article implements Translatable
{
    /**
     * @Gedmo\Translatable
     * @ORM\Column(length=128)
     */
    private $title;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @Gedmo\Locale
     */
    private $locale;

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;

class SomeClass implements TranslatableInterface
{
    use TranslatableTrait;
}


use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

class SomeClassTranslation implements TranslationInterface
{
    use TranslationTrait;

    /**
     * @ORM\Column(length=128)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $content;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $classType = $this->nodeTypeResolver->resolve($node);
        $translatableObjectType = new \PHPStan\Type\ObjectType('Gedmo\\Translatable\\Translatable');
        if (!$translatableObjectType->isSuperTypeOf($classType)->yes()) {
            return null;
        }
        $this->classManipulator->removeInterface($node, 'Gedmo\\Translatable\\Translatable');
        $this->classInsertManipulator->addAsFirstTrait($node, 'Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslatableTrait');
        $node->implements[] = new \PhpParser\Node\Name\FullyQualified('Knp\\DoctrineBehaviors\\Contract\\Entity\\TranslatableInterface');
        $removedPropertyNameToPhpDocInfo = $this->collectAndRemoveTranslatableProperties($node);
        $removePropertyNames = \array_keys($removedPropertyNameToPhpDocInfo);
        $this->removeSetAndGetMethods($node, $removePropertyNames);
        $this->dumpEntityTranslation($node, $removedPropertyNameToPhpDocInfo);
        return $node;
    }
    /**
     * @return array<string, PhpDocInfo>
     */
    private function collectAndRemoveTranslatableProperties(\PhpParser\Node\Stmt\Class_ $class) : array
    {
        $removedPropertyNameToPhpDocInfo = [];
        foreach ($class->getProperties() as $property) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            if ($phpDocInfo->hasByAnnotationClass('Gedmo\\Mapping\\Annotation\\Locale')) {
                $this->removeNode($property);
                continue;
            }
            $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass('Gedmo\\Mapping\\Annotation\\Translatable');
            if (!$doctrineAnnotationTagValueNode) {
                continue;
            }
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $doctrineAnnotationTagValueNode);
            $propertyName = $this->getName($property);
            $removedPropertyNameToPhpDocInfo[$propertyName] = $phpDocInfo;
            $this->removeNode($property);
        }
        return $removedPropertyNameToPhpDocInfo;
    }
    /**
     * @param string[] $removedPropertyNames
     */
    private function removeSetAndGetMethods(\PhpParser\Node\Stmt\Class_ $class, array $removedPropertyNames) : void
    {
        foreach ($removedPropertyNames as $removedPropertyName) {
            foreach ($class->getMethods() as $classMethod) {
                if ($this->isName($classMethod, 'set' . \ucfirst($removedPropertyName))) {
                    $this->removeNode($classMethod);
                }
                if ($this->isName($classMethod, 'get' . \ucfirst($removedPropertyName))) {
                    $this->removeNode($classMethod);
                }
                if ($this->isName($classMethod, 'setTranslatableLocale')) {
                    $this->removeNode($classMethod);
                }
            }
        }
    }
    /**
     * @param PhpDocInfo[] $translatedPropertyToPhpDocInfos
     */
    private function dumpEntityTranslation(\PhpParser\Node\Stmt\Class_ $class, array $translatedPropertyToPhpDocInfos) : void
    {
        $fileInfo = $this->file->getSmartFileInfo();
        $classShortName = $class->name . 'Translation';
        $filePath = \dirname($fileInfo->getRealPath()) . \DIRECTORY_SEPARATOR . $classShortName . '.php';
        $namespace = $class->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$namespace instanceof \PhpParser\Node\Stmt\Namespace_) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $namespace = new \PhpParser\Node\Stmt\Namespace_($namespace->name);
        $class = $this->translationClassNodeFactory->create($classShortName);
        foreach ($translatedPropertyToPhpDocInfos as $translatedPropertyName => $translatedPhpDocInfo) {
            $property = $this->nodeFactory->createPrivateProperty($translatedPropertyName);
            $property->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO, $translatedPhpDocInfo);
            $class->stmts[] = $property;
        }
        $namespace->stmts[] = $class;
        $addedFileWithNodes = new \Rector\FileSystemRector\ValueObject\AddedFileWithNodes($filePath, [$namespace]);
        $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithNodes);
    }
}
