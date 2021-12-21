<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope;

use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Stubs\DummyTraitClass;
use RectorPrefix20211221\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
final class TraitScopeFaker
{
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\RectorPrefix20211221\Symplify\PackageBuilder\Reflection\PrivatesAccessor $privatesAccessor, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->privatesAccessor = $privatesAccessor;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function createDummyClassScopeContext(\PHPStan\Analyser\MutatingScope $mutatingScope) : \PHPStan\Analyser\ScopeContext
    {
        // this has to be faked, because trait PHPStan does not traverse trait without a class
        /** @var ScopeContext $scopeContext */
        $scopeContext = $this->privatesAccessor->getPrivateProperty($mutatingScope, 'context');
        $dummyClassReflection = $this->reflectionProvider->getClass(\Rector\Core\Stubs\DummyTraitClass::class);
        // faking a class reflection
        return \PHPStan\Analyser\ScopeContext::create($scopeContext->getFile())->enterClass($dummyClassReflection);
    }
}
