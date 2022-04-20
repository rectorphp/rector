<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Namespace_\RenameNamespaceRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(RenameNamespaceRector::class, [
            'OldNamespace' => 'NewNamespace',
            'OldNamespaceWith\OldSplitNamespace' => 'NewNamespaceWith\NewSplitNamespace',
            'Old\Long\AnyNamespace' => 'Short\AnyNamespace',
            'PHPUnit_Framework_' => 'PHPUnit\Framework\\',
            'Foo\Bar' => 'Foo\Tmp',
            'App\Repositories' => 'App\Repositories\Example',
        ]);
};
