<?php

declare(strict_types=1);

namespace Rector\AnonymousClass\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeNameResolver\NodeNameResolver;

final class ClassNodeAnalyzer
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function isAnonymousClass(Node $node): bool
    {
        if (! $node instanceof Class_) {
            return false;
        }

        $className = $this->nodeNameResolver->getName($node);
        if ($className === null) {
            return true;
        }

        // match PHPStan pattern for anonymous classes
        return (bool) Strings::match($className, '#AnonymousClass\w+$#');
    }
}
