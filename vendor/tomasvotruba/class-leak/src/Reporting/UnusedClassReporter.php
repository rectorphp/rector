<?php

declare (strict_types=1);
namespace RectorPrefix202609\TomasVotruba\ClassLeak\Reporting;

use RectorPrefix202609\Entropy\Console\Enum\ExitCode;
use RectorPrefix202609\Entropy\Console\Output\OutputPrinter;
use RectorPrefix202609\Nette\Utils\Json;
use RectorPrefix202609\TomasVotruba\ClassLeak\ValueObject\FileWithClass;
use RectorPrefix202609\TomasVotruba\ClassLeak\ValueObject\UnusedClassesResult;
final class UnusedClassReporter
{
    /**
     * @readonly
     */
    private OutputPrinter $outputPrinter;
    public function __construct(OutputPrinter $outputPrinter)
    {
        $this->outputPrinter = $outputPrinter;
    }
    /**
     * @return ExitCode::*
     */
    public function reportResult(UnusedClassesResult $unusedClassesResult, bool $isJson): int
    {
        if ($isJson) {
            $jsonResult = ['unused_class_count' => $unusedClassesResult->getCount(), 'unused_parent_less_classes' => $unusedClassesResult->getParentLessFileWithClasses(), 'unused_classes_with_parents' => $unusedClassesResult->getWithParentsFileWithClasses(), 'unused_traits' => $unusedClassesResult->getTraits()];
            $this->outputPrinter->writeln(Json::encode($jsonResult, Json::PRETTY));
            return ExitCode::SUCCESS;
        }
        $this->outputPrinter->newline(2);
        if ($unusedClassesResult->getCount() === 0) {
            $this->outputPrinter->success('All services are used. Great job!');
            return ExitCode::SUCCESS;
        }
        // separate with and without parent, as first one can be removed more easily
        if ($unusedClassesResult->getWithParentsFileWithClasses() !== []) {
            $this->printLineWIthClasses('Classes with a parent/interface', $unusedClassesResult->getWithParentsFileWithClasses());
        }
        if ($unusedClassesResult->getParentLessFileWithClasses() !== []) {
            $this->printLineWIthClasses('Classes without any parent/interface - easier to remove', $unusedClassesResult->getParentLessFileWithClasses());
        }
        if ($unusedClassesResult->getTraits() !== []) {
            $this->printLineWIthClasses('Unused traits - the easiest to remove', $unusedClassesResult->getTraits());
        }
        $this->outputPrinter->newline();
        $this->outputPrinter->error(sprintf('Found %d unused classes. Remove them or skip them using "--skip-type" option', $unusedClassesResult->getCount()));
        return ExitCode::ERROR;
    }
    /**
     * @param FileWithClass[] $fileWithClasses
     */
    private function printLineWIthClasses(string $title, array $fileWithClasses): void
    {
        $this->outputPrinter->newline();
        $this->outputPrinter->title($title);
        foreach ($fileWithClasses as $fileWithClass) {
            $this->outputPrinter->writeln($fileWithClass->getFilePath());
        }
    }
}
