<?php

declare (strict_types=1);
namespace Rector\PHPStan;

use PhpParser\Node;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\Scope;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ScopeFetcher
{
    public static function fetch(Node $node) : Scope
    {
        /** @var MutatingScope|null $currentScope */
        $currentScope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$currentScope instanceof Scope) {
            $errorMessage = \sprintf('Scope not available on "%s" node. Fix scope refresh on changed nodes first', \get_class($node));
            throw new ShouldNotHappenException($errorMessage);
        }
        return $currentScope;
    }
}
