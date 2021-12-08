composer/pcre
=============

PCRE wrapping library that offers type-safe `preg_*` replacements.

This library gives you a way to ensure `preg_*` functions do not fail silently, returning
unexpected `null`s that may not be handled.

It also makes it easier ot work with static analysis tools like PHPStan or Psalm as it
simplifies and reduces the possible return values from all the `preg_*` functions which
are quite packed with edge cases.

This library is a thin wrapper around `preg_*` functions with [some limitations](#restrictions--limitations).
If you are looking for a richer API to handle regular expressions have a look at
[rawr/t-regx](https://packagist.org/packages/rawr/t-regx) instead.

[![Continuous Integration](https://github.com/composer/pcre/workflows/Continuous%20Integration/badge.svg?branch=main)](https://github.com/composer/pcre/actions)


Installation
------------

Install the latest version with:

```bash
$ composer require composer/pcre
```


Requirements
------------

* PHP 5.3.2 is required but using the latest version of PHP is highly recommended.


Basic usage
-----------

Instead of:

```php
if (preg_match('{fo+}', $string, $matches)) { ... }
if (preg_match('{fo+}', $string, $matches, PREG_OFFSET_CAPTURE)) { ... }
if (preg_match_all('{fo+}', $string, $matches)) { ... }
$newString = preg_replace('{fo+}', 'bar', $string);
$newString = preg_replace_callback('{fo+}', function ($match) { return strtoupper($match[0]); }, $string);
$newString = preg_replace_callback_array(['{fo+}' => fn ($match) => strtoupper($match[0])], $string);
$filtered = preg_grep('{[a-z]}', $elements);
$array = preg_split('{[a-z]+}', $string);
```

You can now call these on the `Preg` class:

```php
use Composer\Pcre\Preg;

if (Preg::match('{fo+}', $string, $matches)) { ... }
if (Preg::matchWithOffsets('{fo+}', $string, $matches)) { ... }
if (Preg::matchAll('{fo+}', $string, $matches)) { ... }
$newString = Preg::replace('{fo+}', 'bar', $string);
$newString = Preg::replaceCallback('{fo+}', function ($match) { return strtoupper($match[0]); }, $string);
$newString = Preg::replaceCallbackArray(['{fo+}' => fn ($match) => strtoupper($match[0])], $string);
$filtered = Preg::grep('{[a-z]}', $elements);
$array = Preg::split('{[a-z]+}', $string);
```

The main difference is if anything fails to match/replace/.., it will throw a `Composer\Pcre\PcreException`
instead of returning `null` (or false in some cases), so you can now use the return values safely relying on
the fact that they can only be strings (for replace), ints (for match) or arrays (for grep/split).

Additionally the `Preg` class provides match methods that return `bool` rather than `int`, for stricter type safety
when the number of pattern matches is not useful:

```php
use Composer\Pcre\Preg;

if (Preg::isMatch('{fo+}', $string, $matches)) // bool
if (Preg::isMatchAll('{fo+}', $string, $matches)) // bool
```

If you would prefer a slightly more verbose usage, replacing by-ref arguments by result objects, you can use the `Regex` class:

```php
use Composer\Pcre\Regex;

// this is useful when you are just interested in knowing if something matched
// as it returns a bool instead of int(1/0) for match
$bool = Regex::isMatch('{fo+}', $string);

$result = Regex::match('{fo+}', $string);
if ($result->matched) { something($result->matches); }

$result = Regex::matchWithOffsets('{fo+}', $string);
if ($result->matched) { something($result->matches); }

$result = Regex::matchAll('{fo+}', $string);
if ($result->matched && $result->count > 3) { something($result->matches); }

$newString = Regex::replace('{fo+}', 'bar', $string)->result;
$newString = Regex::replaceCallback('{fo+}', function ($match) { return strtoupper($match[0]); }, $string)->result;
$newString = Regex::replaceCallbackArray(['{fo+}' => fn ($match) => strtoupper($match[0])], $string)->result;
```

Note that `preg_grep` and `preg_split` are only callable via the `Preg` class as they do not have
complex return types warranting a specific result object.

See the [MatchResult](src/MatchResult.php), [MatchWithOffsetsResult](src/MatchWithOffsetsResult.php), [MatchAllResult](src/MatchAllResult.php),
[MatchAllWithOffsetsResult](src/MatchAllWithOffsetsResult.php), and [ReplaceResult](src/ReplaceResult.php) class sources for more details.

Restrictions / Limitations
--------------------------

Due to type safety requirements a few restrictions are in place.matchWithOffsets

- matching using `PREG_OFFSET_CAPTURE` is made available via `matchWithOffsets` and `matchAllWithOffsets`.
  You cannot pass the flag to `match`/`matchAll`.
- `Preg::split` will also reject `PREG_SPLIT_OFFSET_CAPTURE` and you should use `splitWithOffsets`
  instead.
- `matchAll` rejects `PREG_SET_ORDER` as it also changes the shape of the returned matches. There
  is no alternative provided as you can fairly easily code around it.
- `preg_filter` is not supported as it has a rather crazy API, most likely you should rather
  use `Preg::grep` in combination with some loop and `Preg::replace`.
- `replace`, `replaceCallback` and `replaceCallbackArray` do not support an array `$subject`,
  only simple strings.
- in 2.x, we plan to always implicitly use `PREG_UNMATCHED_AS_NULL` for matching, which offers much
  saner/predictable results. This is currently not doable due to the PHP version requirement and to
  keep things working the same across all PHP versions. If you use the library on a PHP 7.2+ project
  however we highly recommend using `PREG_UNMATCHED_AS_NULL` with all `match*` and `replaceCallback*`
  methods.

License
-------

composer/pcre is licensed under the MIT License, see the LICENSE file for details.
