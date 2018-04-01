<?php declare(strict_types=1);

namespace Rector\PharBuilder\Command;

use Rector\PharBuilder\Compiler\Compiler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class CompileCommand extends Command
{
    /**
     * @var Compiler
     */
    private $compiler;

    public function __construct(Compiler $compiler)
    {
        $this->compiler = $compiler;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->compiler->compile();
    }
}
