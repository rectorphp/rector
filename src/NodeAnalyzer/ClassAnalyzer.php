<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ClassAnalyzer
{
    /**
     * @var string
     * @see https://regex101.com/r/FQH6RT/1
     */
    private const ANONYMOUS_CLASS_REGEX = '#AnonymousClass\w+$#';

    public function __construct(
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function isAnonymousClass(Node $node): bool
    {
        if (! $node instanceof Class_) {
            return false;
        }

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof New_) {
            return false;
        }

        if ($node->isAnonymous()) {
            return true;
        }

        $className = $this->nodeNameResolver->getName($node);
        if ($className === null) {
            return true;
        }

        // match PHPStan pattern for anonymous classes
        return (bool) Strings::match($className, self::ANONYMOUS_CLASS_REGEX);
    }
}
