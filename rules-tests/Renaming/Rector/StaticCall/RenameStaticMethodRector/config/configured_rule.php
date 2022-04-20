<?php

declare(strict_types=1);

use Nette\Utils\Html;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Rector\Tests\Renaming\Rector\StaticCall\RenameStaticMethodRector\Source\FormMacros;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(RenameStaticMethodRector::class, [

            new RenameStaticMethod(Html::class, 'add', Html::class, 'addHtml'),
            new RenameStaticMethod(
                FormMacros::class,
                'renderFormBegin',
                'Nette\Bridges\FormsLatte\Runtime',
                'renderFormBegin'
            ),

        ]);
};
