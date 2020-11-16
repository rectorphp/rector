<?php
declare(strict_types=1);

namespace Rector\PHPStanExtensions\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeFinder;
use Symplify\PHPStanRules\Naming\SimpleNameResolver;
use Symplify\PHPStanRules\ValueObject\PHPStanAttributeKey;

final class SymfonyConfigRectorValueObjectResolver
{
    /**
     * @var string
     */
    private const INLINE_FUNCTION_NAME = 'Rector\SymfonyPhpConfig\inline_value_objects';

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

        /** @var FuncCall|null $inlineFuncCall */
        $inlineFuncCall = $this->nodeFinder->findFirst($parent, function (Node $node): bool {
            if (! $node instanceof FuncCall) {
                return false;
            }

            return $this->simpleNameResolver->isName($node->name, self::INLINE_FUNCTION_NAME);
        });

        if ($inlineFuncCall === null) {
            return null;
        }

        /** @var New_|null $new */
        $new = $this->nodeFinder->findFirstInstanceOf($inlineFuncCall, New_::class);
        if ($new === null) {
            return null;
        }

        return $this->simpleNameResolver->getName($new->class);
    }
}
