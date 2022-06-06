<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Expr\CallLike;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\New_;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Scalar\LNumber;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\NodeFactory;
final class LiteralCallLikeConstFetchReplacer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @template TCallLike as MethodCall|New_|StaticCall
     *
     * @param TCallLike $callLike
     * @param array<string|int, string> $constantMap
     * @return null|\PhpParser\Node\Expr\CallLike
     */
    public function replaceArgOnPosition(CallLike $callLike, int $argPosition, string $className, array $constantMap)
    {
        $args = $callLike->getArgs();
        if (!isset($args[$argPosition])) {
            return null;
        }
        $arg = $args[$argPosition];
        if (!$arg->value instanceof String_ && !$arg->value instanceof LNumber) {
            return null;
        }
        $scalar = $arg->value;
        $constantName = $constantMap[$scalar->value] ?? null;
        if ($constantName === null) {
            return null;
        }
        $classConstFetch = $this->nodeFactory->createClassConstFetch($className, $constantName);
        $arg->value = $classConstFetch;
        return $callLike;
    }
}
