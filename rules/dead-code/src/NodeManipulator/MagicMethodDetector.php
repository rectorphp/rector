<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeManipulator;

use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;

final class MagicMethodDetector
{
    /**
     * @var string[]
     */
    private const MAGIC_METHODS = [
        '__call',
        '__callStatic',
        MethodName::CLONE,
        '__debugInfo',
        MethodName::DESCTRUCT,
        '__get',
        '__invoke',
        '__isset',
        '__set',
        MethodName::SET_STATE,
        '__sleep',
        '__toString',
        '__unset',
        '__wakeup',
    ];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function isMagicMethod(ClassMethod $classMethod): bool
    {
        return $this->nodeNameResolver->isNames($classMethod, self::MAGIC_METHODS);
    }
}
