<?php

declare (strict_types=1);
namespace Rector\Core\Configuration;

use RectorPrefix20220501\Symplify\PackageBuilder\Parameter\ParameterProvider;
/**
 * Rector native configuration provider, to keep deprecated options hidden,
 * but also provide configuration that custom rules can check
 */
final class RectorConfigProvider
{
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    public function __construct(\RectorPrefix20220501\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider)
    {
        $this->parameterProvider = $parameterProvider;
    }
    public function shouldImportNames() : bool
    {
        return $this->parameterProvider->provideBoolParameter(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES);
    }
}
