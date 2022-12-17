<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Contract\Rector\AllowEmptyConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\ClassConstFetch\ConvertStaticPrivateConstantToSelfRector\ConvertStaticPrivateConstantToSelfRectorTest
 * @see https://3v4l.org/8Y0ba
 * @see https://phpstan.org/r/11d4c850-1a40-4fae-b665-291f96104d11
 */
final class ConvertStaticPrivateConstantToSelfRector extends AbstractRector implements AllowEmptyConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const ENABLE_FOR_NON_FINAL_CLASSES = 'enable_for_non_final_classes';
    /**
     * @var bool
     */
    private $enableForNonFinalClasses = \false;
    /**
     * @readonly
     * @var \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(FamilyRelationsAnalyzer $familyRelationsAnalyzer, ReflectionResolver $reflectionResolver)
    {
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replaces static::* access to private constants with self::*', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class Foo {
    private const BAR = 'bar';
    public function run()
    {
        $bar = static::BAR;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class Foo {
    private const BAR = 'bar';
    public function run()
    {
        $bar = self::BAR;
    }
}
CODE_SAMPLE
, [self::ENABLE_FOR_NON_FINAL_CLASSES => \false])]);
    }
    public function getNodeTypes() : array
    {
        return [ClassConstFetch::class];
    }
    public function configure(array $configuration) : void
    {
        $this->enableForNonFinalClasses = $configuration[self::ENABLE_FOR_NON_FINAL_CLASSES] ?? (bool) \current($configuration);
    }
    /**
     * @param ClassConstFetch $node
     */
    public function refactor(Node $node) : ?ClassConstFetch
    {
        $class = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (!$class instanceof Class_) {
            return null;
        }
        if ($this->shouldBeSkipped($class, $node)) {
            return null;
        }
        $node->class = new Name('self');
        return $node;
    }
    private function isUsingStatic(ClassConstFetch $classConstFetch) : bool
    {
        if (!$classConstFetch->class instanceof Name) {
            return \false;
        }
        return $classConstFetch->class->toString() === 'static';
    }
    private function isPrivateConstant(ClassConstFetch $classConstFetch, Class_ $class) : bool
    {
        $constantName = $this->getConstantName($classConstFetch);
        if ($constantName === null) {
            return \false;
        }
        foreach ($class->getConstants() as $classConst) {
            if (!$this->nodeNameResolver->isName($classConst, $constantName)) {
                continue;
            }
            return $classConst->isPrivate();
        }
        return \false;
    }
    private function isUsedInPrivateMethod(ClassConstFetch $classConstFetch) : bool
    {
        $method = $this->betterNodeFinder->findParentType($classConstFetch, ClassMethod::class);
        if (!$method instanceof ClassMethod) {
            return \false;
        }
        return $method->flags === Class_::MODIFIER_PRIVATE;
    }
    private function shouldBeSkipped(Class_ $class, ClassConstFetch $classConstFetch) : bool
    {
        if (!$this->isUsingStatic($classConstFetch)) {
            return \true;
        }
        if (!$this->isPrivateConstant($classConstFetch, $class)) {
            return \true;
        }
        if ($this->isUsedInPrivateMethod($classConstFetch)) {
            return \false;
        }
        if ($this->enableForNonFinalClasses) {
            return $this->isOverwrittenInChildClass($classConstFetch);
        }
        return !$class->isFinal();
    }
    private function isOverwrittenInChildClass(ClassConstFetch $classConstFetch) : bool
    {
        $constantName = $this->getConstantName($classConstFetch);
        if ($constantName === null) {
            return \false;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classConstFetch);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        $childrenClassReflections = $this->familyRelationsAnalyzer->getChildrenOfClassReflection($classReflection);
        foreach ($childrenClassReflections as $childClassReflection) {
            if ($childClassReflection->hasConstant($constantName)) {
                return \true;
            }
        }
        return \false;
    }
    private function getConstantName(ClassConstFetch $classConstFetch) : ?string
    {
        $constantNameIdentifier = $classConstFetch->name;
        if (!$constantNameIdentifier instanceof Identifier) {
            return null;
        }
        return $constantNameIdentifier->toString();
    }
}
