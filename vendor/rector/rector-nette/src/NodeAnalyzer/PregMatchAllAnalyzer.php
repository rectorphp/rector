<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
final class PregMatchAllAnalyzer
{
    /**
     * Compensate enforced flag
     * https://github.com/nette/utils/blob/e3dd1853f56ee9a68bfbb2e011691283c2ed420d/src/Utils/Strings.php#L487 See
     * https://stackoverflow.com/a/61424319/1348344
     *
     * @param Arg[] $args
     * @return Arg[]
     */
    public function compensateEnforcedFlag(string $methodName, \PhpParser\Node\Expr\FuncCall $funcCall, array $args) : array
    {
        if ($methodName !== 'matchAll') {
            return $args;
        }
        if (\count($funcCall->args) !== 3) {
            return $args;
        }
        $constFetch = new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('PREG_SET_ORDER'));
        $minus = new \PhpParser\Node\Expr\BinaryOp\Minus($constFetch, new \PhpParser\Node\Scalar\LNumber(1));
        $args[] = new \PhpParser\Node\Arg($minus);
        return $args;
    }
}
