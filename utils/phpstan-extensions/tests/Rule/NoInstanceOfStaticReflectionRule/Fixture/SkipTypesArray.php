<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\NoInstanceOfStaticReflectionRule\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;

final class SkipTypesArray
{
    /**
     * @var array<class-string<Node>>
     */
    private const COLLECTABLE_NODE_TYPES = [
        Class_::class,
        Interface_::class,
        ClassConst::class,
        ClassConstFetch::class,
        New_::class,
        StaticCall::class,
        MethodCall::class,
        Array_::class,
        Param::class,
    ];

    public function isCollectableNode(Node $node): bool
    {
        foreach (self::COLLECTABLE_NODE_TYPES as $collectableNodeType) {
            /** @var class-string<Node> $collectableNodeType */
            if (is_a($node, $collectableNodeType, true)) {
                return true;
            }
        }

        return false;
    }
}
