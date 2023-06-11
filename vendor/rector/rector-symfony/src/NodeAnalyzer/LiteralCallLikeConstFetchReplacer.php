<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\PhpParser\Node\NodeFactory;
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
     * @return TCallLike
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
        $arg->value = $this->nodeFactory->createClassConstFetch($className, $constantName);
        return $callLike;
    }
}
