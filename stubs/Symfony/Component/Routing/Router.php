<?php

declare(strict_types=1);

namespace Symfony\Component\Routing;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

if (class_exists('Symfony\Component\Routing\Router')) {
    return;
}

class Router implements RouterInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        return $this->getGenerator()->generate($name, $parameters, $referenceType);
    }

    private function getGenerator(): UrlGeneratorInterface
    {
    }
}
