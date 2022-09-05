<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/enumerations
 * @changelog https://github.com/myclabs/php-enum
 *
 * @see \Rector\Tests\Php81\Rector\MethodCall\MyCLabsMethodCallToEnumConstRector\MyCLabsMethodCallToEnumConstRectorTest
 */
final class MyCLabsMethodCallToEnumConstRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var string[]
     */
    private const ENUM_METHODS = ['from', 'values', 'keys', 'isValid', 'search', 'toArray', 'assertValidValue'];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor MyCLabs enum fetch to Enum const', [new CodeSample(<<<'CODE_SAMPLE'
$name = SomeEnum::VALUE()->getKey();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$name = SomeEnum::VALUE;
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
        if (!$this->isObjectType($node->class, new ObjectType('MyCLabs\\Enum\\Enum'))) {
            return null;
        }
        $className = $this->getName($node->class);
        if (!\is_string($className)) {
            return null;
        }
        return $this->nodeFactory->createClassConstFetch($className, $enumCaseName);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ENUM;
    }
    private function refactorGetKeyMethodCall(MethodCall $methodCall) : ?ClassConstFetch
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
        return $this->nodeFactory->createClassConstFetch($className, $enumCaseName);
    }
    private function refactorGetValueMethodCall(MethodCall $methodCall) : ?PropertyFetch
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
        $enumConstFetch = $this->nodeFactory->createClassConstFetch($className, $enumCaseName);
        return new PropertyFetch($enumConstFetch, 'value');
    }
    /**
     * @return null|\PhpParser\Node\Expr\ClassConstFetch|\PhpParser\Node\Expr\PropertyFetch
     */
    private function refactorMethodCall(MethodCall $methodCall, string $methodName)
    {
        if (!$this->isObjectType($methodCall->var, new ObjectType('MyCLabs\\Enum\\Enum'))) {
            return null;
        }
        if ($methodName === 'getKey') {
            return $this->refactorGetKeyMethodCall($methodCall);
        }
        if ($methodName === 'getValue') {
            return $this->refactorGetValueMethodCall($methodCall);
        }
        return null;
    }
    private function shouldOmitEnumCase(string $enumCaseName) : bool
    {
        return \in_array($enumCaseName, self::ENUM_METHODS, \true);
    }
}
