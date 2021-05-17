<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Naming;

use Nette\Utils\Strings;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
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
     * @var string
     * @see https://regex101.com/r/dhAgLI/1
     */
    private const SPACE_REGEX = '#\s+#';

    /**
     * @var string
     * @see https://regex101.com/r/TOPfAQ/1
     */
    private const VALID_STRING_VARIABLE_REGEX = '#^[a-z_]\w*$#';

    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private ExpectedNameResolver $expectedNameResolver
    ) {
    }

    /**
     * @todo decouple to collector by arg type
     */
    public function resolveVariableName(MethodCall $methodCall): ?string
    {
        $methodCallVarName = $this->nodeNameResolver->getName($methodCall->var);
        $methodCallName = $this->nodeNameResolver->getName($methodCall->name);
        if ($methodCallVarName === null) {
            return null;
        }
        if ($methodCallName === null) {
            return null;
        }

        $result = $this->getVariableName($methodCall, $methodCallVarName, $methodCallName);
        if (! Strings::match($result, self::SPACE_REGEX)) {
            return $result;
        }

        return $this->getFallbackVarName($methodCallVarName, $methodCallName);
    }

    private function getVariableName(MethodCall $methodCall, string $methodCallVarName, string $methodCallName): string
    {
        $variableName = $this->expectedNameResolver->resolveForCall($methodCall);
        if ($methodCall->args === [] && $variableName !== null && $variableName !== $methodCallVarName) {
            return $variableName;
        }

        $fallbackVarName = $this->getFallbackVarName($methodCallVarName, $methodCallName);
        $argValue = $methodCall->args[0]->value;
        if ($argValue instanceof ClassConstFetch && $argValue->name instanceof Identifier) {
            return $this->getClassConstFetchVarName($argValue, $methodCallName);
        }

        if ($argValue instanceof String_) {
            return $this->getStringVarName($argValue, $methodCallVarName, $fallbackVarName);
        }

        $argumentName = $this->nodeNameResolver->getName($argValue);
        if (! $argValue instanceof Variable) {
            return $fallbackVarName;
        }
        if ($argumentName === null) {
            return $fallbackVarName;
        }
        if ($variableName === null) {
            return $fallbackVarName;
        }
        return $argumentName . ucfirst($variableName);
    }

    private function getFallbackVarName(string $methodCallVarName, string $methodCallName): string
    {
        return $methodCallVarName . ucfirst($methodCallName);
    }

    private function getClassConstFetchVarName(ClassConstFetch $classConstFetch, string $methodCallName): string
    {
        /** @var Identifier $name */
        $name = $classConstFetch->name;
        $argValueName = strtolower($name->toString());

        if ($argValueName !== 'class') {
            return Strings::replace(
                $argValueName,
                self::CONSTANT_REGEX,
                fn ($matches): string => strtoupper($matches[2])
            );
        }

        if ($classConstFetch->class instanceof Name) {
            return $this->normalizeStringVariableName($methodCallName) . $classConstFetch->class->getLast();
        }

        return $this->normalizeStringVariableName($methodCallName);
    }

    private function getStringVarName(String_ $string, string $methodCallVarName, string $fallbackVarName): string
    {
        $normalizeStringVariableName = $this->normalizeStringVariableName($string->value . ucfirst($fallbackVarName));
        if (! Strings::match($normalizeStringVariableName, self::START_ALPHA_REGEX)) {
            return $fallbackVarName;
        }
        if ($normalizeStringVariableName === $methodCallVarName) {
            return $fallbackVarName;
        }
        return $normalizeStringVariableName;
    }

    private function normalizeStringVariableName(string $string): string
    {
        if (! Strings::match($string, self::VALID_STRING_VARIABLE_REGEX)) {
            return '';
        }

        $get = str_ireplace('get', '', $string);
        $by = str_ireplace('by', '', $get);

        return str_replace('-', '', $by);
    }
}
