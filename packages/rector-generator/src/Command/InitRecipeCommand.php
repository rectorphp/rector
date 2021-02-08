<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Command;

use Rector\RectorGenerator\TemplateInitializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\ShellCode;

final class InitRecipeCommand extends Command
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
        $this->setDescription('[DEV] Initialize "rector-recipe.php" config');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->templateInitializer->initialize(
            __DIR__ . '/../../../../templates/rector-recipe.php.dist',
            'rector-recipe.php'
        );

        return ShellCode::SUCCESS;
    }
}
