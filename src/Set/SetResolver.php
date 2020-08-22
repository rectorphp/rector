<?php

declare(strict_types=1);

namespace Rector\Core\Set;

use Rector\Set\RectorSetProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symplify\SetConfigResolver\ValueObject\Set;

final class SetResolver
{
    /**
     * @var RectorSetProvider
     */
    private $setProvider;

    public function __construct()
    {
        $this->setProvider = new RectorSetProvider();
    }

    public function resolveSetFromInput(InputInterface $input): ?Set
    {
        $setOption = $input->getParameterOption(['-s', '--set']);
        if ($setOption === false) {
            return null;
        }

        return $this->setProvider->provideByName($setOption);
    }
}
