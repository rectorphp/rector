<?php

declare (strict_types=1);
namespace Rector\Symfony\ValueObject\ServiceMap;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Symfony\ValueObject\ServiceDefinition;
final class ServiceMap
{
    /**
     * @var ServiceDefinition[]
     */
    private $services;
    /**
     * @param ServiceDefinition[] $services
     */
    public function __construct(array $services)
    {
        $this->services = $services;
    }
    public function hasService(string $id) : bool
    {
        return isset($this->services[$id]);
    }
    public function getServiceType(string $id) : ?\PHPStan\Type\Type
    {
        $serviceDefinition = $this->getService($id);
        if (!$serviceDefinition instanceof \Rector\Symfony\ValueObject\ServiceDefinition) {
            return null;
        }
        $class = $serviceDefinition->getClass();
        if ($class === null) {
            return null;
        }
        /** @var string[] $interfaces */
        $interfaces = (array) \class_implements($class);
        foreach ($interfaces as $interface) {
            return new \PHPStan\Type\ObjectType($interface);
        }
        return new \PHPStan\Type\ObjectType($class);
    }
    /**
     * @return ServiceDefinition[]
     */
    public function getServicesByTag(string $tagName) : array
    {
        $servicesWithTag = [];
        foreach ($this->services as $service) {
            foreach ($service->getTags() as $tag) {
                if ($tag->getName() !== $tagName) {
                    continue;
                }
                $servicesWithTag[] = $service;
                continue 2;
            }
        }
        return $servicesWithTag;
    }
    private function getService(string $id) : ?\Rector\Symfony\ValueObject\ServiceDefinition
    {
        return $this->services[$id] ?? null;
    }
}
