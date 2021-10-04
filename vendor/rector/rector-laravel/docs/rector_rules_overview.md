# 19 Rules Overview

## AddArgumentDefaultValueRector

Adds default value for arguments in defined methods.

:wrench: **configure it!**

- class: [`Rector\Laravel\Rector\ClassMethod\AddArgumentDefaultValueRector`](../src/Rector/ClassMethod/AddArgumentDefaultValueRector.php)

```php
use Rector\Laravel\Rector\ClassMethod\AddArgumentDefaultValueRector;
use Rector\Laravel\ValueObject\AddArgumentDefaultValue;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddArgumentDefaultValueRector::class)
        ->call('configure', [[
            AddArgumentDefaultValueRector::ADDED_ARGUMENTS => ValueObjectInliner::inline([
                new AddArgumentDefaultValue('SomeClass', 'someMethod', 0, false),
            ]),
        ]]);
};
```

↓

```diff
 class SomeClass
 {
-    public function someMethod($value)
+    public function someMethod($value = false)
     {
     }
 }
```

<br>

## AddGuardToLoginEventRector

Add new `$guard` argument to Illuminate\Auth\Events\Login

- class: [`Rector\Laravel\Rector\New_\AddGuardToLoginEventRector`](../src/Rector/New_/AddGuardToLoginEventRector.php)

```diff
 use Illuminate\Auth\Events\Login;

 final class SomeClass
 {
     public function run(): void
     {
-        $loginEvent = new Login('user', false);
+        $guard = config('auth.defaults.guard');
+        $loginEvent = new Login($guard, 'user', false);
     }
 }
```

<br>

## AddMockConsoleOutputFalseToConsoleTestsRector

Add "$this->mockConsoleOutput = false"; to console tests that work with output content

- class: [`Rector\Laravel\Rector\Class_\AddMockConsoleOutputFalseToConsoleTestsRector`](../src/Rector/Class_/AddMockConsoleOutputFalseToConsoleTestsRector.php)

```diff
 use Illuminate\Support\Facades\Artisan;
 use Illuminate\Foundation\Testing\TestCase;

 final class SomeTest extends TestCase
 {
+    public function setUp(): void
+    {
+        parent::setUp();
+
+        $this->mockConsoleOutput = false;
+    }
+
     public function test(): void
     {
         $this->assertEquals('content', \trim((new Artisan())::output()));
     }
 }
```

<br>

## AddParentBootToModelClassMethodRector

Add `parent::boot();` call to `boot()` class method in child of `Illuminate\Database\Eloquent\Model`

- class: [`Rector\Laravel\Rector\ClassMethod\AddParentBootToModelClassMethodRector`](../src/Rector/ClassMethod/AddParentBootToModelClassMethodRector.php)

```diff
 use Illuminate\Database\Eloquent\Model;

 class Product extends Model
 {
     public function boot()
     {
+        parent::boot();
     }
 }
```

<br>

## AddParentRegisterToEventServiceProviderRector

Add `parent::register();` call to `register()` class method in child of `Illuminate\Foundation\Support\Providers\EventServiceProvider`

- class: [`Rector\Laravel\Rector\ClassMethod\AddParentRegisterToEventServiceProviderRector`](../src/Rector/ClassMethod/AddParentRegisterToEventServiceProviderRector.php)

```diff
 use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

 class EventServiceProvider extends ServiceProvider
 {
     public function register()
     {
+        parent::register();
     }
 }
```

<br>

## AnonymousMigrationsRector

Convert migrations to anonymous classes.

- class: [`Rector\Laravel\Rector\Class_\AnonymousMigrationsRector`](../src/Rector/Class_/AnonymousMigrationsRector.php)

```diff
 use Illuminate\Database\Migrations\Migration;

-class CreateUsersTable extends Migration
+return new class extends Migration
 {
     // ...
-}
+};
```

<br>

## CallOnAppArrayAccessToStandaloneAssignRector

Replace magical call on `$this->app["something"]` to standalone type assign variable

- class: [`Rector\Laravel\Rector\Assign\CallOnAppArrayAccessToStandaloneAssignRector`](../src/Rector/Assign/CallOnAppArrayAccessToStandaloneAssignRector.php)

```diff
 class SomeClass
 {
     /**
      * @var \Illuminate\Contracts\Foundation\Application
      */
     private $app;

     public function run()
     {
-        $validator = $this->app['validator']->make('...');
+        /** @var \Illuminate\Validation\Factory $validationFactory */
+        $validationFactory = $this->app['validator'];
+        $validator = $validationFactory->make('...');
     }
 }
```

<br>

## ChangeQueryWhereDateValueWithCarbonRector

Add `parent::boot();` call to `boot()` class method in child of `Illuminate\Database\Eloquent\Model`

- class: [`Rector\Laravel\Rector\MethodCall\ChangeQueryWhereDateValueWithCarbonRector`](../src/Rector/MethodCall/ChangeQueryWhereDateValueWithCarbonRector.php)

```diff
 use Illuminate\Database\Query\Builder;

 final class SomeClass
 {
     public function run(Builder $query)
     {
-        $query->whereDate('created_at', '<', Carbon::now());
+        $dateTime = Carbon::now();
+        $query->whereDate('created_at', '<=', $dateTime);
+        $query->whereTime('created_at', '<=', $dateTime);
     }
 }
```

<br>

## FactoryApplyingStatesRector

Call the state methods directly instead of specify the name of state.

- class: [`Rector\Laravel\Rector\MethodCall\FactoryApplyingStatesRector`](../src/Rector/MethodCall/FactoryApplyingStatesRector.php)

```diff
-$factory->state('delinquent');
-$factory->states('premium', 'delinquent');
+$factory->delinquent();
+$factory->premium()->delinquent();
```

<br>

## FactoryDefinitionRector

Upgrade legacy factories to support classes.

- class: [`Rector\Laravel\Rector\Namespace_\FactoryDefinitionRector`](../src/Rector/Namespace_/FactoryDefinitionRector.php)

```diff
 use Faker\Generator as Faker;

-$factory->define(App\User::class, function (Faker $faker) {
-    return [
-        'name' => $faker->name,
-        'email' => $faker->unique()->safeEmail,
-    ];
-});
+class UserFactory extends \Illuminate\Database\Eloquent\Factories\Factory
+{
+    protected $model = App\User::class;
+    public function definition()
+    {
+        return [
+            'name' => $this->faker->name,
+            'email' => $this->faker->unique()->safeEmail,
+        ];
+    }
+}
```

<br>

## FactoryFuncCallToStaticCallRector

Use the static factory method instead of global factory function.

- class: [`Rector\Laravel\Rector\FuncCall\FactoryFuncCallToStaticCallRector`](../src/Rector/FuncCall/FactoryFuncCallToStaticCallRector.php)

```diff
-factory(User::class);
+User::factory();
```

<br>

## HelperFuncCallToFacadeClassRector

Change `app()` func calls to facade calls

- class: [`Rector\Laravel\Rector\FuncCall\HelperFuncCallToFacadeClassRector`](../src/Rector/FuncCall/HelperFuncCallToFacadeClassRector.php)

```diff
 class SomeClass
 {
     public function run()
     {
-        return app('translator')->trans('value');
+        return \Illuminate\Support\Facades\App::get('translator')->trans('value');
     }
 }
```

<br>

## MinutesToSecondsInCacheRector

Change minutes argument to seconds in `Illuminate\Contracts\Cache\Store` and Illuminate\Support\Facades\Cache

- class: [`Rector\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector`](../src/Rector/StaticCall/MinutesToSecondsInCacheRector.php)

```diff
 class SomeClass
 {
     public function run()
     {
-        Illuminate\Support\Facades\Cache::put('key', 'value', 60);
+        Illuminate\Support\Facades\Cache::put('key', 'value', 60 * 60);
     }
 }
```

<br>

## PropertyDeferToDeferrableProviderToRector

Change deprecated `$defer` = true; to `Illuminate\Contracts\Support\DeferrableProvider` interface

- class: [`Rector\Laravel\Rector\Class_\PropertyDeferToDeferrableProviderToRector`](../src/Rector/Class_/PropertyDeferToDeferrableProviderToRector.php)

```diff
 use Illuminate\Support\ServiceProvider;
+use Illuminate\Contracts\Support\DeferrableProvider;

-final class SomeServiceProvider extends ServiceProvider
+final class SomeServiceProvider extends ServiceProvider implements DeferrableProvider
 {
-    /**
-     * @var bool
-     */
-    protected $defer = true;
 }
```

<br>

## Redirect301ToPermanentRedirectRector

Change "redirect" call with 301 to "permanentRedirect"

- class: [`Rector\Laravel\Rector\StaticCall\Redirect301ToPermanentRedirectRector`](../src/Rector/StaticCall/Redirect301ToPermanentRedirectRector.php)

```diff
 class SomeClass
 {
     public function run()
     {
-        Illuminate\Routing\Route::redirect('/foo', '/bar', 301);
+        Illuminate\Routing\Route::permanentRedirect('/foo', '/bar');
     }
 }
```

<br>

## RemoveAllOnDispatchingMethodsWithJobChainingRector

Remove `allOnQueue()` and `allOnConnection()` methods used with job chaining, use the `onQueue()` and `onConnection()` methods instead.

- class: [`Rector\Laravel\Rector\MethodCall\RemoveAllOnDispatchingMethodsWithJobChainingRector`](../src/Rector/MethodCall/RemoveAllOnDispatchingMethodsWithJobChainingRector.php)

```diff
 Job::withChain([
     new ChainJob(),
 ])
-    ->dispatch()
-    ->allOnConnection('redis')
-    ->allOnQueue('podcasts');
+    ->onQueue('podcasts')
+    ->onConnection('redis')
+    ->dispatch();
```

<br>

## RequestStaticValidateToInjectRector

Change static `validate()` method to `$request->validate()`

- class: [`Rector\Laravel\Rector\StaticCall\RequestStaticValidateToInjectRector`](../src/Rector/StaticCall/RequestStaticValidateToInjectRector.php)

```diff
 use Illuminate\Http\Request;

 class SomeClass
 {
-    public function store()
+    public function store(\Illuminate\Http\Request $request)
     {
-        $validatedData = Request::validate(['some_attribute' => 'required']);
+        $validatedData = $request->validate(['some_attribute' => 'required']);
     }
 }
```

<br>

## RouteActionCallableRector

Use PHP callable syntax instead of string syntax for controller route declarations.

:wrench: **configure it!**

- class: [`Rector\Laravel\Rector\StaticCall\RouteActionCallableRector`](../src/Rector/StaticCall/RouteActionCallableRector.php)

```php
use Rector\Laravel\Rector\StaticCall\RouteActionCallableRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RouteActionCallableRector::class)
        ->call('configure', [[
            RouteActionCallableRector::NAMESPACE => 'App\Http\Controllers',
        ]]);
};
```

↓

```diff
-Route::get('/users', 'UserController@index');
+Route::get('/users', [\App\Http\Controllers\UserController::class, 'index']);
```

<br>

## UnifyModelDatesWithCastsRector

Unify Model `$dates` property with `$casts`

- class: [`Rector\Laravel\Rector\Class_\UnifyModelDatesWithCastsRector`](../src/Rector/Class_/UnifyModelDatesWithCastsRector.php)

```diff
 use Illuminate\Database\Eloquent\Model;

 class Person extends Model
 {
     protected $casts = [
-        'age' => 'integer',
+        'age' => 'integer', 'birthday' => 'datetime',
     ];
-
-    protected $dates = ['birthday'];
 }
```

<br>
