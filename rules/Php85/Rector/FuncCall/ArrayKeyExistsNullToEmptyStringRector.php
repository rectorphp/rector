<?php

declare (strict_types=1);
namespace Rector\Php85\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionReflection;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\ParametersAcceptorSelectorVariantsWrapper;
use Rector\Php81\NodeManipulator\NullToStrictStringConverter;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_using_values_null_as_an_array_offset_and_when_calling_array_key_exists
 * @see \Rector\Tests\Php85\Rector\FuncCall\ArrayKeyExistsNullToEmptyStringRector\ArrayKeyExistsNullToEmptyStringRectorTest
 */
final class ArrayKeyExistsNullToEmptyStringRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private NullToStrictStringConverter $nullToStrictStringConverter;
    public function __construct(ReflectionResolver $reflectionResolver, NullToStrictStringConverter $nullToStrictStringConverter)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->nullToStrictStringConverter = $nullToStrictStringConverter;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace null key in array_key_exists with empty string', [new CodeSample(<<<'CODE_SAMPLE'
array_key_exists(null, $array);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
array_key_exists('', $array);
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node, 'array_key_exists')) {
            return null;
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return null;
        }
        $args = $node->getArgs();
        if (count($args) !== 2) {
            return null;
        }
        $classReflection = $scope->getClassReflection();
        $isTrait = $classReflection instanceof ClassReflection && $classReflection->isTrait();
        $functionReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
        if (!$functionReflection instanceof FunctionReflection) {
            return null;
        }
        $parametersAcceptor = ParametersAcceptorSelectorVariantsWrapper::select($functionReflection, $node, $scope);
        $result = $this->nullToStrictStringConverter->convertIfNull($node, $args, $this->resolvePosition($args), $isTrait, $scope, $parametersAcceptor);
        if ($result instanceof Node) {
            return $result;
        }
        return null;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::DEPRECATE_NULL_ARG_IN_ARRAY_KEY_EXISTS_FUNCTION;
    }
    /**
     * @param Arg[] $args
     */
    private function resolvePosition(array $args): int
    {
        foreach ($args as $position => $arg) {
            if ($arg->name instanceof Identifier && $arg->name->toString() === 'key') {
                return $position;
            }
        }
        return 0;
    }
}
