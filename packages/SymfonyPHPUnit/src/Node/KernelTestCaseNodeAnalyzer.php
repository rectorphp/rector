<?php declare(strict_types=1);

namespace Rector\SymfonyPHPUnit\Node;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class KernelTestCaseNodeAnalyzer
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(NameResolver $nameResolver)
    {
        $this->nameResolver = $nameResolver;
    }

    /**
     * Matches:
     * self::$container->get()
     */
    public function isSelfContainerGetMethodCall(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $node->var instanceof StaticPropertyFetch) {
            return false;
        }

        if (! $this->nameResolver->isName($node->var->class, 'self')) {
            return false;
        }

        if (! $this->nameResolver->isName($node->var->name, 'container')) {
            return false;
        }

        return $this->nameResolver->isName($node->name, 'get');
    }
}
