<?php

declare (strict_types=1);
namespace Rector\Php83\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
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
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
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
        // Detect if class extends a parent class
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        // Fetch the parent class reflection
        $parentClassReflection = $this->reflectionProvider->getClass((string) $node->extends);
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($classMethod->name->toString() === '__construct') {
                continue;
            }
            // Private methods should be ignored
            if ($parentClassReflection->hasNativeMethod($classMethod->name->toString())) {
                // ignore if it is a private method on the parent
                $parentMethod = $parentClassReflection->getNativeMethod($classMethod->name->toString());
                if ($parentMethod->isPrivate()) {
                    continue;
                }
                // ignore if it already uses the attribute
                if ($this->phpAttributeAnalyzer->hasPhpAttribute($classMethod, 'Override')) {
                    continue;
                }
                $classMethod->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified('Override'))]);
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::OVERRIDE_ATTRIBUTE;
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        if ($this->classAnalyzer->isAnonymousClass($class)) {
            return \true;
        }
        if (!$class->extends instanceof FullyQualified) {
            return \true;
        }
        return !$this->reflectionProvider->hasClass($class->extends->toString());
    }
}
