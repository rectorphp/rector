<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Rector;

use Nette\Utils\Strings;
use Rector\DeprecationExtractor\Deprecation\Deprecation;

final class UnsupportedDeprecationFilter
{
    /**
     * @var string[]
     */
    private $configDeprecationMessages = [
        'Autowiring-types are deprecated',
        'The "=" suffix that used to disable strict references',
        'The XmlFileLoader will raise an exception in Symfony 4.0, instead of silently ignoring unsupported',
        'The "strict" attribute used when referencing the "" service is deprecated',
        'Service names that start with an underscore are deprecated',
        'configuration key',
        // Laravel
        "Key 'create' is deprecated, use 'factory' or 'type' in configuration",
    ];

    /**
     * @var string[]
     */
    private $serviceDeprecationMessages = [
        // Symfony
        'It should either be deprecated or its implementation upgraded.',
        'It should either be deprecated or its factory upgraded.',
        'Service identifiers will be made case sensitive',
        'Generating a dumped container without populating the method map is deprecated',
        'Dumping an uncompiled ContainerBuilder is deprecated',
        'service is private',
        'service is already initialized, ',
        'Relying on its factory\'s return-type to define the class of service',
    ];

    public function matches(Deprecation $deprecation): bool
    {
        foreach ($this->configDeprecationMessages as $configDeprecationMessage) {
            if (Strings::contains($deprecation->getMessage(), $configDeprecationMessage)) {
                return true;
            }
        }

        foreach ($this->serviceDeprecationMessages as $serviceDeprecationMessage) {
            if (Strings::contains($deprecation->getMessage(), $serviceDeprecationMessage)) {
                return true;
            }
        }

        return false;
    }
}
