<?php

declare (strict_types=1);
namespace Rector\NodeAnalyzer;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\NodeNameResolver\NodeNameResolver;
final class DefineFuncCallAnalyzer
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param string[] $constants
     */
    public function isDefinedWithConstants(FuncCall $funcCall, array $constants) : bool
    {
        if (!$this->nodeNameResolver->isName($funcCall, 'defined')) {
            return \false;
        }
        if ($funcCall->isFirstClassCallable()) {
            return \false;
        }
        $firstArg = $funcCall->getArgs()[0];
        if (!$firstArg->value instanceof String_) {
            return \false;
        }
        $string = $firstArg->value;
        return \in_array($string->value, $constants, \true);
    }
}
