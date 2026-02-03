<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\NodeFinder;

use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
final class PropertyFetchUsageFinder
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @return PropertyFetch[]
     */
    public function findInNew(Class_ $class, string $propertyName): array
    {
        /** @var New_[] $news */
        $news = $this->betterNodeFinder->findInstancesOfScoped($class->getMethods(), New_::class);
        $propertyFetchesInNewArgs = [];
        foreach ($news as $new) {
            if ($new->isFirstClassCallable()) {
                continue;
            }
            foreach ($new->getArgs() as $arg) {
                if (!$arg->value instanceof PropertyFetch) {
                    continue;
                }
                if (!$this->nodeNameResolver->isName($arg->value->name, $propertyName)) {
                    continue;
                }
                $propertyFetchesInNewArgs[] = $arg->value;
            }
        }
        return $propertyFetchesInNewArgs;
    }
}
