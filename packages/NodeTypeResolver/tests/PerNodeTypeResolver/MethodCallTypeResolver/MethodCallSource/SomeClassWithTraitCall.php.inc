<?php

$someService = new \Rector\NodeTypeResolver\Tests\Source\SomeClassWithTrait();
$anotherService = $someService->getDoctrine()->getManager();