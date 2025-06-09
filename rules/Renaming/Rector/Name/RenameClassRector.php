<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\Name;

use PHPStan\Reflection\ReflectionProvider;
use PhpParser\Node\Identifier;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitor;
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
    /**
     * @var string
     */
    public const SKIPPED_AS_CLASS_CONST_FETCH_CLASS = 'skipped_as_class_const_fetch_class';
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
        return [ClassConstFetch::class, FullyQualified::class, Property::class, FunctionLike::class, Expression::class, ClassLike::class, If_::class];
    }
    /**
     * @param ClassConstFetch|FunctionLike|FullyQualified|ClassLike|Expression|Property|If_ $node
     * @return int|null|\PhpParser\Node
     */
    public function refactor(Node $node)
    {
        $oldToNewClasses = $this->renamedClassesDataCollector->getOldToNewClasses();
        if ($node instanceof ClassConstFetch) {
            if ($node->class instanceof FullyQualified && $node->name instanceof Identifier && $this->reflectionProvider->hasClass($node->class->toString())) {
                foreach ($oldToNewClasses as $oldClass => $newClass) {
                    if ($this->isName($node->class, $oldClass) && $this->reflectionProvider->hasClass($newClass)) {
                        $classReflection = $this->reflectionProvider->getClass($newClass);
                        if (!$classReflection->isInterface()) {
                            continue;
                        }
                        $oldClassReflection = $this->reflectionProvider->getClass($oldClass);
                        if ($oldClassReflection->hasConstant($node->name->toString()) && !$classReflection->hasConstant($node->name->toString())) {
                            // used by ClassRenamer::renameNode()
                            // that called on ClassRenamingPostRector PostRector
                            $node->class->setAttribute(self::SKIPPED_AS_CLASS_CONST_FETCH_CLASS, \true);
                            // no constant found on new interface? skip node below ClassConstFetch on this rule
                            return NodeVisitor::DONT_TRAVERSE_CHILDREN;
                        }
                    }
                }
            }
            // continue to next FullyQualified usage
            return null;
        }
        if ($oldToNewClasses !== []) {
            $scope = $node->getAttribute(AttributeKey::SCOPE);
            return $this->classRenamer->renameNode($node, $oldToNewClasses, $scope);
        }
        return null;
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
}
