<?php declare(strict_types=1);

namespace Rector\Autoloading;

use Rector\Console\Command\ProcessCommand;
use Symfony\Component\Console\Input\InputInterface;

final class AdditionalAutoloader
{
    public function autoloadWithInput(InputInterface $input): void
    {
        $this->autoloadFile($input);
    }

    private function autoloadFile(InputInterface $input): void
    {
        /** @var string|null $autoloadFile */
        $autoloadFile = $input->getOption(ProcessCommand::OPTION_AUTOLOAD_FILE);
        if ($autoloadFile === null) {
            return;
        }

        if (! is_file($autoloadFile) || ! file_exists($autoloadFile)) {
            return;
        }

        require_once $autoloadFile;
    }
}
