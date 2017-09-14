<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\PerNodeValueResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\PrettyPrinter\Standard;
use Rector\Node\Attribute;
use Rector\NodeValueResolver\Contract\PerNodeValueResolver\PerNodeValueResolverInterface;

final class ClassConstantFetchValueResolver implements PerNodeValueResolverInterface
{
    /**
     * @var Standard
     */
    private $standardPrinter;

    public function getNodeClass(): string
    {
        return ClassConstFetch::class;
    }

    public function __construct(Standard $standardPrinter)
    {
        $this->standardPrinter = $standardPrinter;
    }

    /**
     * @param ClassConstFetch $classConstFetchNode
     * @return mixed
     */
    public function resolve(Node $classConstFetchNode)
    {
        $value = $this->standardPrinter->prettyPrint([$classConstFetchNode]);
        if ($value === 'static::class') {
            return $classConstFetchNode->getAttribute(Attribute::CLASS_NAME);
        }

        return null;
    }
}
