<?php

declare (strict_types=1);
namespace Rector\Console\Command;

use RectorPrefix202401\Nette\Utils\FileSystem;
use Rector\Exception\ShouldNotHappenException;
use Rector\FileSystem\JsonFileSystem;
use RectorPrefix202401\Symfony\Component\Console\Command\Command;
use RectorPrefix202401\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202401\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix202401\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix202401\Symfony\Component\Finder\Finder;
final class CustomRuleCommand extends Command
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setName('custom-rule');
        $this->setDescription('Create base of local custom rule with tests');
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        // ask for rule name
        $rectorName = $this->symfonyStyle->ask('What is the name of the rule class (e.g. "LegacyCallToDbalMethodCall")?', null, static function (string $answer) : string {
            if ($answer === '') {
                throw new ShouldNotHappenException('Rector name cannot be empty');
            }
            return $answer;
        });
        // suffix with Rector by convention
        if (\substr_compare((string) $rectorName, 'Rector', -\strlen('Rector')) !== 0) {
            $rectorName .= 'Rector';
        }
        $rectorName = \ucfirst((string) $rectorName);
        // find all files in templates directory
        $fileInfos = Finder::create()->files()->in(__DIR__ . '/../../../templates/custom-rule')->getIterator();
        $generatedFilePaths = [];
        foreach ($fileInfos as $fileInfo) {
            // replace __Name__ with $rectorName
            $newContent = \str_replace('__Name__', $rectorName, $fileInfo->getContents());
            $newFilePath = \str_replace('__Name__', $rectorName, $fileInfo->getRelativePathname());
            FileSystem::write(\getcwd() . '/' . $newFilePath, $newContent);
            $generatedFilePaths[] = $newFilePath;
        }
        $this->symfonyStyle->title('Generated files');
        $this->symfonyStyle->listing($generatedFilePaths);
        $this->symfonyStyle->success(\sprintf('Base for the "%s" rule was created. Now you can fill the missing parts', $rectorName));
        // 2. update autoload-dev in composer.json
        $composerJsonFilePath = \getcwd() . '/composer.json';
        if (\file_exists($composerJsonFilePath)) {
            $hasChanged = \false;
            $composerJson = JsonFileSystem::readFilePath($composerJsonFilePath);
            if (!isset($composerJson['autoload-dev']['psr-4']['Utils\\Rector\\'])) {
                $composerJson['autoload-dev']['psr-4']['Utils\\Rector\\'] = 'utils/rector/src';
                $composerJson['autoload-dev']['psr-4']['Utils\\Rector\\Tests\\'] = 'utils/rector/tests';
                $hasChanged = \true;
            }
            if ($hasChanged) {
                $this->symfonyStyle->success('We also update composer.json autoload-dev, to load Rector rules. Now run "composer dump-autoload" to update paths');
                JsonFileSystem::writeFile($composerJsonFilePath, $composerJson);
            }
        }
        return Command::SUCCESS;
    }
}
