<?php

declare (strict_types=1);
namespace Rector\Core\Configuration;

use Rector\Core\Configuration\Parameter\ParameterProvider;
/**
 * Rector native configuration provider, to keep deprecated options hidden,
 * but also provide configuration that custom rules can check
 */
final class RectorConfigProvider
{
    /**
     * @readonly
     * @var \Rector\Core\Configuration\Parameter\ParameterProvider
     */
    private $parameterProvider;
    public function __construct(ParameterProvider $parameterProvider)
    {
        $this->parameterProvider = $parameterProvider;
    }
    public function shouldImportNames() : bool
    {
        return $this->parameterProvider->provideBoolParameter(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES);
    }
    public function shouldRemoveUnusedImports() : bool
    {
        return $this->parameterProvider->provideBoolParameter(\Rector\Core\Configuration\Option::REMOVE_UNUSED_IMPORTS);
    }
    /**
     * @api symfony
     */
    public function getSymfonyContainerPhp() : string
    {
        return $this->parameterProvider->provideStringParameter(\Rector\Core\Configuration\Option::SYMFONY_CONTAINER_PHP_PATH_PARAMETER);
    }
    /**
     * @api symfony
     */
    public function getSymfonyContainerXml() : string
    {
        return $this->parameterProvider->provideStringParameter(\Rector\Core\Configuration\Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER);
    }
    public function getIndentChar() : string
    {
        return $this->parameterProvider->provideStringParameter(\Rector\Core\Configuration\Option::INDENT_CHAR);
    }
    public function getIndentSize() : int
    {
        return $this->parameterProvider->provideIntParameter(\Rector\Core\Configuration\Option::INDENT_SIZE);
    }
}
