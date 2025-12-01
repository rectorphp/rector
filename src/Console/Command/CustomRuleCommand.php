<?php

declare (strict_types=1);
namespace Rector\Console\Command;

use RectorPrefix202512\Nette\Utils\FileSystem;
use RectorPrefix202512\Nette\Utils\Strings;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Enum\ClassName;
use Rector\Exception\ShouldNotHappenException;
use Rector\FileSystem\JsonFileSystem;
use RectorPrefix202512\Symfony\Component\Console\Command\Command;
use RectorPrefix202512\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202512\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix202512\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix202512\Symfony\Component\Finder\Finder;
use RectorPrefix202512\Symfony\Component\Finder\SplFileInfo;
final class CustomRuleCommand extends Command
{
    /**
     * @readonly
     */
    private SymfonyStyle $symfonyStyle;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(SymfonyStyle $symfonyStyle, ReflectionProvider $reflectionProvider)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->reflectionProvider = $reflectionProvider;
        parent::__construct();
    }
    protected function configure(): void
    {
        $this->setName('custom-rule');
        $this->setDescription('Create base of local custom rule with tests');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // ask for rule name
        $rectorName = $this->symfonyStyle->ask('What is the rule class name? (e.g. "LegacyCallToDbalMethodCall")?', null, static function (?string $answer): string {
            if ($answer === '' || $answer === null) {
                throw new ShouldNotHappenException('Rector name cannot be empty');
            }
            return $answer;
        });
        // suffix with Rector by convention
        if (substr_compare((string) $rectorName, 'Rector', -strlen('Rector')) !== 0) {
            $rectorName .= 'Rector';
        }
        $rectorName = ucfirst((string) $rectorName);
        // find all files in templates directory
        $finder = Finder::create()->files()->in(__DIR__ . '/../../../templates/custom-rule')->notName('__Name__Test.php.phtml');
        // 0. resolve if local phpunit is at least PHPUnit 10 (which supports #[DataProvider])
        // to provide annotation if not
        if ($this->isPHPUnitAttributeSupported()) {
            $finder->append([new SplFileInfo(__DIR__ . '/../../../templates/custom-rule/utils/rector/tests/Rector/__Name__/__Name__Test.php.phtml', 'utils/rector/tests/Rector/__Name__', 'utils/rector/tests/Rector/__Name__/__Name__Test.php.phtml')]);
        } else {
            // use @annotations for PHPUnit 9 and bellow
            $finder->append([new SplFileInfo(__DIR__ . '/../../../templates/custom-rules-annotations/utils/rector/tests/Rector/__Name__/__Name__Test.php.phtml', 'utils/rector/tests/Rector/__Name__', 'utils/rector/tests/Rector/__Name__/__Name__Test.php.phtml')]);
        }
        $currentDirectory = getcwd();
        $generatedFilePaths = [];
        $fileInfos = iterator_to_array($finder->getIterator());
        foreach ($fileInfos as $fileInfo) {
            // replace "__Name__" with $rectorName
            $newContent = $this->replaceNameVariable($rectorName, $fileInfo->getContents());
            $newFilePath = $this->replaceNameVariable($rectorName, $fileInfo->getRelativePathname());
            // remove "phtml" suffix
            $newFilePath = Strings::substring($newFilePath, 0, -strlen('.phtml'));
            FileSystem::write($currentDirectory . '/' . $newFilePath, $newContent, null);
            $generatedFilePaths[] = $newFilePath;
        }
        $title = sprintf('Skeleton for "%s" rule was created. Now write rule logic to solve your problem', $rectorName);
        $this->symfonyStyle->title($title);
        $this->symfonyStyle->listing($generatedFilePaths);
        // 2. update autoload-dev in composer.json
        $composerJsonFilePath = $currentDirectory . '/composer.json';
        if (file_exists($composerJsonFilePath)) {
            $hasChanged = \false;
            $composerJson = JsonFileSystem::readFilePath($composerJsonFilePath);
            if (!isset($composerJson['autoload-dev']['psr-4']['Utils\Rector\\'])) {
                $composerJson['autoload-dev']['psr-4']['Utils\Rector\\'] = 'utils/rector/src';
                $composerJson['autoload-dev']['psr-4']['Utils\Rector\Tests\\'] = 'utils/rector/tests';
                $hasChanged = \true;
            }
            if ($hasChanged) {
                $this->symfonyStyle->writeln('We updated "composer.json" autoload-dev to load Rector rules.');
                $this->symfonyStyle->writeln('Now run "composer dump-autoload" to update paths');
                JsonFileSystem::writeFile($composerJsonFilePath, $composerJson);
            }
        }
        $this->symfonyStyle->newLine(1);
        // 3. update phpunit.xml(.dist) to include rector test suite
        $this->symfonyStyle->writeln('<fg=green>Run Rector tests via PHPUnit:</>');
        $this->symfonyStyle->newLine(1);
        $this->symfonyStyle->writeln('  vendor/bin/phpunit utils/rector/tests');
        $this->symfonyStyle->newLine(1);
        return Command::SUCCESS;
    }
    private function replaceNameVariable(string $rectorName, string $contents): string
    {
        return str_replace('__Name__', $rectorName, $contents);
    }
    private function isPHPUnitAttributeSupported(): bool
    {
        return $this->reflectionProvider->hasClass(ClassName::DATA_PROVIDER);
    }
}
