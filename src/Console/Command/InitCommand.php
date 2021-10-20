<?php

declare (strict_types=1);
namespace Rector\Core\Console\Command;

use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Template\TemplateResolverInterface;
use Rector\Core\Exception\Template\TemplateTypeNotFoundException;
use Rector\Core\Template\DefaultResolver;
use Stringable;
use RectorPrefix20211020\Symfony\Component\Console\Command\Command;
use RectorPrefix20211020\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20211020\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20211020\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20211020\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20211020\Symplify\SmartFileSystem\FileSystemGuard;
use RectorPrefix20211020\Symplify\SmartFileSystem\SmartFileSystem;
final class InitCommand extends \RectorPrefix20211020\Symfony\Component\Console\Command\Command
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
     * @var \Rector\Core\Contract\Template\TemplateResolverInterface[]
     */
    private $templateResolvers;
    /**
     * @param TemplateResolverInterface[] $templateResolvers
     */
    public function __construct(\RectorPrefix20211020\Symplify\SmartFileSystem\FileSystemGuard $fileSystemGuard, \RectorPrefix20211020\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \RectorPrefix20211020\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle, array $templateResolvers)
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
        $this->addOption(\Rector\Core\Configuration\Option::TEMPLATE_TYPE, null, \RectorPrefix20211020\Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'A template type like default, nette, doctrine etc.', \Rector\Core\Template\DefaultResolver::TYPE);
    }
    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute($input, $output) : int
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
        return \RectorPrefix20211020\Symfony\Component\Console\Command\Command::SUCCESS;
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
