# 11 Rules Overview

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

## MakeTaggedPassedToParameterIterableTypeRector

Change param type to iterable, if passed one

- class: [`Rector\Laravel\Rector\New_\MakeTaggedPassedToParameterIterableTypeRector`](../src/Rector/New_/MakeTaggedPassedToParameterIterableTypeRector.php)

```diff
 class AnotherClass
 {
     /**
      * @var \Illuminate\Contracts\Foundation\Application
      */
     private $app;

     public function create()
     {
         $tagged = $this->app->tagged('some_tagged');
         return new SomeClass($tagged);
     }
 }

 class SomeClass
 {
-    public function __construct(array $items)
+    public function __construct(iterable $items)
     {
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
