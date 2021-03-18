<?php

// mimims: https://github.com/sensiolabs/SensioFrameworkExtraBundle/blob/master/src/Configuration/Route.php

declare(strict_types=1);

namespace Sensio\Bundle\FrameworkExtraBundle\Configuration;

use Symfony\Component\Routing\Annotation\Route as BaseRoute;

if (class_exists('Sensio\Bundle\FrameworkExtraBundle\Configuration\Route')) {
    return;
}

/**
 * @Annotation
 */
class Route extends BaseRoute
{
    private $service;

    public function setService($service)
    {
        // avoid a BC notice in case of @Route(service="") with sf ^2.7
        if (null === $this->getPath()) {
            $this->setPath('');
        }
        $this->service = $service;
    }

    public function getService()
    {
        return $this->service;
    }

    /**
     * Multiple route annotations are allowed.
     *
     * @return bool
     *
     * @see ConfigurationInterface
     */
    public function allowArray()
    {
        return true;
    }
}
