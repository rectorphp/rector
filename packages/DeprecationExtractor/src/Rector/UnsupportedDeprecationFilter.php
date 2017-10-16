<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Rector;

use Nette\Utils\Strings;
use Rector\DeprecationExtractor\Deprecation\Deprecation;

final class UnsupportedDeprecationFilter
{
    /**
     * @var string[]
     */
    private $yamlDeprecationMessages = [
        'Autowiring-types are deprecated',
        'The "=" suffix that used to disable strict references',
        'The XmlFileLoader will raise an exception in Symfony 4.0, instead of silently ignoring unsupported',
        'The "strict" attribute used when referencing the "" service is deprecated',
        'Service names that start with an underscore are deprecated',
        'configuration key',
    ];

    /**
     * @var string[]
     */
    private $serviceDeprecationMessages = [
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
        foreach ($this->yamlDeprecationMessages as $yamlDeprecationMessage) {
            if (Strings::contains($deprecation->getMessage(), $yamlDeprecationMessage)) {
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
