# Master Fluent API Builder

You can [add own Rector](/docs/HowToCreateOwnRector.md) that extends `Rector\Rector\AbstractRector`. But it takes lot of code and knowledge to do so.
 
How about nice fluent API like this?

```php
$rector = $this->builderRectorFactory->create()
    ->matchMethodCallByType('Nette\Application\UI\Control')
    ->matchMethodName('invalidateControl')
    ->changeMethodNameTo('redrawControl');
```

That can perform followin change: 

```diff
-$control->invalidateControl();
+$control->redrawControl();
```

Nice and clear change in 4 lines, with autocomplete and typehinting of PHP.


## 3 Steps to Build Specific Rector 

### 1. Implement `Rector\Contract\Rector\RectorInterface`

```php
namespace App\Rector;

use Rector\Contract\Rector\RectorInterface;

final class NetteRectorProvider implements RectorInterface
{
    /**
     * @return RectorInterface[]
     */
    public function provide(): array
    {
        return [];
    }
}
```

### 2. Builder the Rector 
 
```diff
 namespace App\Rector;
 
 use Rector\Contract\Rector\RectorInterface;
 
 final class NetteRectorProvider implements RectorInterface
 {
     /**
      * @return RectorInterface[]
      */
     public function provide(): array
     {
+         $redrawControlRector = $this->builderRectorFactory->create()
+            ->matchMethodCallByType('Nette\Application\UI\Control')
+            ->matchMethodName('invalidateControl')
+            ->changeMethodNameTo('redrawControl');
-        return [];
+        return [$redrawControlRector];

     }
 }
```

### 3. Register as Service to `rector.yml`
 
```yml
services:
    App\Rector\NetteRectorProvider: ~    
```

That's it!

Now you can load the config and process your code with it:

```php
vendor/bin/rector process src --config rector.yml
```

#### Autowiring Ready!

Do you need autowiring? You can, it's all Symfony config:

```diff
 services:
+    _defaults:
+        autowire: true

     App\Rector\NetteRectorProvider: ~    
```
