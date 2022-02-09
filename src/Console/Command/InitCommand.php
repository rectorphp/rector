<?php

declare (strict_types=1);
namespace Rector\Core\Console\Command;

use RectorPrefix20220209\Nette\Utils\Strings;
use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Template\TemplateResolverInterface;
use Rector\Core\Exception\Template\TemplateTypeNotFoundException;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Template\DefaultResolver;
use Stringable;
use RectorPrefix20220209\Symfony\Component\Console\Command\Command;
use RectorPrefix20220209\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20220209\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20220209\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20220209\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20220209\Symplify\SmartFileSystem\FileSystemGuard;
use RectorPrefix20220209\Symplify\SmartFileSystem\SmartFileSystem;
final class InitCommand extends \RectorPrefix20220209\Symfony\Component\Console\Command\Command
{
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\FileSystemGuard
     */
    private $fileSystemGuard;
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @var TemplateResolverInterface[]
     * @readonly
     */
    private $templateResolvers;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @param TemplateResolverInterface[] $templateResolvers
     */
    public function __construct(\RectorPrefix20220209\Symplify\SmartFileSystem\FileSystemGuard $fileSystemGuard, \RectorPrefix20220209\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \RectorPrefix20220209\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle, array $templateResolvers, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->fileSystemGuard = $fileSystemGuard;
        $this->smartFileSystem = $smartFileSystem;
        $this->symfonyStyle = $symfonyStyle;
        $this->templateResolvers = $templateResolvers;
        $this->phpVersionProvider = $phpVersionProvider;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setDescription('Generate rector.php configuration file');
        $this->addOption(\Rector\Core\Configuration\Option::TEMPLATE_TYPE, null, \RectorPrefix20220209\Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'A template type like default, nette, doctrine etc.', \Rector\Core\Template\DefaultResolver::TYPE);
    }
    protected function execute(\RectorPrefix20220209\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20220209\Symfony\Component\Console\Output\OutputInterface $output) : int
    {
        $templateType = (string) $input->getOption(\Rector\Core\Configuration\Option::TEMPLATE_TYPE);
        $rectorTemplateFilePath = $this->resolveTemplateFilePathByType($templateType);
        $this->fileSystemGuard->ensureFileExists($rectorTemplateFilePath, __METHOD__);
        $rectorRootFilePath = \getcwd() . '/rector.php';
        $doesFileExist = $this->smartFileSystem->exists($rectorRootFilePath);
        if ($doesFileExist) {
            $this->symfonyStyle->warning('Config file "rector.php" already exists');
        } else {
            $this->smartFileSystem->copy($rectorTemplateFilePath, $rectorRootFilePath);
            $fullPHPVersion = (string) $this->phpVersionProvider->provide();
            $phpVersion = \RectorPrefix20220209\Nette\Utils\Strings::substring($fullPHPVersion, 0, 1) . \RectorPrefix20220209\Nette\Utils\Strings::substring($fullPHPVersion, 2, 1);
            $fileContent = $this->smartFileSystem->readFile($rectorRootFilePath);
            $fileContent = \str_replace('LevelSetList::UP_TO_PHP_XY', \sprintf('LevelSetList::UP_TO_PHP_%d', $phpVersion), $fileContent);
            $this->smartFileSystem->dumpFile($rectorRootFilePath, $fileContent);
            $this->symfonyStyle->success('"rector.php" config file was added');
        }
        return \RectorPrefix20220209\Symfony\Component\Console\Command\Command::SUCCESS;
    }
    private function resolveTemplateFilePathByType(string $templateType) : string
    {
        $rectorTemplateFilePath = null;
        foreach ($this->templateResolvers as $templateResolver) {
            if ($templateResolver->supports($templateType)) {
                $rectorTemplateFilePath = $templateResolver->provide();
                break;
            }
        }
        if ($rectorTemplateFilePath === null) {
            $templateResolverTypes = [];
            foreach ($this->templateResolvers as $templateResolver) {
                if (\method_exists($templateResolver, 'getType')) {
                    $templateResolverTypes[] = $templateResolver->getType();
                } elseif ($templateResolver instanceof \Stringable) {
                    $templateResolverTypes[] = (string) $templateResolver;
                }
            }
            throw new \Rector\Core\Exception\Template\TemplateTypeNotFoundException($templateType, $templateResolverTypes);
        }
        return $rectorTemplateFilePath;
    }
}
