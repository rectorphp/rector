<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\VariadicPlaceholder;
use PHPStan\Analyser\Scope;
use Rector\Rector\AbstractScopeAwareRector;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use ReflectionException;
use ReflectionFunction;
use ReflectionNamedType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\FuncCall\FunctionFirstClassCallableRector\FunctionFirstClassCallableRectorTest
 */
final class FunctionFirstClassCallableRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        // see RFC https://wiki.php.net/rfc/first_class_callable_syntax
        return new RuleDefinition('Upgrade string callback functions to first class callable', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(array $data)
    {
        return array_map('trim', $data);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(array $data)
    {
        return array_map(trim(...), $data);
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
        return [FuncCall::class];
    }
    public function refactorWithScope(Node $node, Scope $scope) : ?FuncCall
    {
        if (!$node instanceof FuncCall) {
            return null;
        }
        if (!$node->name instanceof Name) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $functionName = (string) $this->getName($node);
        try {
            $reflectionFunction = new ReflectionFunction($functionName);
        } catch (ReflectionException $exception) {
            return null;
        }
        $callableArgs = [];
        foreach ($reflectionFunction->getParameters() as $reflectionParameter) {
            if ($reflectionParameter->getType() instanceof ReflectionNamedType && $reflectionParameter->getType()->getName() === 'callable') {
                $callableArgs[] = $reflectionParameter->getPosition();
            }
        }
        $hasChanged = \false;
        foreach ($node->getArgs() as $key => $arg) {
            if (!\in_array($key, $callableArgs, \true)) {
                continue;
            }
            if (!$arg->value instanceof String_) {
                continue;
            }
            $node->args[$key] = new Arg(new FuncCall(new Name($arg->value->value), [new VariadicPlaceholder()]), \false, \false, [], $arg->name);
            $hasChanged = \true;
        }
        return $hasChanged ? $node : null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersion::PHP_81;
    }
}
