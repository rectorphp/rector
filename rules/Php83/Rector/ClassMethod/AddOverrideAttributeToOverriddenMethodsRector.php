<?php

declare (strict_types=1);
namespace Rector\Php83\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\NodeAnalyzer\ClassAnalyzer;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://wiki.php.net/rfc/marking_overriden_methods
 * @see \Rector\Tests\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector\AddOverrideAttributeToOverriddenMethodsRectorTest
 */
final class AddOverrideAttributeToOverriddenMethodsRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    /**
     * @var bool
     */
    private $hasChanged = \false;
    public function __construct(ReflectionProvider $reflectionProvider, ClassAnalyzer $classAnalyzer, PhpAttributeAnalyzer $phpAttributeAnalyzer)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->classAnalyzer = $classAnalyzer;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add override attribute to overridden methods', [new CodeSample(<<<'CODE_SAMPLE'
class ParentClass
{
    public function foo()
    {
    }
}

class ChildClass extends ParentClass
{
    public function foo()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class ParentClass
{
    public function foo()
    {
    }
}

class ChildClass extends ParentClass
{
    #[\Override]
    public function foo()
    {
    }
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
        $this->hasChanged = \false;
        if ($this->classAnalyzer->isAnonymousClass($node)) {
            return null;
        }
        $className = (string) $this->getName($node);
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $parentClassReflections = \array_merge($classReflection->getParents(), $classReflection->getInterfaces(), $classReflection->getTraits());
        $this->processAddOverrideAttribute($node, $parentClassReflections);
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
    private function processAddOverrideAttribute(Class_ $class, array $parentClassReflections) : void
    {
        if ($parentClassReflections === []) {
            return;
        }
        foreach ($class->getMethods() as $classMethod) {
            if ($classMethod->name->toString() === '__construct') {
                continue;
            }
            if ($classMethod->isPrivate()) {
                continue;
            }
            // ignore if it already uses the attribute
            if ($this->phpAttributeAnalyzer->hasPhpAttribute($classMethod, 'Override')) {
                continue;
            }
            // Private methods should be ignored
            foreach ($parentClassReflections as $parentClassReflection) {
                if (!$parentClassReflection->hasNativeMethod($classMethod->name->toString())) {
                    continue;
                }
                // ignore if it is a private method on the parent
                $parentMethod = $parentClassReflection->getNativeMethod($classMethod->name->toString());
                if ($parentMethod->isPrivate()) {
                    continue;
                }
                if ($parentClassReflection->isTrait() && !$parentMethod->isAbstract()) {
                    continue;
                }
                $classMethod->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified('Override'))]);
                $this->hasChanged = \true;
                continue 2;
            }
        }
    }
}
