<?php declare(strict_types=1);

namespace Rector\DependencyInjection;

use Rector\Configuration\Normalizer\RectorClassNormalizer;
use Rector\Configuration\Validator\RectorClassValidator;
use Rector\DependencyInjection\Extension\RectorsExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class RectorBundle extends Bundle
{
    public function getContainerExtension(): RectorsExtension
    {
        return new RectorsExtension(
            new RectorClassValidator(),
            new RectorClassNormalizer()
        );
    }
}
