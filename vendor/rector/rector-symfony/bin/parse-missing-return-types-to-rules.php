<?php

namespace RectorPrefix202301;

// parses diff with expected missing types to Rector Return upgrade rule configuration
// https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
use RectorPrefix202301\Nette\Utils\FileSystem;
use RectorPrefix202301\Nette\Utils\Strings;
use Rector\Symfony\Utils\ValueObject\ReturnTypeChange;
use RectorPrefix202301\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix202301\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
require __DIR__ . '/../vendor/autoload.php';
final class MissingReturnTypeParser
{
    /**
     * @var string
     * @see original https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
     */
    private const RAW_SOURCE_FILE = 'https://raw.githubusercontent.com/symfony/symfony/6.1/.github/expected-missing-return-types.diff';
    /**
     * @see https://regex101.com/r/DrFXln/1
     */
    private const DIFF_LINES_REGEX = '#\\-(?<before>.*?)\\n\\+(?<after>.*?)\\n#m';
    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    public function __construct()
    {
        $symfonyStyleFactory = new SymfonyStyleFactory();
        $this->symfonyStyle = $symfonyStyleFactory->create();
    }
    public function run() : void
    {
        $returnTypeChanges = $this->resolveDiffFileToReturnTypeChanges(self::RAW_SOURCE_FILE);
        $configurationContent = '';
        foreach ($returnTypeChanges as $returnTypeChange) {
            $configurationContent .= \sprintf("('%s', '%s', %s)" . \PHP_EOL, $returnTypeChange->getClass(), $returnTypeChange->getMethod(), $returnTypeChange->getReturnType());
        }
        $this->symfonyStyle->writeln($configurationContent);
        $successMessage = \sprintf('The file is generated successfully with %d return types', \count($returnTypeChanges));
        $this->symfonyStyle->success($successMessage);
    }
    /**
     * @return ReturnTypeChange[]
     */
    private function resolveDiffFileToReturnTypeChanges(string $fileDiffPath) : array
    {
        $diffFileContent = FileSystem::read($fileDiffPath);
        $fileDiffs = \explode('diff --git', $diffFileContent);
        $returnTypeChanges = [];
        foreach ($fileDiffs as $fileDiff) {
            $matches = Strings::matchAll($fileDiff, self::DIFF_LINES_REGEX);
            if ($matches === []) {
                continue;
            }
            // match file name
            $filenameMatch = Strings::match($matches[0]['before'], '# a/src/(?<filename>.*?).php$#');
            if ($filenameMatch === null) {
                continue;
            }
            $className = \str_replace('/', '\\', $filenameMatch['filename']);
            unset($matches[0]);
            foreach ($matches as $match) {
                // match method name
                $methodNameMatch = Strings::match($match['before'], '#(?<method_name>\\w+)\\(#');
                $newTypeMatch = Strings::match($match['after'], '#\\): (?<return_type>.*?);?$#');
                $returnTypeChanges[] = new ReturnTypeChange($className, $methodNameMatch['method_name'], $newTypeMatch['return_type']);
            }
        }
        return $returnTypeChanges;
    }
}
$missingReturnTypeParser = new MissingReturnTypeParser();
$missingReturnTypeParser->run();
