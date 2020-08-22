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
    private $rectorSetProvider;

    public function __construct()
    {
        $this->rectorSetProvider = new RectorSetProvider();
    }

    public function resolveSetFromInput(InputInterface $input): ?Set
    {
        $setOption = $input->getParameterOption(['-s', '--set']);
        if ($setOption === false) {
            return null;
        }

        return $this->rectorSetProvider->provideByName($setOption);
    }
}
