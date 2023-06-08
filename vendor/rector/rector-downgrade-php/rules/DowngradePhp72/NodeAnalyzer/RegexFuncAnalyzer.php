<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\NodeAnalyzer;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class RegexFuncAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var string[]
     */
    private const REGEX_FUNCTION_NAMES = ['preg_match', 'preg_match_all'];
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function matchRegexFuncCall(FuncCall $funcCall) : ?FuncCall
    {
        if ($this->nodeNameResolver->isNames($funcCall, self::REGEX_FUNCTION_NAMES)) {
            return $funcCall;
        }
        $variable = $funcCall->name;
        if (!$variable instanceof Variable) {
            return null;
        }
        /** @var Scope $scope */
        $scope = $funcCall->getAttribute(AttributeKey::SCOPE);
        $variableType = $scope->getType($variable);
        foreach ($variableType->getConstantStrings() as $constantStringType) {
            if (\in_array($constantStringType->getValue(), self::REGEX_FUNCTION_NAMES, \true)) {
                return $funcCall;
            }
        }
        return null;
    }
}
