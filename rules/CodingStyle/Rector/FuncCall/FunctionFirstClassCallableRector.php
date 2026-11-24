<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\VariadicPlaceholder;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use ReflectionException;
use ReflectionFunction;
use ReflectionNamedType;
/**
 * @see \Rector\Tests\CodingStyle\Rector\FuncCall\FunctionFirstClassCallableRector\FunctionFirstClassCallableRectorTest
 */
final class FunctionFirstClassCallableRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private UseImportsResolver $useImportsResolver;
    public function __construct(ReflectionProvider $reflectionProvider, UseImportsResolver $useImportsResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->useImportsResolver = $useImportsResolver;
    }
    public function getRuleDefinition(): RuleDefinition
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
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?FuncCall
    {
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
            if (!in_array($key, $callableArgs, \true)) {
                continue;
            }
            if (!$arg->value instanceof String_) {
                continue;
            }
            $isFullyQualified = strncmp($arg->value->value, '\\', strlen('\\')) === 0;
            $callableName = ltrim($arg->value->value, '\\');
            $node->args[$key] = new Arg(new FuncCall($isFullyQualified || strpos($callableName, '\\') !== \false || $this->isShadowedFunction($node, $callableName) ? new FullyQualified($callableName) : new Name($callableName), [new VariadicPlaceholder()]), \false, \false, [], $arg->name);
            $hasChanged = \true;
        }
        return $hasChanged ? $node : null;
    }
    /**
     * A string callable always resolves to the global function,
     * while a bare name resolves to a namespaced or imported function first
     */
    private function isShadowedFunction(FuncCall $funcCall, string $functionName): bool
    {
        $namespaceName = ScopeFetcher::fetch($funcCall)->getNamespace();
        if ($namespaceName !== null && $this->reflectionProvider->hasFunction(new FullyQualified($namespaceName . '\\' . $functionName), null)) {
            return \true;
        }
        foreach ($this->useImportsResolver->resolve() as $use) {
            foreach ($use->uses as $useItem) {
                if ($use->type !== Use_::TYPE_FUNCTION && $useItem->type !== Use_::TYPE_FUNCTION) {
                    continue;
                }
                if ($useItem->getAlias()->toLowerString() === strtolower($functionName)) {
                    return \true;
                }
            }
        }
        return \false;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_81;
    }
}
