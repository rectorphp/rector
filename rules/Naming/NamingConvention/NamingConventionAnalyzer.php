<?php

declare (strict_types=1);
namespace Rector\Naming\NamingConvention;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Util\StringUtils;
use Rector\NodeNameResolver\NodeNameResolver;
final class NamingConventionAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * Matches cases:
     *
     * $someNameSuffix = $this->getSomeName();
     * $prefixSomeName = $this->getSomeName();
     * $someName = $this->getSomeName();
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $expr
     */
    public function isCallMatchingVariableName($expr, string $currentName, string $expectedName) : bool
    {
        // skip "$call = $method->call();" based conventions
        $callName = $this->nodeNameResolver->getName($expr->name);
        if ($currentName === $callName) {
            return \true;
        }
        // starts with or ends with
        return \Rector\Core\Util\StringUtils::isMatch($currentName, '#^(' . $expectedName . '|' . $expectedName . '$)#i');
    }
}
