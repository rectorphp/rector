### 3.1.0 (2017-06-11)
* Add $language support to slugify
* Add bg specific transliteration
* ЬЪ/ьъ handling is now language-specific

### 3.0.1 (2017-04-12)
* Don't replace @ in toAscii
* Use normal replacement for @ in slugify, e.g. user@home => user-home

### 3.0.0 (2017-03-08)

* Breaking change: added $language parameter to toAscii, before
  $removeUnsupported
* Breaking change: dropped PHP 5.3 support
* Breaking change: any StaticStringy methods that previously returned instances
  of Stringy now return strings

### 2.4.0 (2017-03-02)

* Add startsWithAny
* Add endsWithAny
* Add stripWhitespace
* Fix error handling for unsupported encodings
* Change private methods to protected for extending class
* Fix safeTruncate for strings without spaces
* Additional char support in toAscii, e.g. full width chars and wide
  non-breaking space

### 2.3.2 (2016-05-02)

* Improve support without mbstring

### 2.3.1 (2016-03-21)

* Always use root namespace for mbstring functions

### 2.3.0 (2016-03-19)

* Add Persian characters in Stringy::charsArray()
* Use symfony/polyfill-mbstring to avoid dependency on ext-mbstring

### 2.2.0 (2015-12-20)

* isJSON now returns false for empty strings
* Update for German umlaut transformation
* Use reflection to generate method list for StaticStringy
* Added isBase64 method
* Improved toAscii char coverage

### 2.1.0 (2015-09-02)

* Added simplified StaticStringy class
* str in Stringy::create and constructor is now optional

### 2.0.0 (2015-07-29)

 * Removed StaticStringy class
 * Added append, prepend, toBoolean, repeat, between, slice, split, and lines
 * camelize/upperCamelize now strip leading dashes and underscores
 * titleize converts to lowercase, thus no longer preserving acronyms

### 1.10.0 (2015-07-22)

 * Added trimLeft, trimRight
 * Added support for unicode whitespace to trim
 * Added delimit
 * Added indexOf and indexOfLast
 * Added htmlEncode and htmlDecode
 * Added "Ç" in toAscii()

### 1.9.0 (2015-02-09)

 * Added hasUpperCase and hasLowerCase
 * Added $removeUnsupported parameter to toAscii()
 * Improved toAscii support with additional Unicode spaces, Vietnamese chars,
   and numerous other characters
 * Separated the charsArray from toAscii as a protected method that may be
   extended by inheriting classes
 * Chars array is cached for better performance

### 1.8.1 (2015-01-08)

 * Optimized chars()
 * Added "ä Ä Ö Ü"" in toAscii()
 * Added support for Unicode spaces in toAscii()
 * Replaced instances of self::create() with static::create()
 * Added missing test cases for safeTruncate() and longestCommonSuffix()
 * Updated Stringy\create() to avoid collision when it already exists

### 1.8.0 (2015-01-03)

 * Listed ext-mbstring in composer.json
 * Added Stringy\create function for PHP 5.6

### 1.7.0 (2014-10-14)

 * Added containsAll and containsAny
 * Light cleanup

### 1.6.0 (2014-09-14)

 * Added toTitleCase

### 1.5.2 (2014-07-09)

 * Announced support for HHVM

### 1.5.1 (2014-04-19)

  * Fixed toAscii() failing to remove remaining non-ascii characters
  * Updated slugify() to treat dash and underscore as delimiters by default
  * Updated slugify() to remove leading and trailing delimiter, if present

### 1.5.0 (2014-03-19)

  * Made both str and encoding protected, giving property access to subclasses
  * Added getEncoding()
  * Fixed isJSON() giving false negatives
  * Cleaned up and simplified: replace(), collapseWhitespace(), underscored(),
    dasherize(), pad(), padLeft(), padRight() and padBoth()
  * Fixed handling consecutive invalid chars in slugify()
  * Removed conflicting hard sign transliteration in toAscii()

### 1.4.0 (2014-02-12)

  * Implemented the IteratorAggregate interface, added chars()
  * Renamed count() to countSubstr()
  * Updated count() to implement Countable interface
  * Implemented the ArrayAccess interface with positive and negative indices
  * Switched from PSR-0 to PSR-4 autoloading

### 1.3.0 (2013-12-16)

  * Additional Bulgarian support for toAscii
  * str property made private
  * Constructor casts first argument to string
  * Constructor throws an InvalidArgumentException when given an array
  * Constructor throws an InvalidArgumentException when given an object without
    a __toString method

### 1.2.2 (2013-12-04)

  * Updated create function to use late static binding
  * Added optional $replacement param to slugify

### 1.2.1 (2013-10-11)

  * Cleaned up tests
  * Added homepage to composer.json

### 1.2.0 (2013-09-15)

  * Fixed pad's use of InvalidArgumentException
  * Fixed replace(). It now correctly treats regex special chars as normal chars
  * Added additional Cyrillic letters to toAscii
  * Added $caseSensitive to contains() and count()
  * Added toLowerCase()
  * Added toUpperCase()
  * Added regexReplace()

### 1.1.0 (2013-08-31)

  * Fix for collapseWhitespace()
  * Added isHexadecimal()
  * Added constructor to Stringy\Stringy
  * Added isSerialized()
  * Added isJson()

### 1.0.0 (2013-08-1)

  * 1.0.0 release
  * Added test coverage for Stringy::create and method chaining
  * Added tests for returned type
  * Fixed StaticStringy::replace(). It was returning a Stringy object instead of string
  * Renamed standardize() to the more appropriate toAscii()
  * Cleaned up comments and README

### 1.0.0-rc.1 (2013-07-28)

  * Release candidate
