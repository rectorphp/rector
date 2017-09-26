<?php declare(strict_types=1);

namespace Rector\DependencyInjection;

use Rector\DependencyInjection\Extension\RectorsExtension;
use Rector\Validator\RectorClassValidator;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class RectorBundle extends Bundle
{
    public function getContainerExtension(): RectorsExtension
    {
        return new RectorsExtension(
            $this->createRectorClassValidator()
        );
    }

    private function createRectorClassValidator(): RectorClassValidator
    {
        return new RectorClassValidator;
    }
}
