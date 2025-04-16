<?php

declare (strict_types=1);
namespace Rector\Php83\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\NodeAnalyzer\ClassAnalyzer;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\PhpParser\AstResolver;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://wiki.php.net/rfc/marking_overriden_methods
 *
 * @see \Rector\Tests\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector\AddOverrideAttributeToOverriddenMethodsRectorTest
 */
final class AddOverrideAttributeToOverriddenMethodsRector extends AbstractRector implements MinPhpVersionInterface, ConfigurableRectorInterface
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private ClassAnalyzer $classAnalyzer;
    /**
     * @readonly
     */
    private PhpAttributeAnalyzer $phpAttributeAnalyzer;
    /**
     * @readonly
     */
    private AstResolver $astResolver;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @api
     * @var string
     */
    public const ALLOW_OVERRIDE_EMPTY_METHOD = 'allow_override_empty_method';
    /**
     * @var string
     */
    private const OVERRIDE_CLASS = 'Override';
    private bool $allowOverrideEmptyMethod = \false;
    private bool $hasChanged = \false;
    public function __construct(ReflectionProvider $reflectionProvider, ClassAnalyzer $classAnalyzer, PhpAttributeAnalyzer $phpAttributeAnalyzer, AstResolver $astResolver, ValueResolver $valueResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->classAnalyzer = $classAnalyzer;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->astResolver = $astResolver;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add override attribute to overridden methods', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class ParentClass
{
    public function foo()
    {
        echo 'default';
    }
}

final class ChildClass extends ParentClass
{
    public function foo()
    {
        echo 'override default';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class ParentClass
{
    public function foo()
    {
        echo 'default';
    }
}

final class ChildClass extends ParentClass
{
    #[\Override]
    public function foo()
    {
        echo 'override default';
    }
}
CODE_SAMPLE
, [self::ALLOW_OVERRIDE_EMPTY_METHOD => \false])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $this->allowOverrideEmptyMethod = $configuration[self::ALLOW_OVERRIDE_EMPTY_METHOD] ?? \false;
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $this->hasChanged = \false;
        if ($this->classAnalyzer->isAnonymousClass($node)) {
            return null;
        }
        $className = (string) $this->getName($node);
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $parentClassReflections = $classReflection->getParents();
        if ($this->allowOverrideEmptyMethod) {
            $parentClassReflections = \array_merge(
                $parentClassReflections,
                $classReflection->getInterfaces(),
                // place on last to ensure verify method exists on parent early
                // for non abstract method from trait
                $classReflection->getTraits()
            );
        }
        if ($parentClassReflections === []) {
            return null;
        }
        foreach ($node->getMethods() as $classMethod) {
            $this->processAddOverrideAttribute($classMethod, $parentClassReflections);
        }
        if (!$this->hasChanged) {
            return null;
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::OVERRIDE_ATTRIBUTE;
    }
    /**
     * @param ClassReflection[] $parentClassReflections
     */
    private function processAddOverrideAttribute(ClassMethod $classMethod, array $parentClassReflections) : void
    {
        if ($this->shouldSkipClassMethod($classMethod)) {
            return;
        }
        /** @var string $classMethodName */
        $classMethodName = $this->getName($classMethod->name);
        // Private methods should be ignored
        $shouldAddOverride = \false;
        foreach ($parentClassReflections as $parentClassReflection) {
            if (!$parentClassReflection->hasNativeMethod($classMethod->name->toString())) {
                continue;
            }
            // ignore if it is a private method on the parent
            if (!$parentClassReflection->hasNativeMethod($classMethodName)) {
                continue;
            }
            $parentMethod = $parentClassReflection->getNativeMethod($classMethodName);
            if ($parentMethod->isPrivate()) {
                break;
            }
            if ($this->shouldSkipParentClassMethod($parentClassReflection, $classMethod)) {
                continue;
            }
            if ($parentClassReflection->isTrait() && !$parentMethod->isAbstract()) {
                break;
            }
            $shouldAddOverride = \true;
            break;
        }
        if ($shouldAddOverride) {
            $classMethod->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified(self::OVERRIDE_CLASS))]);
            $this->hasChanged = \true;
        }
    }
    private function shouldSkipClassMethod(ClassMethod $classMethod) : bool
    {
        if ($this->isName($classMethod->name, MethodName::CONSTRUCT)) {
            return \true;
        }
        if ($classMethod->isPrivate()) {
            return \true;
        }
        // ignore if it already uses the attribute
        return $this->phpAttributeAnalyzer->hasPhpAttribute($classMethod, self::OVERRIDE_CLASS);
    }
    private function shouldSkipParentClassMethod(ClassReflection $parentClassReflection, ClassMethod $classMethod) : bool
    {
        if ($this->allowOverrideEmptyMethod && $parentClassReflection->isBuiltIn()) {
            return \false;
        }
        // parse parent method, if it has some contents or not
        $parentClass = $this->astResolver->resolveClassFromClassReflection($parentClassReflection);
        if (!$parentClass instanceof ClassLike) {
            return \true;
        }
        $parentClassMethod = $parentClass->getMethod($classMethod->name->toString());
        if (!$parentClassMethod instanceof ClassMethod) {
            return \true;
        }
        if ($this->allowOverrideEmptyMethod) {
            return \false;
        }
        // just override abstract method also skipped on purpose
        // only grand child of abstract method that parent has content will have
        if ($parentClassMethod->isAbstract()) {
            return \true;
        }
        // has any stmts?
        if ($parentClassMethod->stmts === null || $parentClassMethod->stmts === []) {
            return \true;
        }
        if (\count($parentClassMethod->stmts) === 1) {
            /** @var Stmt $soleStmt */
            $soleStmt = $parentClassMethod->stmts[0];
            // most likely, return null; is interface to be designed to override
            if ($soleStmt instanceof Return_ && $soleStmt->expr instanceof Expr && $this->valueResolver->isNull($soleStmt->expr)) {
                return \true;
            }
            if ($soleStmt instanceof Expression && $soleStmt->expr instanceof Throw_) {
                return \true;
            }
        }
        return \false;
    }
}
