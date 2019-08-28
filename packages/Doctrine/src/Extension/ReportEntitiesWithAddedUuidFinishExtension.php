<?php declare(strict_types=1);

namespace Rector\Doctrine\Extension;

use Rector\Contract\Extension\FinishingExtensionInterface;
use Rector\Doctrine\Collector\EntitiesWithAddedUuidPropertyCollector;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ReportEntitiesWithAddedUuidFinishExtension implements FinishingExtensionInterface
{
    /**
     * @var EntitiesWithAddedUuidPropertyCollector
     */
    private $entitiesWithAddedUuidPropertyCollector;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(
        EntitiesWithAddedUuidPropertyCollector $entitiesWithAddedUuidPropertyCollector,
        SymfonyStyle $symfonyStyle
    ) {
        $this->entitiesWithAddedUuidPropertyCollector = $entitiesWithAddedUuidPropertyCollector;
        $this->symfonyStyle = $symfonyStyle;
    }

    public function run(): void
    {
        $classes = $this->entitiesWithAddedUuidPropertyCollector->getClasses();

        if ($classes === []) {
            return;
        }

        $this->symfonyStyle->section('Entities with new nullable $uuid property');
        $this->symfonyStyle->listing($classes);
    }
}
