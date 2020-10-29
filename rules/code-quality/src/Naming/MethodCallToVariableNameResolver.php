<?php
declare(strict_types=1);

namespace Rector\CodeQuality\Naming;

use Nette\Utils\Strings;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\Naming\Naming\ExpectedNameResolver;
use Rector\NodeNameResolver\NodeNameResolver;

final class MethodCallToVariableNameResolver
{
    /**
     * @var string
     * @see https://regex101.com/r/LTykey/1
     */
    private const START_ALPHA_REGEX = '#^[a-zA-Z]#';

    /**
     * @var string
     * @see https://regex101.com/r/sYIKpj/1
     */
    private const CONSTANT_REGEX = '#(_)([a-z])#';

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ExpectedNameResolver
     */
    private $expectedNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver, ExpectedNameResolver $expectedNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->expectedNameResolver = $expectedNameResolver;
    }

    /**
     * @todo decouple to collector by arg type
     */
    public function resolveVariableName(MethodCall $methodCall): ?string
    {
        $methodCallVarName = $this->nodeNameResolver->getName($methodCall->var);
        if ($methodCallVarName === null) {
            return null;
        }

        $methodCallName = $this->nodeNameResolver->getName($methodCall->name);
        if ($methodCallName === null) {
            return null;
        }

        $variableName = $this->expectedNameResolver->resolveForCall($methodCall);
        if ($methodCall->args === [] && $variableName !== null && $variableName !== $methodCallVarName) {
            return $variableName;
        }

        $fallbackVarName = $this->getFallbackVarName($methodCallVarName, $methodCallName);
        $argValue = $methodCall->args[0]->value;
        if ($argValue instanceof ClassConstFetch && $argValue->name instanceof Identifier) {
            $argValueName = strtolower($argValue->name->toString());
            if ($argValueName !== 'class') {
                return Strings::replace(
                    $argValueName,
                    self::CONSTANT_REGEX,
                    function ($matches): string {
                        return strtoupper($matches[2]);
                    }
                );
            }

            return $this->replaceGetByDash($methodCallName) . $argValue->class->getLast();
        }

        if ($argValue instanceof String_) {
            return $this->getStringVarName($argValue, $methodCallVarName, $fallbackVarName);
        }

        if ($argValue instanceof Variable) {
            $argumentName = $this->nodeNameResolver->getName($argValue);
            if ($argumentName !== null && $variableName !== null) {
                return $argumentName . ucfirst($variableName);
            }
        }

        return $fallbackVarName;
    }

    private function getFallbackVarName(string $methodCallVarName, string $methodCallName): string
    {
        return $methodCallVarName . ucfirst($methodCallName);
    }

    private function getStringVarName(String_ $string, string $methodCallVarName, string $fallbackVarName): string
    {
        $replaceGetByDash = $this->replaceGetByDash($string->value . ucfirst($fallbackVarName));
        if (Strings::match($replaceGetByDash, self::START_ALPHA_REGEX) && $replaceGetByDash !== $methodCallVarName) {
            return $replaceGetByDash;
        }

        return $fallbackVarName;
    }

    private function replaceGetByDash(string $string): string
    {
        $get = str_ireplace('get', '', $string);
        $by = str_ireplace('by', '', $get);

        return str_replace('-', '', $by);
    }
}
