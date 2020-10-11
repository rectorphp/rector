# Simple PHP Doc Parser

Simple service integration of phpstan/phpdoc-parser, with few extra goodies for practical use

## 1. Install

```bash
composer require rector/simple-php-doc-parser
```

## 2. Register Bundle

Register bundle in your project:

```php
// app/bundles.php
return [
    Rector\SimplePhpDocParser\Bundle\SimplePhpDocParserBundle::class => [
        'all' => true,
    ],
];
```

or via Kernel:

```php
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Rector\SimplePhpDocParser\Bundle\SimplePhpDocParserBundle;

final class AppKernel extends Kernel
{
    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): array
    {
        return [new SimplePhpDocParserBundle()];
    }
}
```

## 3. Usage

Required services `Rector\SimplePhpDocParser\SimplePhpDocParser` in constructor, where you need it, and use it:

```php
use Rector\SimplePhpDocParser\SimplePhpDocParser;

final class SomeClass
{
    /**
     * @var SimplePhpDocParser
     */
    private $simplePhpDocParser;

    public function __construct(SimplePhpDocParser $simplePhpDocParser)
    {
        $this->simplePhpDocParser = $simplePhpDocParser;
    }

    public function some()
    {
        $docBlock = '/** @param int $name */';

        /** @var \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode $phpDocNode */
        $phpDocNode = $this->simplePhpDocParser->parseDocBlock($docBlock);

        // param extras

        /** @var \PHPStan\PhpDocParser\Ast\Type\TypeNode $nameParamType */
        $nameParamType = $phpDocNode->getParamType('name');

        /** @var \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode $nameParamTagValueNode */
        $nameParamTagValueNode = $phpDocNode->getParam('name');
    }
}
```
