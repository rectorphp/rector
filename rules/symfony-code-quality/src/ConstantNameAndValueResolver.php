<?php

declare(strict_types=1);

namespace Rector\SymfonyCodeQuality;

use PhpParser\Node\Attribute;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\SymfonyCodeQuality\ValueObject\ConstantNameAndValue;

final class ConstantNameAndValueResolver
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ConstantNameAndValueMatcher
     */
    private $constantNameAndValueMatcher;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        ConstantNameAndValueMatcher $constantNameAndValueMatcher
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->constantNameAndValueMatcher = $constantNameAndValueMatcher;
    }

    /**
     * @param Attribute[] $routeAttributes
     * @return ConstantNameAndValue[]
     */
    public function resolveFromAttributes(array $routeAttributes, string $prefixForNumeric): array
    {
        $constantNameAndValues = [];

        foreach ($routeAttributes as $routeAttribute) {
            foreach ($routeAttribute->args as $arg) {
                if (! $this->nodeNameResolver->isName($arg, 'name')) {
                    continue;
                }

                $constantNameAndValue = $this->constantNameAndValueMatcher->matchFromArg($arg, $prefixForNumeric);
                if (! $constantNameAndValue instanceof ConstantNameAndValue) {
                    continue;
                }

                $constantNameAndValues[] = $constantNameAndValue;
            }
        }

        return $constantNameAndValues;
    }
}
