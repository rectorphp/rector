Upgrading from phpstan/phpdoc-parser 1.x to 2.0
=================================

### PHP version requirements

phpstan/phpdoc-parser now requires PHP 7.4 or newer to run.

### Changed constructors of parser classes

Instead of different arrays and boolean values passed into class constructors during setup, parser classes now share a common ParserConfig object.

Before:

```php
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;

$usedAttributes = ['lines' => true, 'indexes' => true];

$lexer = new Lexer();
$constExprParser = new ConstExprParser(true, true, $usedAttributes);
$typeParser = new TypeParser($constExprParser, true, $usedAttributes);
$phpDocParser = new PhpDocParser($typeParser, $constExprParser, true, true, $usedAttributes);
```

After:

```php
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\ParserConfig;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;

$config = new ParserConfig(usedAttributes: ['lines' => true, 'indexes' => true]);
$lexer = new Lexer($config);
$constExprParser = new ConstExprParser($config);
$typeParser = new TypeParser($config, $constExprParser);
$phpDocParser = new PhpDocParser($config, $typeParser, $constExprParser);
```

The point of ParserConfig is that over the course of phpstan/phpdoc-parser 2.x development series it's most likely going to gain new optional parameters akin to PHPStan's [bleeding edge](https://phpstan.org/blog/what-is-bleeding-edge). These parameters will allow opting in to new behaviour which will become the default in 3.0.

With ParserConfig object, it's now going to be impossible to configure parser classes inconsistently. Which [happened to users](https://github.com/phpstan/phpdoc-parser/issues/251#issuecomment-2333927959) when they were separate boolean values.

### Support for parsing Doctrine annotations

This parser now supports parsing [Doctrine Annotations](https://github.com/doctrine/annotations). The AST nodes representing Doctrine Annotations live in the [PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine namespace](https://phpstan.github.io/phpdoc-parser/2.0.x/namespace-PHPStan.PhpDocParser.Ast.PhpDoc.Doctrine.html).

### Whitespace before description is required

phpdoc-parser 1.x sometimes silently consumed invalid part of a PHPDoc type as description:

```php
/** @return \Closure(...int, string): string */
```

This became `IdentifierTypeNode` of `\Closure` and with `(...int, string): string` as description. (Valid callable syntax is: `\Closure(int ...$u, string): string`.)

Another example:

```php
/** @return array{foo: int}} */
```

The extra `}` also became description.

Both of these examples are now InvalidTagValueNode.

If these parts are supposed to be PHPDoc descriptions, you need to put whitespace between the type and the description text:

```php
/** @return \Closure (...int, string): string */
/** @return array{foo: int} } */
```

### Type aliases with invalid types are preserved

In phpdoc-parser 1.x, invalid type alias syntax was represented as [`InvalidTagValueNode`](https://phpstan.github.io/phpdoc-parser/2.0.x/PHPStan.PhpDocParser.Ast.PhpDoc.InvalidTagValueNode.html), losing information about a type alias being present.

```php
/**
 * @phpstan-type TypeAlias
 */
```

This `@phpstan-type` is missing the actual type to alias. In phpdoc-parser 2.0 this is now represented as [`TypeAliasTagValueNode`](https://phpstan.github.io/phpdoc-parser/2.0.x/PHPStan.PhpDocParser.Ast.PhpDoc.TypeAliasTagValueNode.html) (instead of `InvalidTagValueNode`) with [`InvalidTypeNode`](https://phpstan.github.io/phpdoc-parser/2.0.x/PHPStan.PhpDocParser.Ast.Type.InvalidTypeNode.html) in place of the type.

### Removal of QuoteAwareConstExprStringNode

The class [QuoteAwareConstExprStringNode](https://phpstan.github.io/phpdoc-parser/1.23.x/PHPStan.PhpDocParser.Ast.ConstExpr.QuoteAwareConstExprStringNode.html) has been removed.

Instead, [ConstExprStringNode](https://phpstan.github.io/phpdoc-parser/2.0.x/PHPStan.PhpDocParser.Ast.ConstExpr.ConstExprStringNode.html) gained information about the kind of quotes being used.

### Removed 2nd parameter of `ConstExprParser::parse()` (`$trimStrings`)

`ConstExprStringNode::$value` now contains unescaped values without surrounding `''` or `""` quotes.

Use `ConstExprStringNode::__toString()` or [`Printer`](https://phpstan.github.io/phpdoc-parser/2.0.x/PHPStan.PhpDocParser.Printer.Printer.html) to get the escaped value along with surrounding quotes.

### Text between tags always belongs to description

Multi-line descriptions between tags were previously represented as separate [PhpDocTextNode](https://phpstan.github.io/phpdoc-parser/2.0.x/PHPStan.PhpDocParser.Ast.PhpDoc.PhpDocTextNode.html):

```php
/**
 * @param Foo $foo 1st multi world description
 * some text in the middle
 * @param Bar $bar 2nd multi world description
 */
```

The line with `some text in the middle` in phpdoc-parser 2.0 is now part of the description of the first `@param` tag.

### `ArrayShapeNode` construction changes

`ArrayShapeNode` constructor made private, added public static methods `createSealed()` and `createUnsealed()`.

### Minor BC breaks

* Constructor parameter `$isEquality` in `AssertTag*ValueNode` made required
* Constructor parameter `$templateTypes` in `MethodTagValueNode` made required
* Constructor parameter `$isReference` in `ParamTagValueNode` made required
* Constructor parameter `$isReference` in `TypelessParamTagValueNode` made required
* Constructor parameter `$templateTypes` in `CallableTypeNode` made required
* Constructor parameters `$expectedTokenValue` and `$currentTokenLine` in `ParserException` made required
* `ArrayShapeItemNode` and `ObjectShapeItemNode` are not standalone TypeNode, just Node
