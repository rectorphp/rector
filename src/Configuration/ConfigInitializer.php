<?php

declare (strict_types=1);
namespace Rector\Configuration;

use RectorPrefix202407\Nette\Utils\FileSystem;
use Rector\Contract\Rector\RectorInterface;
use Rector\FileSystem\InitFilePathsResolver;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use RectorPrefix202407\Symfony\Component\Console\Style\SymfonyStyle;
final class ConfigInitializer
{
    /**
     * @var RectorInterface[]
     * @readonly
     */
    private $rectors;
    /**
     * @readonly
     * @var \Rector\FileSystem\InitFilePathsResolver
     */
    private $initFilePathsResolver;
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(array $rectors, InitFilePathsResolver $initFilePathsResolver, SymfonyStyle $symfonyStyle)
    {
        $this->rectors = $rectors;
        $this->initFilePathsResolver = $initFilePathsResolver;
        $this->symfonyStyle = $symfonyStyle;
    }
    public function createConfig(string $projectDirectory) : void
    {
        $commonRectorConfigPath = $projectDirectory . '/rector.php';
        if (\file_exists($commonRectorConfigPath)) {
            $this->symfonyStyle->warning('Register rules or sets in your "rector.php" config');
            return;
        }
        $response = $this->symfonyStyle->ask('No "rector.php" config found. Should we generate it for you?', 'yes');
        // be tolerant about input
        if (!\in_array($response, ['yes', 'YES', 'y', 'Y'], \true)) {
            // okay, nothing we can do
            return;
        }
        $configContents = FileSystem::read(__DIR__ . '/../../templates/rector.php.dist');
        $configContents = $this->replacePathsContents($configContents, $projectDirectory);
        FileSystem::write($commonRectorConfigPath, $configContents, null);
        $this->symfonyStyle->success('The config is added now. Re-run command to make Rector do the work!');
    }
    public function areSomeRectorsLoaded() : bool
    {
        $activeRectors = $this->filterActiveRectors($this->rectors);
        return $activeRectors !== [];
    }
    /**
     * @param RectorInterface[] $rectors
     * @return RectorInterface[]
     */
    private function filterActiveRectors(array $rectors) : array
    {
        return \array_filter($rectors, static function (RectorInterface $rector) : bool {
            return !$rector instanceof PostRectorInterface;
        });
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
