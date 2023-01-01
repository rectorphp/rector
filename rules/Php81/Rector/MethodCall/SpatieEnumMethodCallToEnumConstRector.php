<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use RectorPrefix202301\Spatie\Enum\Enum;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/enumerations
 *
 * @see \Rector\Tests\Php81\Rector\MethodCall\MyCLabsMethodCallToEnumConstRector\MyCLabsMethodCallToEnumConstRectorTest
 */
final class SpatieEnumMethodCallToEnumConstRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var class-string<Enum>
     */
    private const SPATIE_FQN = 'Spatie\\Enum\\Enum';
    /**
     * @var string[]
     */
    private const ENUM_METHODS = ['from', 'values', 'keys', 'isValid', 'search', 'toArray', 'assertValidValue'];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor Spatie enum method calls', [new CodeSample(<<<'CODE_SAMPLE'
$value1 = SomeEnum::SOME_CONSTANT()->getValue();
$value2 = SomeEnum::SOME_CONSTANT()->value;
$name1 = SomeEnum::SOME_CONSTANT()->getName();
$name2 = SomeEnum::SOME_CONSTANT()->name;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$value1 = SomeEnum::SOME_CONSTANT->value;
$value2 = SomeEnum::SOME_CONSTANT->value;
$name1 = SomeEnum::SOME_CONSTANT->name;
$name2 = SomeEnum::SOME_CONSTANT->name;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->name instanceof Expr) {
            return null;
        }
        $enumCaseName = $this->getName($node->name);
        if ($enumCaseName === null) {
            return null;
        }
        if ($this->shouldOmitEnumCase($enumCaseName)) {
            return null;
        }
        if ($node instanceof MethodCall) {
            return $this->refactorMethodCall($node, $enumCaseName);
        }
        if (!$this->isObjectType($node->class, new ObjectType(self::SPATIE_FQN))) {
            return null;
        }
        $className = $this->getName($node->class);
        if (!\is_string($className)) {
            return null;
        }
        $constantName = \strtoupper($enumCaseName);
        return $this->nodeFactory->createClassConstFetch($className, $constantName);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ENUM;
    }
    private function refactorGetterToMethodCall(MethodCall $methodCall, string $property) : ?PropertyFetch
    {
        if (!$methodCall->var instanceof StaticCall) {
            return null;
        }
        $staticCall = $methodCall->var;
        $className = $this->getName($staticCall->class);
        if ($className === null) {
            return null;
        }
        $enumCaseName = $this->getName($staticCall->name);
        if ($enumCaseName === null) {
            return null;
        }
        if ($this->shouldOmitEnumCase($enumCaseName)) {
            return null;
        }
        $upperCaseName = \strtoupper($enumCaseName);
        $enumConstFetch = $this->nodeFactory->createClassConstFetch($className, $upperCaseName);
        return new PropertyFetch($enumConstFetch, $property);
    }
    private function refactorMethodCall(MethodCall $methodCall, string $methodName) : ?\PhpParser\Node\Expr\PropertyFetch
    {
        if (!$this->isObjectType($methodCall->var, new ObjectType(self::SPATIE_FQN))) {
            return null;
        }
        if ($methodName === 'getName') {
            return $this->refactorGetterToMethodCall($methodCall, 'name');
        }
        if ($methodName === 'label') {
            return $this->refactorGetterToMethodCall($methodCall, 'name');
        }
        if ($methodName === 'getValue') {
            return $this->refactorGetterToMethodCall($methodCall, 'value');
        }
        if ($methodName === 'value') {
            return $this->refactorGetterToMethodCall($methodCall, 'value');
        }
        return null;
    }
    private function shouldOmitEnumCase(string $enumCaseName) : bool
    {
        return \in_array($enumCaseName, self::ENUM_METHODS, \true);
    }
}
