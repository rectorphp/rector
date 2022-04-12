# How To Configure Rules

Rector rules that implement `Rector\Core\Contract\Rector\ConfigurableRectorInterface` can be configured.

Typical example is `Rector\Renaming\Rector\Name\RenameClassRector`:

```php
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'App\SomeOldClass' => 'App\SomeNewClass',
    ]);
};
```
