<?php

declare(strict_types=1);

namespace Rector\Core\Set;

use Symfony\Component\Console\Input\ArgvInput;

final class SetResolver
{
    /**
     * @var SetProvider
     */
    private $setProvider;

    public function __construct()
    {
        $this->setProvider = new SetProvider();
    }

    public function resolveSetFromInput(ArgvInput $input)
    {
        $set = $input->getParameterOption(['-s', '--set']);
        if ($set === false) {
            return [];
        }

        $setFilePath = $this->setProvider->provideFilePathByName($set);
        if ($setFilePath === null) {
            return [];
        }

        dump($setFilePath);
        die;
    }
}
