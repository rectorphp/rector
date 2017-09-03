<?php declare (strict_types=1);

$containerBuilder = new \Symfony\Component\DependencyInjection\ContainerBuilder;
$containerBuilder->addCompilerPass(new Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\SerializerPass);
