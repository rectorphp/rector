<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\Scope;

use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Stubs\DummyTraitClass;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;

final class TraitScopeFaker
{
    public function __construct(
        private PrivatesAccessor $privatesAccessor,
        private ReflectionProvider $reflectionProvider
    ) {
    }

    public function createDummyClassScopeContext(MutatingScope $mutatingScope): ScopeContext
    {
        // this has to be faked, because trait PHPStan does not traverse trait without a class
        /** @var ScopeContext $scopeContext */
        $scopeContext = $this->privatesAccessor->getPrivateProperty($mutatingScope, 'context');
        $dummyClassReflection = $this->reflectionProvider->getClass(DummyTraitClass::class);

        // faking a class reflection
        return ScopeContext::create($scopeContext->getFile())
            ->enterClass($dummyClassReflection);
    }
}
