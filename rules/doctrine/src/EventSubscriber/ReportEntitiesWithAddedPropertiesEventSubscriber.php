<?php

declare(strict_types=1);

namespace Rector\Doctrine\EventSubscriber;

use Nette\Utils\Json;
use Rector\Core\EventDispatcher\Event\AfterProcessEvent;
use Rector\Doctrine\Collector\UuidMigrationDataCollector;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symplify\SmartFileSystem\SmartFileSystem;

final class ReportEntitiesWithAddedPropertiesEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var UuidMigrationDataCollector
     */
    private $uuidMigrationDataCollector;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(
        SmartFileSystem $smartFileSystem,
        SymfonyStyle $symfonyStyle,
        UuidMigrationDataCollector $uuidMigrationDataCollector
    ) {
        $this->uuidMigrationDataCollector = $uuidMigrationDataCollector;
        $this->symfonyStyle = $symfonyStyle;
        $this->smartFileSystem = $smartFileSystem;
    }

    public function reportEntities(): void
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
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [AfterProcessEvent::class => 'reportEntities'];
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
        $this->smartFileSystem->dumpFile($filePath, $jsonContent);
        $message = sprintf('See freshly created "%s" file for changes on entities', $fileName);

        $this->symfonyStyle->warning($message);
    }
}
