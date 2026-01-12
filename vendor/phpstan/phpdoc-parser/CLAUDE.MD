# CLAUDE.MD - Instructions for AI Assistant

This file contains important instructions for working on the phpdoc-parser project.

## Project Overview

**phpstan/phpdoc-parser** is a library that represents PHPDocs with an Abstract Syntax Tree (AST). It supports parsing and modifying PHPDocs, and is primarily used by PHPStan for static analysis.

### Key Features
- Parses PHPDoc comments into an AST representation
- Supports all PHPDoc tags and types (see [PHPStan documentation](https://phpstan.org/writing-php-code/phpdocs-basics))
- Format-preserving printer for modifying and printing AST nodes
- Support for Doctrine Annotations parsing
- Nullable, intersection, generic, and conditional types support

### Requirements
- PHP ^7.4 || ^8.0
- Platform target: PHP 7.4.6

## Project Structure

### Source Code (`src/`)

The source code is organized into the following main components:

1. **Lexer** (`src/Lexer/`)
   - `Lexer.php` - Tokenizes PHPDoc strings

2. **Parser** (`src/Parser/`)
   - `PhpDocParser.php` - Main PHPDoc parser (parses tags and structure)
   - `TypeParser.php` - Parses PHPDoc type expressions
   - `ConstExprParser.php` - Parses constant expressions
   - `TokenIterator.php` - Iterator for tokens
   - `StringUnescaper.php` - Handles string unescaping
   - `ParserException.php` - Exception handling

3. **AST** (`src/Ast/`)
   - `Node.php` - Base AST node interface
   - `NodeTraverser.php` - Traverses and transforms AST
   - `NodeVisitor.php` - Visitor pattern for AST traversal
   - `Type/` - Type nodes (GenericTypeNode, ArrayTypeNode, UnionTypeNode, etc.)
   - `PhpDoc/` - PHPDoc tag nodes (ParamTagValueNode, ReturnTagValueNode, etc.)
   - `ConstExpr/` - Constant expression nodes
   - `NodeVisitor/` - Built-in visitors (CloningVisitor, etc.)

4. **Printer** (`src/Printer/`)
   - `Printer.php` - Prints AST back to PHPDoc format
   - `Differ.php` - Computes differences between AST nodes
   - `DiffElem.php` - Represents diff elements

5. **Configuration**
   - `ParserConfig.php` - Parser configuration (attributes to use)

### Tests (`tests/PHPStan/`)

Tests mirror the source structure and include:

1. **Parser Tests** (`tests/PHPStan/Parser/`)
   - `TypeParserTest.php` - Type parsing tests
   - `PhpDocParserTest.php` - PHPDoc parsing tests
   - `ConstExprParserTest.php` - Constant expression parsing tests
   - `FuzzyTest.php` - Fuzzy testing
   - `Doctrine/` - Doctrine annotation test fixtures

2. **AST Tests** (`tests/PHPStan/Ast/`)
   - `NodeTraverserTest.php` - Node traversal tests
   - `Attributes/AttributesTest.php` - AST attribute tests
   - `ToString/` - Tests for converting AST to string
   - `NodeVisitor/` - Visitor pattern tests

3. **Printer Tests** (`tests/PHPStan/Printer/`)
   - Tests for format-preserving printing functionality

### Configuration Files

- `phpunit.xml` - PHPUnit test configuration
- `phpstan.neon` - PHPStan static analysis configuration
- `phpstan-baseline.neon` - PHPStan baseline (known issues)
- `phpcs.xml` - PHP CodeSniffer configuration
- `composer.json` - Dependencies and autoloading

## How the Parser Works

The parsing flow follows these steps:

1. **Lexing**: `Lexer` tokenizes the PHPDoc string into tokens
2. **Parsing**: `PhpDocParser` uses `TypeParser` and `ConstExprParser` to build an AST
3. **Traversal/Modification**: `NodeTraverser` with `NodeVisitor` can traverse and modify the AST
4. **Printing**: `Printer` converts the AST back to PHPDoc format (optionally preserving formatting)

### Basic Usage Example

```php
$config = new ParserConfig(usedAttributes: []);
$lexer = new Lexer($config);
$constExprParser = new ConstExprParser($config);
$typeParser = new TypeParser($config, $constExprParser);
$phpDocParser = new PhpDocParser($config, $typeParser, $constExprParser);

$tokens = new TokenIterator($lexer->tokenize('/** @param Lorem $a */'));
$phpDocNode = $phpDocParser->parse($tokens);
```

### Format-Preserving Printing

For format-preserving printing (used when modifying existing PHPDocs), enable these attributes:
- `lines` - Preserve line information
- `indexes` - Preserve token indexes
- `comments` - Preserve comments

## Common Development Tasks

### Adding a New PHPDoc Tag
1. Create a new `*TagValueNode` class in `src/Ast/PhpDoc/`
2. Add parsing logic in `PhpDocParser.php`
3. Add tests in `tests/PHPStan/Parser/PhpDocParserTest.php`
4. Run tests and PHPStan

### Adding a New Type Node
1. Create a new `*TypeNode` class in `src/Ast/Type/`
2. Add parsing logic in `TypeParser.php`
3. Add printing logic in `Printer.php`
4. Add tests in `tests/PHPStan/Parser/TypeParserTest.php`
5. Run tests and PHPStan

### Modifying the Lexer
1. Update token generation in `Lexer.php`
2. Update parsers that consume those tokens
3. Add/update tests
4. Run comprehensive checks with `make check`

## Testing and Quality Checks

### Running Tests

Tests are run using PHPUnit:
```bash
make tests
```

Or directly:
```bash
php vendor/bin/phpunit
```

### Running PHPStan

PHPStan static analysis is run with:
```bash
make phpstan
```

Or directly:
```bash
php vendor/bin/phpstan
```

### Running All Checks

To run all quality checks (lint, code style, tests, and PHPStan):
```bash
make check
```

This runs:
- `lint` - PHP syntax checking with parallel-lint
- `cs` - Code style checking with phpcs
- `tests` - PHPUnit test suite
- `phpstan` - Static analysis

## CRITICAL RULES

### ⚠️ MANDATORY: Run After Every Change

**You MUST run both tests and PHPStan after every code change:**

```bash
make tests && make phpstan
```

Or use the comprehensive check:
```bash
make check
```

**DO NOT** commit or consider work complete until both tests and PHPStan pass successfully.

### ⚠️ NEVER Delete Tests

**NEVER delete any tests.** Tests are critical to the project's quality and regression prevention. If tests are failing:
- Fix the implementation to make tests pass
- Only modify tests if they contain actual bugs or if requirements have legitimately changed
- When in doubt, ask before modifying any test

## Other Available Commands

- `make cs-fix` - Automatically fix code style issues
- `make lint` - Check PHP syntax only
- `make cs` - Check code style only
- `make phpstan-generate-baseline` - Generate PHPStan baseline (use sparingly)

## Workflow Summary

1. Make code changes
2. Run `make tests` - ensure all tests pass
3. Run `make phpstan` - ensure static analysis passes
4. Fix any issues found
5. Commit only when both pass
6. Repeat as needed

**Remember: Tests and PHPStan MUST pass before any commit.**

## Coding Standards and Best Practices

### Code Style
- Follow PSR-12 coding standards (enforced by phpcs)
- Use tabs for indentation (project convention)
- Run `make cs-fix` to automatically fix code style issues
- Always run `make cs` to verify code style before committing

### PHPStan Rules
- Project uses strict PHPStan rules (level max)
- All code must pass static analysis
- Avoid adding to phpstan-baseline.neon unless absolutely necessary
- Type hints are required for all public APIs

### Testing Best Practices
- All new features must include tests
- Tests should be in the corresponding test directory matching src/ structure
- Use data providers for testing multiple similar cases
- Test both valid and invalid inputs
- Include edge cases and error conditions

### AST Node Conventions
- All AST nodes implement the `Node` interface
- Nodes should be immutable where possible
- Use `__toString()` for debugging output
- Implement proper equality checks if needed
- Follow the visitor pattern for AST traversal

### Parser Patterns
- Parsers should be recursive descent style
- Use `TokenIterator` for token consumption
- Throw `ParserException` for syntax errors
- Support optional attributes through `ParserConfig`
- Maintain backwards compatibility when adding features

## Important Notes

### Backwards Compatibility
- This library is used by PHPStan and many other tools
- Breaking changes should be avoided
- New features should be opt-in when possible
- Deprecate before removing functionality

### Performance Considerations
- The parser is performance-critical (runs on large codebases)
- Avoid unnecessary object allocations
- Be careful with regex patterns
- Consider memory usage in loops

### Documentation
- Public APIs should have PHPDoc comments
- Complex logic should include inline comments
- Update README.md when adding major features
- Reference PHPStan documentation for PHPDoc tag specifications
