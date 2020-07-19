<?php

declare(strict_types=1);

namespace Rector\Naming\NamingConvention;

use Nette\Utils\Strings;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\NodeNameResolver\NodeNameResolver;

final class NamingConventionAnalyzer
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * Matches cases:
     *
     * $someNameSuffix = $this->getSomeName();
     * $prefixSomeName = $this->getSomeName();
     * $someName = $this->getSomeName();
     *
     * @param FuncCall|StaticCall|MethodCall $expr
     */
    public function isCallMatchingVariableName(Expr $expr, string $currentName, string $expectedName): bool
    {
        // skip "$call = $method->call();" based conventions
        $callName = $this->nodeNameResolver->getName($expr->name);
        if ($currentName === $callName) {
            return true;
        }

        // starts with or ends with
        return (bool) Strings::match($currentName, '#^(' . $expectedName . '|' . $expectedName . '$)#i');
    }
}
