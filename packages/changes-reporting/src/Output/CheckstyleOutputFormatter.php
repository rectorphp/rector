<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\Output;

use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\ChangesReporting\Contract\Output\OutputFormatterInterface;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Inspired by https://github.com/phpstan/phpstan-src/commit/fa1f416981438b80e2f39eabd9f1b62fca9a6803#diff-7a7d635d9f9cf3388e34d414731dece3
 */
final class CheckstyleOutputFormatter implements OutputFormatterInterface
{
    /**
     * @var string
     */
    public const NAME = 'checkstyle';

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function report(ErrorAndDiffCollector $errorAndDiffCollector): void
    {
        $this->symfonyStyle->writeln('<?xml version="1.0" encoding="UTF-8"?>');
        $this->symfonyStyle->writeln('<checkstyle>');

        foreach ($errorAndDiffCollector->getFileDiffs() as $fileDiff) {
            $this->writeFileErrors($fileDiff);
        }

        $this->writeNonFileErrors($errorAndDiffCollector);

        $this->symfonyStyle->writeln('</checkstyle>');
    }

    private function writeFileErrors(FileDiff $fileDiff): void
    {
        $message = sprintf('<file name="%s">', $this->escape($fileDiff->getRelativeFilePath()));
        $this->symfonyStyle->writeln($message);

        foreach ($fileDiff->getRectorChanges() as $rectorWithFileAndLineChange) {
            $message = $rectorWithFileAndLineChange->getRectorDefinitionsDescription() . ' (Reported by: ' . $rectorWithFileAndLineChange->getRectorClass() . ')';
            $message = $this->escape($message);

            $error = sprintf(
                '  <error line="%d" column="1" severity="error" message="%s" />',
                $this->escape((string) $rectorWithFileAndLineChange->getLine()),
                $message
            );
            $this->symfonyStyle->writeln($error);
        }

        $this->symfonyStyle->writeln('</file>');
    }

    private function writeNonFileErrors(ErrorAndDiffCollector $errorAndDiffCollector): void
    {
        if ($errorAndDiffCollector->getErrors() !== []) {
            $this->symfonyStyle->writeln('<file>');

            foreach ($errorAndDiffCollector->getErrors() as $rectorError) {
                $escapedMessage = $this->escape($rectorError->getMessage());
                $message = sprintf('    <error severity="error" message="%s" />', $escapedMessage);

                $this->symfonyStyle->writeln($message);
            }

            $this->symfonyStyle->writeln('</file>');
        }
    }

    private function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
