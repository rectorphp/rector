<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\Core\NodeManipulator\ClassManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Doctrine\NodeAnalyzer\TranslatablePropertyCollectorAndRemover;
use Rector\Doctrine\NodeFactory\TranslationClassNodeFactory;
use Rector\Doctrine\ValueObject\PropertyNamesAndPhpDocInfos;
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
     * @var \Rector\Core\NodeManipulator\ClassInsertManipulator
     */
    private $classInsertManipulator;
    /**
     * @var \Rector\Core\NodeManipulator\ClassManipulator
     */
    private $classManipulator;
    /**
     * @var \Rector\Doctrine\NodeFactory\TranslationClassNodeFactory
     */
    private $translationClassNodeFactory;
    /**
     * @var \Rector\Doctrine\NodeAnalyzer\TranslatablePropertyCollectorAndRemover
     */
    private $translatablePropertyCollectorAndRemover;
    public function __construct(\Rector\Core\NodeManipulator\ClassInsertManipulator $classInsertManipulator, \Rector\Core\NodeManipulator\ClassManipulator $classManipulator, \Rector\Doctrine\NodeFactory\TranslationClassNodeFactory $translationClassNodeFactory, \Rector\Doctrine\NodeAnalyzer\TranslatablePropertyCollectorAndRemover $translatablePropertyCollectorAndRemover)
    {
        $this->classInsertManipulator = $classInsertManipulator;
        $this->classManipulator = $classManipulator;
        $this->translationClassNodeFactory = $translationClassNodeFactory;
        $this->translatablePropertyCollectorAndRemover = $translatablePropertyCollectorAndRemover;
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
        $classType = $this->nodeTypeResolver->getType($node);
        $translatableObjectType = new \PHPStan\Type\ObjectType('Gedmo\\Translatable\\Translatable');
        if (!$translatableObjectType->isSuperTypeOf($classType)->yes()) {
            return null;
        }
        if (!$this->hasImplements($node)) {
            return null;
        }
        $this->classManipulator->removeInterface($node, 'Gedmo\\Translatable\\Translatable');
        $this->classInsertManipulator->addAsFirstTrait($node, 'Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslatableTrait');
        $node->implements[] = new \PhpParser\Node\Name\FullyQualified('Knp\\DoctrineBehaviors\\Contract\\Entity\\TranslatableInterface');
        $propertyNamesAndPhpDocInfos = $this->translatablePropertyCollectorAndRemover->processClass($node);
        $this->removeSetAndGetMethods($node, $propertyNamesAndPhpDocInfos->getPropertyNames());
        $this->dumpEntityTranslation($node, $propertyNamesAndPhpDocInfos);
        return $node;
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
    private function dumpEntityTranslation(\PhpParser\Node\Stmt\Class_ $class, \Rector\Doctrine\ValueObject\PropertyNamesAndPhpDocInfos $propertyNamesAndPhpDocInfos) : void
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
        foreach ($propertyNamesAndPhpDocInfos->all() as $propertyNameAndPhpDocInfo) {
            $property = $this->nodeFactory->createPrivateProperty($propertyNameAndPhpDocInfo->getPropertyName());
            $property->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO, $propertyNameAndPhpDocInfo->getPhpDocInfo());
            $class->stmts[] = $property;
        }
        $namespace->stmts[] = $class;
        $addedFileWithNodes = new \Rector\FileSystemRector\ValueObject\AddedFileWithNodes($filePath, [$namespace]);
        $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithNodes);
    }
    private function hasImplements(\PhpParser\Node\Stmt\Class_ $class) : bool
    {
        foreach ($class->implements as $implement) {
            if ($this->isName($implement, 'Gedmo\\Translatable\\Translatable')) {
                return \true;
            }
        }
        return \false;
    }
}
