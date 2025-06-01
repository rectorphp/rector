<?php

/**
 * CodeClimate Specification:
 * - https://github.com/codeclimate/platform/blob/master/spec/analyzers/SPEC.md
 */
declare (strict_types=1);
namespace Rector\ChangesReporting\Output;

use RectorPrefix202506\Nette\Utils\Json;
use Rector\ChangesReporting\Contract\Output\OutputFormatterInterface;
use Rector\Util\FileHasher;
use Rector\ValueObject\Configuration;
use Rector\ValueObject\ProcessResult;
final class GitlabOutputFormatter implements OutputFormatterInterface
{
    /**
     * @readonly
     */
    private Filehasher $filehasher;
    /**
     * @var string
     */
    public const NAME = 'gitlab';
    private const ERROR_TYPE_ISSUE = 'issue';
    private const ERROR_CATEGORY_BUG_RISK = 'Bug Risk';
    private const ERROR_CATEGORY_STYLE = 'Style';
    private const ERROR_SEVERITY_BLOCKER = 'blocker';
    private const ERROR_SEVERITY_MINOR = 'minor';
    public function __construct(Filehasher $filehasher)
    {
        $this->filehasher = $filehasher;
    }
    public function getName() : string
    {
        return self::NAME;
    }
    public function report(ProcessResult $processResult, Configuration $configuration) : void
    {
        $errorsJson = \array_merge($this->appendSystemErrors($processResult, $configuration), $this->appendFileDiffs($processResult, $configuration));
        $json = Json::encode($errorsJson, \true);
        echo $json . \PHP_EOL;
    }
    /**
     * @return array<array{
     *      type: 'issue',
     *      categories: array{'Bug Risk'},
     *      severity: 'blocker',
     *      description: string,
     *      check_name: string,
     *      location: array{
     *          path: string,
     *          lines: array{
     *              begin: int,
     *          },
     *      },
     *  }>
     */
    private function appendSystemErrors(ProcessResult $processResult, Configuration $configuration) : array
    {
        $errorsJson = [];
        foreach ($processResult->getSystemErrors() as $systemError) {
            $filePath = $configuration->isReportingWithRealPath() ? $systemError->getAbsoluteFilePath() ?? '' : $systemError->getRelativeFilePath() ?? '';
            $fingerprint = $this->filehasher->hash($filePath . ';' . $systemError->getLine() . ';' . $systemError->getMessage());
            $errorsJson[] = ['fingerprint' => $fingerprint, 'type' => self::ERROR_TYPE_ISSUE, 'categories' => [self::ERROR_CATEGORY_BUG_RISK], 'severity' => self::ERROR_SEVERITY_BLOCKER, 'description' => $systemError->getMessage(), 'check_name' => $systemError->getRectorClass() ?? '', 'location' => ['path' => $filePath, 'lines' => ['begin' => $systemError->getLine() ?? 0]]];
        }
        return $errorsJson;
    }
    /**
     * @return array<array{
     *      type: 'issue',
     *      categories: array{'Style'},
     *      description: string,
     *      check_name: string,
     *      location: array{
     *          path: string,
     *          lines: array{
     *              begin: int,
     *          },
     *      },
     *  }>
     */
    private function appendFileDiffs(ProcessResult $processResult, Configuration $configuration) : array
    {
        $errorsJson = [];
        $fileDiffs = $processResult->getFileDiffs();
        \ksort($fileDiffs);
        foreach ($fileDiffs as $fileDiff) {
            $filePath = $configuration->isReportingWithRealPath() ? $fileDiff->getAbsoluteFilePath() ?? '' : $fileDiff->getRelativeFilePath() ?? '';
            $rectorClasses = \implode(' / ', $fileDiff->getRectorShortClasses());
            $fingerprint = $this->filehasher->hash($filePath . ';' . $fileDiff->getDiff());
            $errorsJson[] = ['fingerprint' => $fingerprint, 'type' => self::ERROR_TYPE_ISSUE, 'categories' => [self::ERROR_CATEGORY_STYLE], 'severity' => self::ERROR_SEVERITY_MINOR, 'description' => $rectorClasses, 'content' => ['body' => $fileDiff->getDiff()], 'check_name' => $rectorClasses, 'location' => ['path' => $filePath, 'lines' => ['begin' => $fileDiff->getFirstLineNumber() ?? 0]]];
        }
        return $errorsJson;
    }
}
