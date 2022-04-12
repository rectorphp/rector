<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\NonPhpFile\Rector\RenameClassNonPhpRector;
use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClass;
use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClass;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();

    $services->set(RenameClassNonPhpRector::class)
        ->configure([
            'Session' => 'Illuminate\Support\Facades\Session',
            OldClass::class => NewClass::class,
            // Laravel
            'Form' => 'Collective\Html\FormFacade',
            'Html' => 'Collective\Html\HtmlFacade',
        ]);
};
