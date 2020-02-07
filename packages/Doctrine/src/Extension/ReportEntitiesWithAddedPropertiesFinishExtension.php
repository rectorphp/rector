<?php

declare(strict_types=1);

namespace Rector\Doctrine\Extension;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Rector\Core\Contract\Extension\FinishingExtensionInterface;
use Rector\Doctrine\Collector\UuidMigrationDataCollector;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ReportEntitiesWithAddedPropertiesFinishExtension implements FinishingExtensionInterface
{
    /**
     * @var UuidMigrationDataCollector
     */
    private $uuidMigrationDataCollector;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(
        UuidMigrationDataCollector $uuidMigrationDataCollector,
        SymfonyStyle $symfonyStyle
    ) {
        $this->uuidMigrationDataCollector = $uuidMigrationDataCollector;
        $this->symfonyStyle = $symfonyStyle;
    }

    public function run(): void
    {
        $this->generatePropertiesJsonWithFileName(
            'uuid-migration-new-column-properties.json',
            $this->uuidMigrationDataCollector->getColumnPropertiesByClass()
        );

        $this->generatePropertiesJsonWithFileName(
            'uuid-migration-new-relation-properties.json',
            $this->uuidMigrationDataCollector->getRelationPropertiesByClass()
        );
    }

    /**
     * @param mixed[] $data
     */
    private function generatePropertiesJsonWithFileName(string $fileName, array $data): void
    {
        if ($data === []) {
            return;
        }

        $jsonContent = Json::encode(['new_columns_by_class' => $data], Json::PRETTY);

        $filePath = getcwd() . '/' . $fileName;
        FileSystem::write($filePath, $jsonContent);

        $this->symfonyStyle->warning(sprintf('See freshly created "%s" file for changes on entities', $fileName));
    }
}
