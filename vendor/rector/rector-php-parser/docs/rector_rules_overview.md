# 1 Rules Overview

## ParserFactoryRector

Upgrade ParserFactory `create()` method

- class: [`Rector\PhpParser\Rector\MethodCall\ParserFactoryRector`](../src/Rector/MethodCall/ParserFactoryRector.php)

```diff
 use PhpParser\ParserFactory;

 $factory = new ParserFactory;

-$parser = $factory->create(ParserFactory::PREFER_PHP7);
+$parser = $factory->createForNewestSupportedVersion();
```

<br>
