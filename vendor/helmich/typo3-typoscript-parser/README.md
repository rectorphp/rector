TypoScript Parser
=================

![Build Status](https://github.com/martin-helmich/typo3-typoscript-parser/workflows/PHP%20type%20checking%20and%20unit%20testing/badge.svg?branch=master)
[![Code Climate](https://codeclimate.com/github/martin-helmich/typo3-typoscript-parser/badges/gpa.svg)](https://codeclimate.com/github/martin-helmich/typo3-typoscript-parser)
[![Test Coverage](https://codeclimate.com/github/martin-helmich/typo3-typoscript-parser/badges/coverage.svg)](https://codeclimate.com/github/martin-helmich/typo3-typoscript-parser/coverage)
[![Dependabot Status](https://api.dependabot.com/badges/status?host=github&repo=martin-helmich/typo3-typoscript-parser)](https://dependabot.com)

Author
======

Martin Helmich (typo3 at martin-helmich dot de)

Synopsis
========

This package contains a library offering a tokenizer and a parser for TYPO3's
configuration language, "TypoScript".

Why?
====

Just as [typoscript-lint](https://github.com/martin-helmich/typo3-typoscript-lint),
this project started of as a simple programming excercise. Tokenizer and parser
could probably implemented in a better way (it's open source, go for it!).

Usage
=====

Parsing TypoScript
------------------

You can use the `Helmich\TypoScriptParser\Parser\Parser` class to generate a syntax
tree from source code input. The class requires an instance of the `Helmich\TypoScriptParser\Tokenizer\Tokenizer`
class as dependency. When using the Symfony DependencyInjection component, you can
simply use the service `parser` for this.

```php
use Helmich\TypoScriptParser\Parser\Parser,
    Helmich\TypoScriptParser\Tokenizer\Tokenizer;

$typoscript = file_get_contents('path/to/typoscript.ts');
$parser     = new Parser(new Tokenizer());
$statements = $parser->parse($typoscript);
```

Analyzing TypoScript
--------------------

You can analyze the generated syntax tree by implementing [visitors](http://en.wikipedia.org/wiki/Visitor_pattern).
For example, let's implement a check that checks for non-CGL-compliant variable
names (there's probably no use case for that, just as a simple example):

First, we need the respective visitor implementation:

```php
use Helmich\TypoScriptParser\Parser\Traverser\Visitor,
    Helmich\TypoScriptParser\Parser\AST\Statement,
    Helmich\TypoScriptParser\Parser\AST\Operator\Assignment,
    Helmich\TypoScriptParser\Parser\AST\NestedAssignment;

class VariableNamingCheckVisitor implements Visitor {
    public function enterTree(array $statements) {}
    public function enterNode(Statement $statement) {
        if ($statement instanceof Assignment || $statement instanceof NestedAssignment) {
            if (!preg_match(',^[0-9]+$,', $statement->object->relativePath)) {
                throw new \Exception('Variable names must be numbers only!');
            }
        }
    }
    public function exitNode(Statement $statement) {}
    public function exitTree(array $statements) {}
}
```

Then traverse the syntax tree:

```php
use Helmich\TypoScriptParser\Parser\Traverser\Traverser;

$traverser = new Traverser($statements);
$traverser->addVisitor(new VariableNamingCheckVisitor());
$traverser->walk();
```
