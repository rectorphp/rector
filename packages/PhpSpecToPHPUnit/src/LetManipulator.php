<?php declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class LetManipulator
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(BetterNodeFinder $betterNodeFinder, NameResolver $nameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nameResolver = $nameResolver;
    }

    public function isLetNeededInClass(Class_ $class): bool
    {
        foreach ($class->getMethods() as $method) {
            // new test
            if ($this->nameResolver->isName($method, 'test*')) {
                continue;
            }

            $hasBeConstructedThrough = (bool) $this->betterNodeFinder->find(
                (array) $method->stmts,
                function (Node $node): ?bool {
                    if (! $node instanceof MethodCall) {
                        return null;
                    }

                    return $this->nameResolver->isName($node, 'beConstructedThrough');
                }
            );

            if ($hasBeConstructedThrough) {
                continue;
            }

            return true;
        }

        return false;
    }
}
