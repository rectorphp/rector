<?php

declare(strict_types=1);

namespace Rector\Core\Set;

use Rector\Set\SetProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symplify\SetConfigResolver\ValueObject\Set;

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

    public function resolveSetFromInput(InputInterface $input): ?Set
    {
        $setOption = $input->getParameterOption(['-s', '--set']);
        if ($setOption === false) {
            return null;
        }

        return $this->setProvider->provideByName($setOption);
    }

    public function resolveSetByName(string $name): ?Set
    {
        return $this->setProvider->provideByName($name);
    }
}
