<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\Scope;

use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\ScopeFactory as PHPStanScopeFactory;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Broker\Broker;
use PHPStan\DependencyInjection\Container;
use PHPStan\DependencyInjection\Type\DynamicReturnTypeExtensionRegistryProvider;
use PHPStan\DependencyInjection\Type\OperatorTypeSpecifyingExtensionRegistryProvider;
use PHPStan\Rules\Properties\PropertyReflectionFinder;
use Rector\PhpParser\Printer\BetterStandardPrinter;

final class ScopeFactory
{
    /**
     * @var Broker
     */
    private $broker;

    /**
     * @var \PHPStan\DependencyInjection\Container
     */
    private $container;

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

    public function __construct(
        Broker $broker,
        Container $container,
        TypeSpecifier $typeSpecifier,
        PHPStanScopeFactory $phpStanScopeFactory,
        BetterStandardPrinter $betterStandardPrinter
    ) {
        $this->broker = $broker;
        $this->container = $container;
        $this->typeSpecifier = $typeSpecifier;
        $this->phpStanScopeFactory = $phpStanScopeFactory;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    public function createFromFile(string $filePath): Scope
    {
        return new MutatingScope(
            $this->phpStanScopeFactory,
            $this->broker,
            $this->container->getByType(DynamicReturnTypeExtensionRegistryProvider::class)->getRegistry(),
            $this->container->getByType(OperatorTypeSpecifyingExtensionRegistryProvider::class)->getRegistry(),
            $this->betterStandardPrinter,
            $this->typeSpecifier,
            new PropertyReflectionFinder(),
            ScopeContext::create($filePath)
        );
    }
}
