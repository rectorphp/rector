<?php

use Rector\Guzzle\Tests\Rector\MethodCall\MessageAsArrayRector\Source\MessageType;

$message = new MessageType();

$value = $message->getMessage('key', true);
