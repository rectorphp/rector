<?php

declare (strict_types=1);
namespace Rector\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Identifier;
use Rector\NodeNameResolver\NodeNameResolver;
final class ArgsAnalyzer
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param Arg[] $args
     */
    public function hasNamedArg(array $args): bool
    {
        $found = \false;
        foreach ($args as $arg) {
            if ($arg->name instanceof Identifier) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
    /**
     * @param Arg[] $args
     */
    public function resolveArgPosition(array $args, string $name, int $defaultPosition): int
    {
        foreach ($args as $position => $arg) {
            if (!$arg->name instanceof Identifier) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($arg->name, $name)) {
                continue;
            }
            return $position;
        }
        return $defaultPosition;
    }
    /**
     * @param Arg[] $args
     */
    public function resolveFirstNamedArgPosition(array $args): ?int
    {
        $position = 0;
        foreach ($args as $arg) {
            if ($arg->name instanceof Identifier) {
                return $position;
            }
            ++$position;
        }
        return null;
    }
}
