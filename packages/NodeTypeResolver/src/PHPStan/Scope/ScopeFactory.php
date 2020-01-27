<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\Scope;

use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\ScopeFactory as PHPStanScopeFactory;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\DependencyInjection\Type\DynamicReturnTypeExtensionRegistryProvider;
use PHPStan\DependencyInjection\Type\OperatorTypeSpecifyingExtensionRegistryProvider;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Properties\PropertyReflectionFinder;
use Rector\PhpParser\Printer\BetterStandardPrinter;

final class ScopeFactory
{
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    /**
     * @var TypeSpecifier
     */
    private $typeSpecifier;

    /**
     * @var PHPStanScopeFactory
     */
    private $phpStanScopeFactory;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var DynamicReturnTypeExtensionRegistryProvider
     */
    private $dynamicReturnTypeExtensionRegistryProvider;

    /**
     * @var OperatorTypeSpecifyingExtensionRegistryProvider
     */
    private $operatorTypeSpecifyingExtensionRegistryProvider;

    public function __construct(
        ReflectionProvider $reflectionProvider,
        TypeSpecifier $typeSpecifier,
        PHPStanScopeFactory $phpStanScopeFactory,
        BetterStandardPrinter $betterStandardPrinter,
        DynamicReturnTypeExtensionRegistryProvider $dynamicReturnTypeExtensionRegistryProvider,
        OperatorTypeSpecifyingExtensionRegistryProvider $operatorTypeSpecifyingExtensionRegistryProvider
    ) {
        $this->reflectionProvider = $reflectionProvider;
        $this->typeSpecifier = $typeSpecifier;
        $this->phpStanScopeFactory = $phpStanScopeFactory;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->dynamicReturnTypeExtensionRegistryProvider = $dynamicReturnTypeExtensionRegistryProvider;
        $this->operatorTypeSpecifyingExtensionRegistryProvider = $operatorTypeSpecifyingExtensionRegistryProvider;
    }

    public function createFromFile(string $filePath): Scope
    {
        return new MutatingScope(
            $this->phpStanScopeFactory,
            $this->reflectionProvider,
            $this->dynamicReturnTypeExtensionRegistryProvider->getRegistry(),
            $this->operatorTypeSpecifyingExtensionRegistryProvider->getRegistry(),
            $this->betterStandardPrinter,
            $this->typeSpecifier,
            new PropertyReflectionFinder(),
            ScopeContext::create($filePath)
        );
    }
}
