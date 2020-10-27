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

    public function resolveVariableName(MethodCall $methodCall): ?string
    {
        $methodCallVarName = $this->nodeNameResolver->getName($methodCall->var);
        $methodCallIdentifier = $methodCall->name;

        if (! $methodCallIdentifier instanceof Identifier) {
            return null;
        }

        $methodCallName = $methodCallIdentifier->toString();
        if ($methodCallVarName === null || $methodCallName === null) {
            return null;
        }

        $variableName = $this->expectedNameResolver->resolveForCall($methodCall);
        if ($methodCall->args === [] && $variableName !== null && $variableName !== $methodCallVarName) {
            return $variableName;
        }

        $arg0 = $methodCall->args[0]->value;
        if ($arg0 instanceof ClassConstFetch && $arg0->name instanceof Identifier) {
            return Strings::replace(
                strtolower($arg0->name->toString()),
                self::CONSTANT_REGEX,
                function ($matches): string {
                    return strtoupper($matches[2]);
                }
            );
        }

        $fallbackVarName = $this->getFallbackVarName($methodCallVarName, $methodCallName);
        if ($arg0 instanceof String_) {
            return $this->getStringVarName($arg0, $methodCallVarName, $fallbackVarName);
        }

        if ($arg0 instanceof Variable) {
            $argumentName = $this->nodeNameResolver->getName($arg0);
            if ($argumentName !== null) {
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
        $get = str_ireplace('get', '', $string->value . ucfirst($fallbackVarName));
        $by = str_ireplace('by', '', $get);
        $by = str_replace('-', '', $by);

        if (Strings::match($by, self::START_ALPHA_REGEX) && $by !== $methodCallVarName) {
            return $by;
        }

        return $fallbackVarName;
    }
}
