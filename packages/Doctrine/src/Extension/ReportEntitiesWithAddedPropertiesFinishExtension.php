<?php declare(strict_types=1);

namespace Rector\Doctrine\Extension;

use Rector\Contract\Extension\FinishingExtensionInterface;
use Rector\Doctrine\Collector\EntitiesWithAddedPropertyCollector;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ReportEntitiesWithAddedPropertiesFinishExtension implements FinishingExtensionInterface
{
    /**
     * @var EntitiesWithAddedPropertyCollector
     */
    private $entitiesWithAddedPropertyCollector;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(
        EntitiesWithAddedPropertyCollector $entitiesWithAddedPropertyCollector,
        SymfonyStyle $symfonyStyle
    ) {
        $this->entitiesWithAddedPropertyCollector = $entitiesWithAddedPropertyCollector;
        $this->symfonyStyle = $symfonyStyle;
    }

    public function run(): void
    {
        $classes = $this->entitiesWithAddedPropertyCollector->getPropertiesByClasses();

        if ($classes === []) {
            return;
        }

        $this->symfonyStyle->title('Entities with new properties');

        foreach ($this->entitiesWithAddedPropertyCollector->getPropertiesByClasses() as $class => $properties) {
            $this->symfonyStyle->section($class);
            $this->symfonyStyle->listing($properties);
            $this->symfonyStyle->newLine(1);
        }
    }
}
