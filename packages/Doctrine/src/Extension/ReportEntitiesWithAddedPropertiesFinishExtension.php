<?php declare(strict_types=1);

namespace Rector\Doctrine\Extension;

use Nette\Utils\Json;
use Rector\Contract\Extension\FinishingExtensionInterface;
use Rector\Doctrine\Collector\EntityWithAddedPropertyCollector;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ReportEntitiesWithAddedPropertiesFinishExtension implements FinishingExtensionInterface
{
    /**
     * @var EntityWithAddedPropertyCollector
     */
    private $entityWithAddedPropertyCollector;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(
        EntityWithAddedPropertyCollector $entityWithAddedPropertyCollector,
        SymfonyStyle $symfonyStyle
    ) {
        $this->entityWithAddedPropertyCollector = $entityWithAddedPropertyCollector;
        $this->symfonyStyle = $symfonyStyle;
    }

    public function run(): void
    {
        $propertiesByClass = $this->entityWithAddedPropertyCollector->getPropertiesByClass();
        if ($propertiesByClass === []) {
            return;
        }

        $data = [
            'title' => 'Entities with new properties',
            'added_properties_by_class' => $propertiesByClass,
        ];

        $jsonContent = Json::encode($data, Json::PRETTY);

        $this->symfonyStyle->writeln($jsonContent);
    }
}
