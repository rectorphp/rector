<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PHPStan\Type\ObjectType;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
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
final class TranslationBehaviorRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassInsertManipulator
     */
    private $classInsertManipulator;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassManipulator
     */
    private $classManipulator;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeFactory\TranslationClassNodeFactory
     */
    private $translationClassNodeFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\TranslatablePropertyCollectorAndRemover
     */
    private $translatablePropertyCollectorAndRemover;
    /**
     * @readonly
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;
    public function __construct(ClassInsertManipulator $classInsertManipulator, ClassManipulator $classManipulator, TranslationClassNodeFactory $translationClassNodeFactory, TranslatablePropertyCollectorAndRemover $translatablePropertyCollectorAndRemover, RemovedAndAddedFilesCollector $removedAndAddedFilesCollector)
    {
        $this->classInsertManipulator = $classInsertManipulator;
        $this->classManipulator = $classManipulator;
        $this->translationClassNodeFactory = $translationClassNodeFactory;
        $this->translatablePropertyCollectorAndRemover = $translatablePropertyCollectorAndRemover;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change Translation from gedmo/doctrine-extensions to knplabs/doctrine-behaviors', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $classType = $this->nodeTypeResolver->getType($node);
        $translatableObjectType = new ObjectType('Gedmo\\Translatable\\Translatable');
        if (!$translatableObjectType->isSuperTypeOf($classType)->yes()) {
            return null;
        }
        if (!$this->hasImplements($node)) {
            return null;
        }
        $this->classManipulator->removeInterface($node, 'Gedmo\\Translatable\\Translatable');
        $this->classInsertManipulator->addAsFirstTrait($node, 'Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslatableTrait');
        $node->implements[] = new FullyQualified('Knp\\DoctrineBehaviors\\Contract\\Entity\\TranslatableInterface');
        $propertyNamesAndPhpDocInfos = $this->translatablePropertyCollectorAndRemover->processClass($node);
        $this->removeSetAndGetMethods($node, $propertyNamesAndPhpDocInfos->getPropertyNames());
        $this->dumpEntityTranslation($node, $propertyNamesAndPhpDocInfos);
        return $node;
    }
    /**
     * @param string[] $removedPropertyNames
     */
    private function removeSetAndGetMethods(Class_ $class, array $removedPropertyNames) : void
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
    private function dumpEntityTranslation(Class_ $class, PropertyNamesAndPhpDocInfos $propertyNamesAndPhpDocInfos) : void
    {
        $smartFileInfo = $this->file->getSmartFileInfo();
        $classShortName = $class->name . 'Translation';
        $filePath = \dirname($smartFileInfo->getRealPath()) . \DIRECTORY_SEPARATOR . $classShortName . '.php';
        $parentNode = $class->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Namespace_) {
            throw new ShouldNotHappenException();
        }
        $namespace = new Namespace_($parentNode->name);
        $class = $this->translationClassNodeFactory->create($classShortName);
        foreach ($propertyNamesAndPhpDocInfos->all() as $propertyNameAndPhpDocInfo) {
            $property = $this->nodeFactory->createPrivateProperty($propertyNameAndPhpDocInfo->getPropertyName());
            $property->setAttribute(AttributeKey::PHP_DOC_INFO, $propertyNameAndPhpDocInfo->getPhpDocInfo());
            $class->stmts[] = $property;
        }
        $namespace->stmts[] = $class;
        $addedFileWithNodes = new AddedFileWithNodes($filePath, [$namespace]);
        $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithNodes);
    }
    private function hasImplements(Class_ $class) : bool
    {
        foreach ($class->implements as $implement) {
            if ($this->isName($implement, 'Gedmo\\Translatable\\Translatable')) {
                return \true;
            }
        }
        return \false;
    }
}
