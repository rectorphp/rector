<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\Contract;

interface ClassSyncerInterface
{
    public function getSourceFilePath(): string;

    public function getTargetFilePath(): string;

    public function sync(): void;
}
