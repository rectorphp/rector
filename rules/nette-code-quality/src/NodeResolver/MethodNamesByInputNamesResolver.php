<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\NodeResolver;

use PhpParser\Node;
use Rector\NetteCodeQuality\Contract\FormControlTypeResolverInterface;
use Rector\NetteCodeQuality\Contract\MethodNamesByInputNamesResolverAwareInterface;

final class MethodNamesByInputNamesResolver
{
    /**
     * @var FormControlTypeResolverInterface[]
     */
    private $formControlTypeResolvers = [];

    /**
     * @param FormControlTypeResolverInterface[] $formControlTypeResolvers
     */
    public function __construct(array $formControlTypeResolvers)
    {
        foreach ($formControlTypeResolvers as $formControlTypeResolver) {
            if ($formControlTypeResolver instanceof MethodNamesByInputNamesResolverAwareInterface) {
                $formControlTypeResolver->setResolver($this);
            }

            $this->formControlTypeResolvers[] = $formControlTypeResolver;
        }
    }

    /**
     * @return array<string, string>
     */
    public function resolveExpr(Node $node): array
    {
        $methodNamesByInputNames = [];

        foreach ($this->formControlTypeResolvers as $formControlTypeResolver) {
            $currentMethodNamesByInputNames = $formControlTypeResolver->resolve($node);
            $methodNamesByInputNames = array_merge($methodNamesByInputNames, $currentMethodNamesByInputNames);
        }

        return $methodNamesByInputNames;
    }
}
