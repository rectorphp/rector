# Create your own custom FileProcessor

This section is all about creating your custom specific FileProcessor.
If you don´t know the concept of FileProcessors in the context of Rector, have a look at [Beyond PHP - Entering the realm of FileProcessors](beyond_php_file_processors.md)

Most of the examples starting with a rather contrived example, let´s do it the same.

Imagine you would like to replace the sentence "Make america great again" to "Make the whole world a better place to be" in every file named bold_statement.txt.

In order to do so, we create the BoldStatementFileProcessor like that:

```php
<?php
namespace MyVendor\MyPackage\FileProcessor;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;

final class BoldStatementFileProcessor implements FileProcessorInterface
{
    /**
    * @var string
    */
    private const OLD_STATEMENT = 'Make america great again';

    public function supports(File $file): bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        return 'bold_statement.txt' === $smartFileInfo->getBasename();
    }

    /**
     * @param File[] $files
     */
    public function process(array $files): void
    {
        foreach ($files as $file) {
            $this->processFile($file);
        }
    }

    private function processFile(File $file): void
    {
        $oldContent = $file->getFileContent();

        if(false === strpos($oldContent, self::OLD_STATEMENT)) {
            return;
        }

        $newFileContent = str_replace(self::OLD_STATEMENT, 'Make the whole world a better place to be', $oldContent);
        $file->changeFileContent($newFileContent);
    }

    public function getSupportedFileExtensions(): array
    {
        return ['txt'];
    }
}

```

Now register your FileProcessor in your configuration (actually in the container):

```php
<?php
// rector.php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use MyVendor\MyPackage\FileProcessor\BoldStatementFileProcessor;

return static function (ContainerConfigurator $containerConfigurator): void {
    // [...]
    $services = $containerConfigurator->services();
    $services->set(BoldStatementFileProcessor::class);
};
```

Run rector again and see what happens. Yes, we made the world better.

The astute reader has noticed, that the BoldStatementFileProcessor is not really reusable and easily extendable.
So it would be much better to separate the processing from the actual rule(s).
This is also the best practice in all Rector internal FileProcessors. So, let´s just do that.

Create a new dedicated Interface for our rules used by the BoldStatementFileProcessor. Just call it BoldStatementRectorInterface.

```php
<?php

namespace MyVendor\MyPackage\FileProcessor\Rector;

use Rector\Core\Contract\Rector\RectorInterface;

interface BoldStatementRectorInterface extends RectorInterface
{
    public function transform(string $content): string;
}

```

Now, separate the modification from the processing:

```php
<?php

namespace MyVendor\MyPackage\FileProcessor\Rector;
use Rector\Core\ValueObject\Application\File;

final class BoldStatementMakeAmericaGreatAgainRector implements BoldStatementRectorInterface
{
    /**
    * @var string
    */
    private const OLD_STATEMENT = 'Make america great again';

    public function transform(string $content): string
    {
        if(false === strpos($content, self::OLD_STATEMENT)) {
            return;
        }

        return str_replace(self::OLD_STATEMENT, 'Make the whole world a better place to be', $content);
    }
}

```

And change our BoldStatementFileProcessor so it is using one or multiple classes implementing the BoldStatementRectorInterface:

```php
<?php

namespace MyVendor\MyPackage\FileProcessor;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use MyVendor\MyPackage\FileProcessor\Rector\BoldStatementRectorInterface;

final class BoldStatementFileProcessor implements FileProcessorInterface
{
    /**
    * @var  BoldStatementRectorInterface[]
    */
    private $boldStatementRectors;

    /**
    * @param BoldStatementRectorInterface[] $boldStatementRectors
    */
    public function __construct(array $boldStatementRectors)
    {
        $this->boldStatementRectors = $boldStatementRectors;
    }

    public function supports(File $file): bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        return 'bold_statement.txt' === $smartFileInfo->getBasename();
    }

    /**
     * @param File[] $files
     */
    public function process(array $files): void
    {
        foreach ($files as $file) {
            $this->processFile($file);
        }
    }

    private function processFile(File $file): void
    {
        foreach ($this->boldStatementRectors as $boldStatementRector) {
            $changeFileContent = $boldStatementRector->transform($file->getFileContent());
            $file->changeFileContent($changeFileContent);
        }
    }

    public function getSupportedFileExtensions(): array
    {
        return ['txt'];
    }
}

```

Notice the annotation BoldStatementRectorInterface[]. This is important to inject all active classes implementing the BoldStatementRectorInterface into the BoldStatementFileProcessor.
Yes, we said active. So last but not least we must register our new rule in the container, so it is applied:

```php
<?php
// rector.php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use MyVendor\MyPackage\FileProcessor\BoldStatementFileProcessor;
use MyVendor\MyPackage\FileProcessor\Rector\BoldStatementMakeAmericaGreatAgainRector;

return static function (ContainerConfigurator $containerConfigurator): void {
    // [...]
    $services = $containerConfigurator->services();
    $services->set(BoldStatementFileProcessor::class);
    $services->set(BoldStatementMakeAmericaGreatAgainRector::class);
};
```

Run rector again and yes, we made the world a better place again.

Puh. This was a long ride. But we are done and have our new shiny BoldStatementFileProcessor in place.
Now, it´s up to you, to create something useful. But always keep in mind: Try to make the world a better place to be.
