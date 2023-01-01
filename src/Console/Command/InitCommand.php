<?php

declare (strict_types=1);
namespace Rector\Core\Console\Command;

use RectorPrefix202301\Nette\Utils\FileSystem;
use RectorPrefix202301\Nette\Utils\Strings;
use Rector\Core\Contract\Console\OutputStyleInterface;
use Rector\Core\FileSystem\InitFilePathsResolver;
use Rector\Core\Php\PhpVersionProvider;
use RectorPrefix202301\Symfony\Component\Console\Command\Command;
use RectorPrefix202301\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202301\Symfony\Component\Console\Output\OutputInterface;
final class InitCommand extends Command
{
    /**
     * @var string
     */
    private const TEMPLATE_PATH = __DIR__ . '/../../../templates/rector.php.dist';
    /**
     * @readonly
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;
    /**
     * @readonly
     * @var \Rector\Core\Contract\Console\OutputStyleInterface
     */
    private $rectorOutputStyle;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @readonly
     * @var \Rector\Core\FileSystem\InitFilePathsResolver
     */
    private $initFilePathsResolver;
    public function __construct(\RectorPrefix202301\Symfony\Component\Filesystem\Filesystem $filesystem, OutputStyleInterface $rectorOutputStyle, PhpVersionProvider $phpVersionProvider, InitFilePathsResolver $initFilePathsResolver)
    {
        $this->filesystem = $filesystem;
        $this->rectorOutputStyle = $rectorOutputStyle;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->initFilePathsResolver = $initFilePathsResolver;
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $projectDirectory = \getcwd();
        $rectorRootFilePath = $projectDirectory . '/rector.php';
        $doesFileExist = $this->filesystem->exists($rectorRootFilePath);
        if ($doesFileExist) {
            $this->rectorOutputStyle->note('Config file "rector.php" already exists');
        } else {
            $rectorPhpTemplateContents = FileSystem::read(self::TEMPLATE_PATH);
            $rectorPhpTemplateContents = $this->replacePhpLevelContents($rectorPhpTemplateContents);
            $rectorPhpTemplateContents = $this->replacePathsContents($rectorPhpTemplateContents, $projectDirectory);
            $this->filesystem->dumpFile($rectorRootFilePath, $rectorPhpTemplateContents);
            $this->rectorOutputStyle->success('"rector.php" config file was added');
        }
        return Command::SUCCESS;
    }
    protected function configure() : void
    {
        $this->setName('init');
        $this->setDescription('Generate rector.php configuration file');
    }
    private function replacePhpLevelContents(string $rectorPhpTemplateContents) : string
    {
        $fullPHPVersion = (string) $this->phpVersionProvider->provide();
        $phpVersion = Strings::substring($fullPHPVersion, 0, 1) . Strings::substring($fullPHPVersion, 2, 1);
        return \str_replace('LevelSetList::UP_TO_PHP_XY', 'LevelSetList::UP_TO_PHP_' . $phpVersion, $rectorPhpTemplateContents);
    }
    private function replacePathsContents(string $rectorPhpTemplateContents, string $projectDirectory) : string
    {
        $projectPhpDirectories = $this->initFilePathsResolver->resolve($projectDirectory);
        // fallback to default 'src' in case of empty one
        if ($projectPhpDirectories === []) {
            $projectPhpDirectories[] = 'src';
        }
        $projectPhpDirectoriesContents = '';
        foreach ($projectPhpDirectories as $projectPhpDirectory) {
            $projectPhpDirectoriesContents .= "        __DIR__ . '/" . $projectPhpDirectory . "'," . \PHP_EOL;
        }
        $projectPhpDirectoriesContents = \rtrim($projectPhpDirectoriesContents);
        return \str_replace('__PATHS__', $projectPhpDirectoriesContents, $rectorPhpTemplateContents);
    }
}
