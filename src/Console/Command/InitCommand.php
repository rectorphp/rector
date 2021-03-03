<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Rector\RectorGenerator\TemplateInitializer;
use Rector\TypeDeclaration\Tests\Rector\Property\CompleteVarDocTypePropertyRector\Fixture\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\ShellCode;

final class InitCommand extends Command
{
    /**
     * @var TemplateInitializer
     */
    private $templateInitializer;

    public function __construct(TemplateInitializer $templateInitializer)
    {
        parent::__construct();

        $this->templateInitializer = $templateInitializer;
    }

    protected function configure(): void
    {
        $this->setDescription('Generate rector.php configuration file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->templateInitializer->initialize(__DIR__ . '/../../../templates/rector.php.dist', 'rector.php');

        return ShellCode::SUCCESS;
    }
}
