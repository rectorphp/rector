<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\Name;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Configuration\RenamedClassesDataCollector;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\Renaming\NodeManipulator\ClassRenamer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202512\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\Name\RenameClassRector\RenameClassRectorTest
 */
final class RenameClassRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     */
    private RenamedClassesDataCollector $renamedClassesDataCollector;
    /**
     * @readonly
     */
    private ClassRenamer $classRenamer;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(RenamedClassesDataCollector $renamedClassesDataCollector, ClassRenamer $classRenamer, ReflectionProvider $reflectionProvider)
    {
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        $this->classRenamer = $classRenamer;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace defined classes by new ones', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
namespace App;

use SomeOldClass;

function someFunction(SomeOldClass $someOldClass): SomeOldClass
{
    if ($someOldClass instanceof SomeOldClass) {
        return new SomeOldClass;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
namespace App;

use SomeNewClass;

function someFunction(SomeNewClass $someOldClass): SomeNewClass
{
    if ($someOldClass instanceof SomeNewClass) {
        return new SomeNewClass;
    }
}
CODE_SAMPLE
, ['App\SomeOldClass' => 'App\SomeNewClass'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [
            // place FullyQualified before Name on purpose executed early before the Name as parent
            FullyQualified::class,
            // Name as parent of FullyQualified executed later for fallback annotation to attribute rename to Name
            Name::class,
            Property::class,
            FunctionLike::class,
            Expression::class,
            ClassLike::class,
            If_::class,
        ];
    }
    /**
     * @param FunctionLike|FullyQualified|Name|ClassLike|Expression|Property|If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $oldToNewClasses = $this->renamedClassesDataCollector->getOldToNewClasses();
        if ($oldToNewClasses === []) {
            return null;
        }
        if ($node instanceof FullyQualified && $this->shouldSkipClassConstFetchForMissingConstantName($node, $oldToNewClasses)) {
            return null;
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        return $this->classRenamer->renameNode($node, $oldToNewClasses, $scope);
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allString($configuration);
        Assert::allString(array_keys($configuration));
        $this->renamedClassesDataCollector->addOldToNewClasses($configuration);
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function shouldSkipClassConstFetchForMissingConstantName(FullyQualified $fullyQualified, array $oldToNewClasses): bool
    {
        if (!$this->reflectionProvider->hasClass($fullyQualified->toString())) {
            return \false;
        }
        // not part of class const fetch (e.g. SomeClass::SOME_VALUE)
        $constFetchName = $fullyQualified->getAttribute(AttributeKey::CLASS_CONST_FETCH_NAME);
        if (!is_string($constFetchName)) {
            return \false;
        }
        foreach ($oldToNewClasses as $oldClass => $newClass) {
            if (!$this->isName($fullyQualified, $oldClass)) {
                continue;
            }
            if (!$this->reflectionProvider->hasClass($newClass)) {
                continue;
            }
            $classReflection = $this->reflectionProvider->getClass($newClass);
            $oldClassReflection = $this->reflectionProvider->getClass($oldClass);
            if ($oldClassReflection->hasConstant($constFetchName) && !$classReflection->hasConstant($constFetchName)) {
                // should be skipped as new class does not have access to the constant
                return \true;
            }
        }
        return \false;
    }
}
