<?php

declare(strict_types=1);

namespace Rector\DoctrineGedmoToKnplabs\Rector\Class_;

use Nette\Utils\FileSystem;
use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\LocaleTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\TranslatableTagValueNode;
use Rector\Doctrine\PhpDocParser\Ast\PhpDoc\PhpDocTagNodeFactory;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see https://github.com/Atlantic18/DoctrineExtensions/blob/v2.4.x/doc/translatable.md
 * @see https://github.com/KnpLabs/DoctrineBehaviors/blob/4e0677379dd4adf84178f662d08454a9627781a8/docs/translatable.md
 *
 * @see https://lab.axioma.lv/symfony2/pagebundle/commit/062f9f87add5740ea89072e376dd703f3188d2ce
 *
 * @see \Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\TranslationBehaviorRector\TranslationBehaviorRectorTest
 */
final class TranslationBehaviorRector extends AbstractRector
{
    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var PhpDocTagNodeFactory
     */
    private $phpDocTagNodeFactory;

    public function __construct(ClassManipulator $classManipulator, PhpDocTagNodeFactory $phpDocTagNodeFactory)
    {
        $this->classManipulator = $classManipulator;
        $this->phpDocTagNodeFactory = $phpDocTagNodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change Translation from gedmo/doctrine-extensions to knplabs/doctrine-behaviors', [
            new CodeSample(
                <<<'PHP'
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
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     * and it is not necessary because globally locale can be set in listener
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
PHP
,
                <<<'PHP'
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
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->classManipulator->hasInterface($node, 'Gedmo\Translatable\Translatable')) {
            return null;
        }

        $this->classManipulator->removeInterface($node, 'Gedmo\Translatable\Translatable');

        // 1. replace trait
        $this->classManipulator->addAsFirstTrait($node, 'Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait');

        // 2. add interface
        $node->implements[] = new FullyQualified('Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface');

        $removedPropertyNameToPhpDocInfo = $this->collectAndRemoveTranslatableProperties($node);
        $removePropertyNames = array_keys($removedPropertyNameToPhpDocInfo);

        // @todo add them as a @method annotation, so the autocomplete still works?
        $this->removeSetAndGetMethods($node, $removePropertyNames);

        $this->dumpEntityTranslation($node, $removedPropertyNameToPhpDocInfo);

        return $node;
    }

    /**
     * @return PhpDocInfo[]
     */
    private function collectAndRemoveTranslatableProperties(Class_ $class): array
    {
        $removedPropertyNameToPhpDocInfo = [];

        foreach ($class->getProperties() as $property) {
            $propertyPhpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
            if ($propertyPhpDocInfo === null) {
                continue;
            }

            if ($propertyPhpDocInfo->hasByType(LocaleTagValueNode::class)) {
                $this->removeNode($property);
                continue;
            }

            if (! $propertyPhpDocInfo->hasByType(TranslatableTagValueNode::class)) {
                continue;
            }

            $propertyPhpDocInfo->removeByType(TranslatableTagValueNode::class);

            $propertyName = $this->getName($property);
            $removedPropertyNameToPhpDocInfo[$propertyName] = $propertyPhpDocInfo;

            $this->removeNode($property);
        }

        return $removedPropertyNameToPhpDocInfo;
    }

    /**
     * @param string[] $removedPropertyNames
     */
    private function removeSetAndGetMethods(Class_ $class, array $removedPropertyNames): void
    {
        foreach ($removedPropertyNames as $removedPropertyName) {
            foreach ($class->getMethods() as $method) {
                if ($this->isName($method, 'set' . ucfirst($removedPropertyName))) {
                    $this->removeNode($method);
                }

                if ($this->isName($method, 'get' . ucfirst($removedPropertyName))) {
                    $this->removeNode($method);
                }

                if ($this->isName($method, 'setTranslatableLocale')) {
                    $this->removeNode($method);
                }
            }
        }
    }

    /**
     * @param PhpDocInfo[] $translatedPropertyToPhpDocInfos
     */
    private function dumpEntityTranslation(Class_ $class, array $translatedPropertyToPhpDocInfos): void
    {
        /** @var SmartFileInfo|null $fileInfo */
        $fileInfo = $class->getAttribute(AttributeKey::FILE_INFO);
        if ($fileInfo === null) {
            throw new ShouldNotHappenException();
        }

        $classShortName = $class->name . 'Translation';
        $filePath = dirname($fileInfo->getRealPath()) . DIRECTORY_SEPARATOR . $classShortName . '.php';

        $namespace = $class->getAttribute(AttributeKey::PARENT_NODE);
        if (! $namespace instanceof Namespace_) {
            throw new ShouldNotHappenException();
        }

        $namespace = new Namespace_($namespace->name);

        $class = new Class_($classShortName);
        $class->implements[] = new FullyQualified('Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface');
        $this->classManipulator->addAsFirstTrait($class, 'Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait');

        $this->docBlockManipulator->addTag($class, $this->phpDocTagNodeFactory->createEntityTag());

        foreach ($translatedPropertyToPhpDocInfos as $translatedPropertyName => $translatedPhpDocInfo) {
            $property = $this->nodeFactory->createPrivateProperty($translatedPropertyName);
            $property->setAttribute(AttributeKey::PHP_DOC_INFO, $translatedPhpDocInfo);

            $class->stmts[] = $property;
        }

        $namespace->stmts[] = $class;

        // @todo make temporary
        $content = '<?php' . PHP_EOL . $this->print($namespace) . PHP_EOL;

        FileSystem::write($filePath, $content);
    }
}
