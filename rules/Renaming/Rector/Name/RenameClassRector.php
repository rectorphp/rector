<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\Name;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitor;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Configuration\RenamedClassesDataCollector;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\Renaming\NodeManipulator\ClassRenamer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202506\Webmozart\Assert\Assert;
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
    public function getRuleDefinition() : RuleDefinition
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
, ['App\\SomeOldClass' => 'App\\SomeNewClass'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [
            ClassConstFetch::class,
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
     * @param ClassConstFetch|FunctionLike|FullyQualified|Name|ClassLike|Expression|Property|If_ $node
     * @return int|null|\PhpParser\Node
     */
    public function refactor(Node $node)
    {
        $oldToNewClasses = $this->renamedClassesDataCollector->getOldToNewClasses();
        if ($oldToNewClasses === []) {
            return null;
        }
        if ($node instanceof ClassConstFetch) {
            return $this->processClassConstFetch($node, $oldToNewClasses);
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        return $this->classRenamer->renameNode($node, $oldToNewClasses, $scope);
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allString($configuration);
        Assert::allString(\array_keys($configuration));
        $this->renamedClassesDataCollector->addOldToNewClasses($configuration);
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function processClassConstFetch(ClassConstFetch $classConstFetch, array $oldToNewClasses) : ?int
    {
        if (!$classConstFetch->class instanceof FullyQualified || !$classConstFetch->name instanceof Identifier || !$this->reflectionProvider->hasClass($classConstFetch->class->toString())) {
            return null;
        }
        foreach ($oldToNewClasses as $oldClass => $newClass) {
            if (!$this->isName($classConstFetch->class, $oldClass)) {
                continue;
            }
            if (!$this->reflectionProvider->hasClass($newClass)) {
                continue;
            }
            $classReflection = $this->reflectionProvider->getClass($newClass);
            if (!$classReflection->isInterface()) {
                continue;
            }
            $oldClassReflection = $this->reflectionProvider->getClass($oldClass);
            if ($oldClassReflection->hasConstant($classConstFetch->name->toString()) && !$classReflection->hasConstant($classConstFetch->name->toString())) {
                // no constant found on new interface? skip node below ClassConstFetch on this rule
                return NodeVisitor::DONT_TRAVERSE_CHILDREN;
            }
        }
        // continue to next Name usage
        return null;
    }
}
