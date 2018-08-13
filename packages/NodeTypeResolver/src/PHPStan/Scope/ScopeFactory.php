<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\Scope;

use PHPStan\Analyser\Scope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\ScopeFactory as PHPStanScopeFactory;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Broker\Broker;
use Rector\Printer\BetterStandardPrinter;

final class ScopeFactory
{
    /**
     * @var Broker
     */
    private $broker;

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
        TypeSpecifier $typeSpecifier,
        PHPStanScopeFactory $phpStanScopeFactory,
        BetterStandardPrinter $betterStandardPrinter
    ) {
        $this->broker = $broker;
        $this->typeSpecifier = $typeSpecifier;
        $this->phpStanScopeFactory = $phpStanScopeFactory;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    public function createFromFile(string $filePath): Scope
    {
        return new Scope(
            $this->phpStanScopeFactory,
            $this->broker,
            $this->betterStandardPrinter,
            $this->typeSpecifier,
            ScopeContext::create($filePath)
        );
    }
}
