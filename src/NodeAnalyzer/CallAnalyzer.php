<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class CallAnalyzer
{
    /**
     * @var array<class-string<Expr>>
     */
    private const OBJECT_CALLS = [MethodCall::class, NullsafeMethodCall::class, StaticCall::class];

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function isObjectCall(Node $node): bool
    {
        return $this->nodeTypeResolver->isObjectTypes($node, self::OBJECT_CALLS);
    }
}
