<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\Scope;

use PHPStan\Analyser\Scope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\ScopeFactory as PHPStanScopeFactory;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Broker\Broker;
use Rector\Printer\BetterStandardPrinter;
use Symfony\Component\Finder\SplFileInfo;

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
    private $pHPStanScopeFactory;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(
        Broker $broker,
        TypeSpecifier $typeSpecifier,
        PHPStanScopeFactory $pHPStanScopeFactory,
        BetterStandardPrinter $betterStandardPrinter
    ) {
        $this->broker = $broker;
        $this->typeSpecifier = $typeSpecifier;
        $this->pHPStanScopeFactory = $pHPStanScopeFactory;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    public function createFromFileInfo(SplFileInfo $splFileInfo): Scope
    {
        return new Scope(
            $this->pHPStanScopeFactory,
            $this->broker,
            $this->betterStandardPrinter,
            $this->typeSpecifier,
            ScopeContext::create($splFileInfo->getRealPath())
        );
    }
}
