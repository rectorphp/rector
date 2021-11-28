<?php

declare(strict_types=1);

namespace Rector\Naming\NamingConvention;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Util\StringUtils;
use Rector\NodeNameResolver\NodeNameResolver;

final class NamingConventionAnalyzer
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    /**
     * Matches cases:
     *
     * $someNameSuffix = $this->getSomeName();
     * $prefixSomeName = $this->getSomeName();
     * $someName = $this->getSomeName();
     */
    public function isCallMatchingVariableName(
        FuncCall | StaticCall | MethodCall $expr,
        string $currentName,
        string $expectedName
    ): bool {
        // skip "$call = $method->call();" based conventions
        $callName = $this->nodeNameResolver->getName($expr->name);
        if ($currentName === $callName) {
            return true;
        }

        // starts with or ends with
        return StringUtils::isMatch($currentName, '#^(' . $expectedName . '|' . $expectedName . '$)#i');
    }
}
