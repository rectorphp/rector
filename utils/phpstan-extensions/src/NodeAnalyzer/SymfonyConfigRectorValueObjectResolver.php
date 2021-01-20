<?php
declare(strict_types=1);

namespace Rector\PHPStanExtensions\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeFinder;
use Symplify\Astral\Naming\SimpleNameResolver;
use Symplify\PHPStanRules\ValueObject\PHPStanAttributeKey;

final class SymfonyConfigRectorValueObjectResolver
{
    /**
     * @var string
     */
    private const INLINE_CLASS_NAME = 'Symplify\SymfonyPhpConfig\ValueObjectInliner';

    /**
     * @var NodeFinder
     */
    private $nodeFinder;

    /**
     * @var SimpleNameResolver
     */
    private $simpleNameResolver;

    public function __construct(NodeFinder $nodeFinder, SimpleNameResolver $simpleNameResolver)
    {
        $this->nodeFinder = $nodeFinder;
        $this->simpleNameResolver = $simpleNameResolver;
    }

    public function resolveFromSetMethodCall(MethodCall $methodCall): ?string
    {
        $parent = $methodCall->getAttribute(PHPStanAttributeKey::PARENT);
        while (! $parent instanceof Expression) {
            $parent = $parent->getAttribute(PHPStanAttributeKey::PARENT);
        }

        $inlineStaticCall = $this->nodeFinder->findFirst($parent, function (Node $node): bool {
            if (! $node instanceof StaticCall) {
                return false;
            }

            return $this->simpleNameResolver->isName($node->class, self::INLINE_CLASS_NAME);
        });

        if (! $inlineStaticCall instanceof StaticCall) {
            return null;
        }

        $new = $this->nodeFinder->findFirstInstanceOf($inlineStaticCall, New_::class);
        if (! $new instanceof New_) {
            return null;
        }

        return $this->simpleNameResolver->getName($new->class);
    }
}
