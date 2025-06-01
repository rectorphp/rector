<?php

declare (strict_types=1);
namespace Rector\Autoloading;

use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\StaticReflection\DynamicSourceLocatorDecorator;
use RectorPrefix202506\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202506\Webmozart\Assert\Assert;
/**
 * Should it pass autoload files/directories to PHPStan analyzer?
 */
final class AdditionalAutoloader
{
    /**
     * @readonly
     */
    private DynamicSourceLocatorDecorator $dynamicSourceLocatorDecorator;
    public function __construct(DynamicSourceLocatorDecorator $dynamicSourceLocatorDecorator)
    {
        $this->dynamicSourceLocatorDecorator = $dynamicSourceLocatorDecorator;
    }
    public function autoloadInput(InputInterface $input) : void
    {
        if (!$input->hasOption(Option::AUTOLOAD_FILE)) {
            return;
        }
        /** @var string|null $autoloadFile */
        $autoloadFile = $input->getOption(Option::AUTOLOAD_FILE);
        if ($autoloadFile === null) {
            return;
        }
        Assert::fileExists($autoloadFile, \sprintf('Extra autoload file %s was not found', $autoloadFile));
        require_once $autoloadFile;
    }
    public function autoloadPaths() : void
    {
        $autoloadPaths = SimpleParameterProvider::provideArrayParameter(Option::AUTOLOAD_PATHS);
        $autoloadPaths = $this->dynamicSourceLocatorDecorator->addPaths($autoloadPaths);
        // set values of Option::AUTOLOAD_PATHS with transformed paths
        SimpleParameterProvider::setParameter(Option::AUTOLOAD_PATHS, $autoloadPaths);
    }
}
