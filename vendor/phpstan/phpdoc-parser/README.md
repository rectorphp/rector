<h1 align="center">PHPDoc Parser for PHPStan</h1>

<p align="center">
	<a href="https://github.com/phpstan/phpdoc-parser/actions"><img src="https://github.com/phpstan/phpdoc-parser/workflows/Build/badge.svg" alt="Build Status"></a>
	<a href="https://packagist.org/packages/phpstan/phpdoc-parser"><img src="https://poser.pugx.org/phpstan/phpdoc-parser/v/stable" alt="Latest Stable Version"></a>
	<a href="https://choosealicense.com/licenses/mit/"><img src="https://poser.pugx.org/phpstan/phpstan/license" alt="License"></a>
	<a href="https://phpstan.org/"><img src="https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat" alt="PHPStan Enabled"></a>
</p>

This library `phpstan/phpdoc-parser` represents PHPDocs with an AST (Abstract Syntax Tree). It supports parsing and modifying PHPDocs.

For the complete list of supported PHPDoc features check out PHPStan documentation. PHPStan is the main (but not the only) user of this library.

* [PHPDoc Basics](https://phpstan.org/writing-php-code/phpdocs-basics) (list of PHPDoc tags)
* [PHPDoc Types](https://phpstan.org/writing-php-code/phpdoc-types) (list of PHPDoc types)
* [phpdoc-parser API Reference](https://phpstan.github.io/phpdoc-parser/1.23.x/namespace-PHPStan.PhpDocParser.html) with all the AST node types etc.

This parser also supports parsing [Doctrine Annotations](https://github.com/doctrine/annotations). The AST nodes live in the [PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine namespace](https://phpstan.github.io/phpdoc-parser/1.23.x/namespace-PHPStan.PhpDocParser.Ast.PhpDoc.Doctrine.html). The support needs to be turned on by setting `bool $parseDoctrineAnnotations` to `true` in `Lexer` and `PhpDocParser` class constructors.

## Installation

```
composer require phpstan/phpdoc-parser
```

## Basic usage

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

// basic setup

$lexer = new Lexer();
$constExprParser = new ConstExprParser();
$typeParser = new TypeParser($constExprParser);
$phpDocParser = new PhpDocParser($typeParser, $constExprParser);

// parsing and reading a PHPDoc string

$tokens = new TokenIterator($lexer->tokenize('/** @param Lorem $a */'));
$phpDocNode = $phpDocParser->parse($tokens); // PhpDocNode
$paramTags = $phpDocNode->getParamTagValues(); // ParamTagValueNode[]
echo $paramTags[0]->parameterName; // '$a'
echo $paramTags[0]->type; // IdentifierTypeNode - 'Lorem'
```

### Format-preserving printer

This component can be used to modify the AST
and print it again as close as possible to the original.

It's heavily inspired by format-preserving printer component in [nikic/PHP-Parser](https://github.com/nikic/PHP-Parser).

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use PHPStan\PhpDocParser\Ast\NodeTraverser;
use PHPStan\PhpDocParser\Ast\NodeVisitor\CloningVisitor;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\Printer\Printer;

// basic setup with enabled required lexer attributes

$usedAttributes = ['lines' => true, 'indexes' => true];

$lexer = new Lexer();
$constExprParser = new ConstExprParser(true, true, $usedAttributes);
$typeParser = new TypeParser($constExprParser, true, $usedAttributes);
$phpDocParser = new PhpDocParser($typeParser, $constExprParser, true, true, $usedAttributes);

$tokens = new TokenIterator($lexer->tokenize('/** @param Lorem $a */'));
$phpDocNode = $phpDocParser->parse($tokens); // PhpDocNode

$cloningTraverser = new NodeTraverser([new CloningVisitor()]);

/** @var PhpDocNode $newPhpDocNode */
[$newPhpDocNode] = $cloningTraverser->traverse([$phpDocNode]);

// change something in $newPhpDocNode
$newPhpDocNode->getParamTagValues()[0]->type = new IdentifierTypeNode('Ipsum');

// print changed PHPDoc
$printer = new Printer();
$newPhpDoc = $printer->printFormatPreserving($newPhpDocNode, $phpDocNode, $tokens);
echo $newPhpDoc; // '/** @param Ipsum $a */'
```

## Code of Conduct

This project adheres to a [Contributor Code of Conduct](CODE_OF_CONDUCT.md). By participating in this project and its community, you are expected to uphold this code.

## Building

Initially you need to run `composer install`, or `composer update` in case you aren't working in a folder which was built before.

Afterwards you can either run the whole build including linting and coding standards using

    make

or run only tests using

    make tests
