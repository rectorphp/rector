<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\DataProvider;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;

final class OnPropertyMagicCallProvider
{
    /**
     * Package "nette/application" is required for DEV, might not exist for PROD.
     * So access the class throgh the string
     *
     * @var string
     */
    private const CONTROL_CLASS = 'Nette\Application\UI\Control';

    /**
     * @var MethodCall[]
     */
    private $onPropertyMagicCalls = [];

    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver, NodeRepository $nodeRepository)
    {
        $this->nodeRepository = $nodeRepository;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @return MethodCall[]
     */
    public function provide(): array
    {
        if ($this->onPropertyMagicCalls !== [] && ! StaticPHPUnitEnvironment::isPHPUnitRun()) {
            return $this->onPropertyMagicCalls;
        }

        foreach ($this->nodeRepository->getMethodsCalls() as $methodCall) {
            $scope = $methodCall->getAttribute(AttributeKey::SCOPE);
            if (! $scope instanceof Scope) {
                continue;
            }

            if (! $this->isLocalOnPropertyCall($methodCall, $scope)) {
                continue;
            }

            $this->onPropertyMagicCalls[] = $methodCall;
        }

        return $this->onPropertyMagicCalls;
    }

    /**
     * Detects method call on, e.g:
     * public $onSomeProperty;
     */
    private function isLocalOnPropertyCall(MethodCall $methodCall, Scope $scope): bool
    {
        if ($methodCall->var instanceof StaticCall) {
            return false;
        }

        if ($methodCall->var instanceof MethodCall) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($methodCall->var, 'this')) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($methodCall->name, 'on*')) {
            return false;
        }

        $methodName = $this->nodeNameResolver->getName($methodCall->name);
        if ($methodName === null) {
            return false;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        // control event, inner only
        if ($classReflection->isSubclassOf(self::CONTROL_CLASS)) {
            return false;
        }

        if ($classReflection->hasMethod($methodName)) {
            return false;
        }

        return $classReflection->hasProperty($methodName);
    }
}
