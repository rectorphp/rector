<?php

declare (strict_types=1);
namespace Rector\Nette\NodeResolver;

use PhpParser\Node;
use Rector\Nette\Contract\FormControlTypeResolverInterface;
final class MethodNamesByInputNamesResolver
{
    /**
     * @var FormControlTypeResolverInterface[]
     */
    private $formControlTypeResolvers;
    /**
     * @param FormControlTypeResolverInterface[] $formControlTypeResolvers
     */
    public function __construct(array $formControlTypeResolvers)
    {
        $this->formControlTypeResolvers = $formControlTypeResolvers;
    }
    /**
     * @return array<string, string>
     */
    public function resolveExpr(\PhpParser\Node $node) : array
    {
        $methodNamesByInputNames = [];
        foreach ($this->formControlTypeResolvers as $formControlTypeResolver) {
            $currentMethodNamesByInputNames = $formControlTypeResolver->resolve($node);
            $methodNamesByInputNames = \array_merge($methodNamesByInputNames, $currentMethodNamesByInputNames);
        }
        return $methodNamesByInputNames;
    }
}
