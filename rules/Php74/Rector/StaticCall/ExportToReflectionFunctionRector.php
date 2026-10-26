<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Enum\ObjectReference;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
/**
 * @see \Rector\Tests\Php74\Rector\StaticCall\ExportToReflectionFunctionRector\ExportToReflectionFunctionRectorTest
 */
final class ExportToReflectionFunctionRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_74;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change export() to ReflectionFunction alternatives', [new CodeSample(<<<'CODE_SAMPLE'
$reflectionFunction = ReflectionFunction::export('foo');
$reflectionFunctionAsString = ReflectionFunction::export('foo', true);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$reflectionFunction = new ReflectionFunction('foo');
$reflectionFunctionAsString = (string) new ReflectionFunction('foo');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$node->class instanceof Name) {
            return null;
        }
        if (!$this->isName($node->name, 'export')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $firstArg = $node->getArgs()[0] ?? null;
        if (!$firstArg instanceof Arg) {
            return null;
        }
        $callerType = $this->resolveCallerType($node->class);
        if (!$callerType->isSuperTypeOf(new ObjectType('ReflectionFunction'))->yes()) {
            return null;
        }
        $new = new New_($node->class, [new Arg($firstArg->value)]);
        $secondArg = $node->getArgs()[1] ?? null;
        if (!$secondArg instanceof Arg) {
            return $new;
        }
        if ($this->valueResolver->isTrue($secondArg->value)) {
            return new String_($new);
        }
        return $new;
    }
    private function resolveCallerType(Name $name): Type
    {
        // a concrete class name is resolvable straight from the node, no type inference needed;
        // self/static/parent depend on the enclosing class, so fall back to scope-based type
        $className = $this->getName($name);
        if ($className === null) {
            return $this->getType($name);
        }
        $loweredClassName = strtolower($className);
        if (in_array($loweredClassName, [ObjectReference::SELF, ObjectReference::STATIC, ObjectReference::PARENT], \true)) {
            return $this->getType($name);
        }
        return new ObjectType($className);
    }
}
