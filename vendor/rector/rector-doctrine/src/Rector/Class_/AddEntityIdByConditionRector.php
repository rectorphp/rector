<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Doctrine\NodeFactory\EntityIdNodeFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202208\Webmozart\Assert\Assert;
/**
 * @see \Rector\Doctrine\Tests\Rector\Class_\AddEntityIdByConditionRector\AddEntityIdByConditionRectorTest
 */
final class AddEntityIdByConditionRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const DETECTED_TRAITS = 'detected_traits';
    /**
     * @var string[]
     */
    private $detectedTraits = [];
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeFactory\EntityIdNodeFactory
     */
    private $entityIdNodeFactory;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassInsertManipulator
     */
    private $classInsertManipulator;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    public function __construct(EntityIdNodeFactory $entityIdNodeFactory, ClassInsertManipulator $classInsertManipulator, ReflectionProvider $reflectionProvider, ClassAnalyzer $classAnalyzer)
    {
        $this->entityIdNodeFactory = $entityIdNodeFactory;
        $this->classInsertManipulator = $classInsertManipulator;
        $this->reflectionProvider = $reflectionProvider;
        $this->classAnalyzer = $classAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add entity id with annotations when meets condition', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    use SomeTrait;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class SomeClass
{
    use SomeTrait;

    /**
      * @ORM\Id
      * @ORM\Column(type="integer")
      * @ORM\GeneratedValue(strategy="AUTO")
      */
     private $id;

    public function getId(): int
    {
        return $this->id;
    }
}
CODE_SAMPLE
, [self::DETECTED_TRAITS => ['Knp\\DoctrineBehaviors\\Model\\Translatable\\Translation', 'Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslationTrait']])]);
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
        if ($this->shouldSkip($node)) {
            return null;
        }
        $idProperty = $this->entityIdNodeFactory->createIdProperty();
        $this->classInsertManipulator->addAsFirstMethod($node, $idProperty);
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $detectTraits = $configuration[self::DETECTED_TRAITS] ?? $configuration;
        Assert::isArray($detectTraits);
        Assert::allString($detectTraits);
        $this->detectedTraits = $detectTraits;
    }
    private function shouldSkip(Class_ $class) : bool
    {
        if ($this->classAnalyzer->isAnonymousClass($class)) {
            return \true;
        }
        if (!$this->isTraitMatch($class)) {
            return \true;
        }
        return (bool) $class->getProperty('id');
    }
    private function isTraitMatch(Class_ $class) : bool
    {
        $className = $this->getName($class);
        if ($className === null) {
            return \false;
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        foreach ($this->detectedTraits as $detectedTrait) {
            if ($classReflection->hasTraitUse($detectedTrait)) {
                return \true;
            }
        }
        return \false;
    }
}
