<?php

declare(strict_types=1);

namespace Rector\ZendToSymfony\Extension;

use Rector\Core\Contract\Extension\ReportingExtensionInterface;
use Rector\ZendToSymfony\Collector\RouteCollector;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ReportRoutesExtension implements ReportingExtensionInterface
{
    /**
     * @var RouteCollector
     */
    private $routeCollector;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(RouteCollector $routeCollector, SymfonyStyle $symfonyStyle)
    {
        $this->routeCollector = $routeCollector;
        $this->symfonyStyle = $symfonyStyle;
    }

    public function run(): void
    {
        $collectedRoutes = $this->routeCollector->getRouteValueObjects();
        if ($collectedRoutes === []) {
            return;
        }

        $tableLines = [];

        foreach ($this->routeCollector->getRouteValueObjects() as $routeValueObject) {
            $tableLines[] = [
                $routeValueObject->getControllerClass(),
                $routeValueObject->getMethodName(),
                $routeValueObject->getParamsAsString(),
            ];
        }

        $this->symfonyStyle->newLine(1);
        $this->symfonyStyle->title('Collected routes data');
        $this->symfonyStyle->table(['Controller', 'Method', 'Parameters'], $tableLines);
    }
}
