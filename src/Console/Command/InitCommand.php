<?php

declare (strict_types=1);
namespace Rector\Core\Console\Command;

use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Template\TemplateResolverInterface;
use Rector\Core\Template\TemplateTypeNotFound;
use RectorPrefix20210514\Symfony\Component\Console\Command\Command;
use RectorPrefix20210514\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210514\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20210514\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20210514\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20210514\Symplify\PackageBuilder\Console\ShellCode;
use RectorPrefix20210514\Symplify\SmartFileSystem\FileSystemGuard;
use RectorPrefix20210514\Symplify\SmartFileSystem\SmartFileSystem;
final class InitCommand extends \RectorPrefix20210514\Symfony\Component\Console\Command\Command
{
    /**
     * @var \Symplify\SmartFileSystem\FileSystemGuard
     */
    private $fileSystemGuard;
    /**
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @var mixed[]
     */
    private $templateResolvers;
    /**
     * @param TemplateResolverInterface[] $templateResolvers
     */
    public function __construct(\RectorPrefix20210514\Symplify\SmartFileSystem\FileSystemGuard $fileSystemGuard, \RectorPrefix20210514\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \RectorPrefix20210514\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle, array $templateResolvers)
    {
        $this->fileSystemGuard = $fileSystemGuard;
        $this->smartFileSystem = $smartFileSystem;
        $this->symfonyStyle = $symfonyStyle;
        $this->templateResolvers = $templateResolvers;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setDescription('Generate rector.php configuration file');
        $this->addOption(\Rector\Core\Configuration\Option::TEMPLATE_TYPE, null, \RectorPrefix20210514\Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'A template type like default, nette, doctrine etc.', 'default');
    }
    protected function execute(\RectorPrefix20210514\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210514\Symfony\Component\Console\Output\OutputInterface $output) : int
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
            $this->symfonyStyle->success('"rector.php" config file was added');
        }
        return \RectorPrefix20210514\Symplify\PackageBuilder\Console\ShellCode::SUCCESS;
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
            $availableTemplateTypes = \implode(', ', $this->templateResolvers);
            throw \Rector\Core\Template\TemplateTypeNotFound::typeNotFound($templateType, $availableTemplateTypes);
        }
        return $rectorTemplateFilePath;
    }
}
