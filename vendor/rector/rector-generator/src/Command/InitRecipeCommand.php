<?php

declare (strict_types=1);
namespace Rector\RectorGenerator\Command;

use Rector\RectorGenerator\TemplateInitializer;
use RectorPrefix20220531\Symfony\Component\Console\Command\Command;
use RectorPrefix20220531\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20220531\Symfony\Component\Console\Output\OutputInterface;
final class InitRecipeCommand extends \RectorPrefix20220531\Symfony\Component\Console\Command\Command
{
    /**
     * @readonly
     * @var \Rector\RectorGenerator\TemplateInitializer
     */
    private $templateInitializer;
    public function __construct(\Rector\RectorGenerator\TemplateInitializer $templateInitializer)
    {
        $this->templateInitializer = $templateInitializer;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setName('init-recipe');
        $this->setDescription('[DEV] Initialize "rector-recipe.php" config');
    }
    protected function execute(\RectorPrefix20220531\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20220531\Symfony\Component\Console\Output\OutputInterface $output) : int
    {
        $this->templateInitializer->initialize(__DIR__ . '/../../templates/rector-recipe.php', 'rector-recipe.php');
        return self::SUCCESS;
    }
}
