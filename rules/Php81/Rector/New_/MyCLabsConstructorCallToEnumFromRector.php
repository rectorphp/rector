<?php

namespace Rector\Php81\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\Enum\ObjectReference;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php81\Rector\New_\MyCLabsConstructorCallToEnumFromRector\MyCLabsConstructorCallToEnumFromRectorTest
 */
final class MyCLabsConstructorCallToEnumFromRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    private const MY_C_LABS_CLASS = 'MyCLabs\\Enum\\Enum';
    private const DEFAULT_ENUM_CONSTRUCTOR = 'from';
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [New_::class];
    }
    /**
     * @param New_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        return $this->refactorConstructorCallToStaticFromCall($node);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ENUM;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor MyCLabs Enum using constructor for instantiation', [new CodeSample(<<<'CODE_SAMPLE'
$enum = new Enum($args);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$enum = Enum::from($args);
CODE_SAMPLE
)]);
    }
    private function refactorConstructorCallToStaticFromCall(New_ $node) : ?StaticCall
    {
        if (!$this->isObjectType($node->class, new ObjectType(self::MY_C_LABS_CLASS))) {
            return null;
        }
        $classname = $this->getName($node->class);
        if (\in_array($classname, [ObjectReference::SELF, ObjectReference::STATIC], \true)) {
            $classname = ($nullsafeVariable1 = ScopeFetcher::fetch($node)->getClassReflection()) ? $nullsafeVariable1->getName() : null;
        }
        if ($classname === null) {
            return null;
        }
        if (!$this->isMyCLabsConstructor($node, $classname)) {
            return null;
        }
        return new StaticCall(new Name\FullyQualified($classname), self::DEFAULT_ENUM_CONSTRUCTOR, $node->args);
    }
    private function isMyCLabsConstructor(New_ $node, string $classname) : bool
    {
        $classReflection = $this->reflectionProvider->getClass($classname);
        if (!$classReflection->hasMethod(MethodName::CONSTRUCT)) {
            return \true;
        }
        return $classReflection->getMethod(MethodName::CONSTRUCT, ScopeFetcher::fetch($node))->getDeclaringClass()->getName() === self::MY_C_LABS_CLASS;
    }
}
