<?php

declare (strict_types=1);
namespace Rector\Nette\Contract;

use Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver;
interface MethodNamesByInputNamesResolverAwareInterface
{
    public function setResolver(\Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver $methodNamesByInputNamesResolver) : void;
}
