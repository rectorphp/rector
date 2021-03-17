<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use Symplify\PhpConfigPrinter\HttpKernel\PhpConfigPrinterKernel;
use Symplify\PhpConfigPrinter\Printer\SmartPhpConfigPrinter;

final class SkipContainerGet
{
    public function create(): SmartPhpConfigPrinter
    {
        $phpConfigPrinterKernel = new PhpConfigPrinterKernel('prod', true);
        $phpConfigPrinterKernel->setConfigs([__DIR__ . '/config/php-config-printer-config.php']);
        $phpConfigPrinterKernel->boot();
        $container = $phpConfigPrinterKernel->getContainer();

        return $container->get(SmartPhpConfigPrinter::class);
    }
}
