<?php

/**
 * Reports errors in pull-requests diff when run in a GitHub Action
 * @see https://help.github.com/en/actions/reference/workflow-commands-for-github-actions#setting-an-error-message
 * @see https://github.com/actions/toolkit/blob/main/packages/core/src/command.ts
 *
 * @todo: print endLine property. For now in GitHub’s PR display (specifically in GitHub Actions annotations), the "endLine" property is known to be bugged.
 * @see https://github.com/orgs/community/discussions/129899
 */
declare (strict_types=1);
namespace Rector\ChangesReporting\Output;

use Rector\ChangesReporting\Contract\Output\OutputFormatterInterface;
use Rector\ValueObject\Configuration;
use Rector\ValueObject\ProcessResult;
/**
 * @phpstan-type AnnotationProperties array{title?: string|null, file?: string|null, col?: int|null, endColumn?: int|null, line?: int|null, endLine?: int|null}
 */
final class GitHubOutputFormatter implements OutputFormatterInterface
{
    /**
     * @var string
     */
    public const NAME = 'github';
    private const GROUP_NAME = 'Rector report';
    public function getName() : string
    {
        return self::NAME;
    }
    public function report(ProcessResult $processResult, Configuration $configuration) : void
    {
        $this->startGroup();
        $this->reportSystemErrors($processResult, $configuration);
        $this->reportFileDiffs($processResult, $configuration);
        $this->endGroup();
    }
    private function startGroup() : void
    {
        echo \sprintf('::group::%s', self::GROUP_NAME) . \PHP_EOL;
    }
    private function endGroup() : void
    {
        echo '::endgroup::' . \PHP_EOL;
    }
    private function reportSystemErrors(ProcessResult $processResult, Configuration $configuration) : void
    {
        foreach ($processResult->getSystemErrors() as $systemError) {
            $filePath = $configuration->isReportingWithRealPath() ? $systemError->getAbsoluteFilePath() : $systemError->getRelativeFilePath();
            $line = $systemError->getLine();
            $message = \trim($systemError->getRectorShortClass() . \PHP_EOL . $systemError->getMessage());
            $this->reportErrorAnnotation($message, ['file' => $filePath, 'line' => $line]);
        }
    }
    private function reportFileDiffs(ProcessResult $processResult, Configuration $configuration) : void
    {
        $fileDiffs = $processResult->getFileDiffs();
        \ksort($fileDiffs);
        foreach ($fileDiffs as $fileDiff) {
            $filePath = $configuration->isReportingWithRealPath() ? $fileDiff->getAbsoluteFilePath() : $fileDiff->getRelativeFilePath();
            $line = $fileDiff->getFirstLineNumber();
            $endLine = $fileDiff->getLastLineNumber();
            $message = \trim(\implode(' / ', $fileDiff->getRectorShortClasses())) . \PHP_EOL . \PHP_EOL . $fileDiff->getDiff();
            $this->reportErrorAnnotation($message, ['file' => $filePath, 'line' => $line, 'endLine' => $endLine]);
        }
    }
    /**
     * @param AnnotationProperties $annotationProperties
     */
    private function reportErrorAnnotation(string $message, array $annotationProperties) : void
    {
        $properties = $this->sanitizeAnnotationProperties($annotationProperties);
        $command = \sprintf('::error %s::%s', $properties, $message);
        // Sanitize command
        $command = \str_replace(['%', "\r", "\n"], ['%25', '%0D', '%0A'], $command);
        echo $command . \PHP_EOL;
    }
    /**
     * @param AnnotationProperties $annotationProperties
     */
    private function sanitizeAnnotationProperties(array $annotationProperties) : string
    {
        if (!isset($annotationProperties['line']) || !$annotationProperties['line']) {
            $annotationProperties['line'] = 0;
        }
        // This is a workaround for buggy endLine. See https://github.com/orgs/community/discussions/129899
        // TODO: Should be removed once github will have fixed it issue.
        unset($annotationProperties['endLine']);
        $nonNullProperties = \array_filter($annotationProperties, static fn($value): bool => $value !== null);
        $sanitizedProperties = \array_map(fn($key, $value): string => \sprintf('%s=%s', $key, $this->sanitizeAnnotationProperty($value)), \array_keys($nonNullProperties), $nonNullProperties);
        return \implode(',', $sanitizedProperties);
    }
    /**
     * @param string|int|null $value
     */
    private function sanitizeAnnotationProperty($value) : string
    {
        if ($value === null || $value === '') {
            return '';
        }
        $value = (string) $value;
        return \str_replace(['%', "\r", "\n", ':', ','], ['%25', '%0D', '%0A', '%3A', '%2C'], $value);
    }
}
