<?php

use Rector\Guzzle\Tests\Rector\MethodCall\MessageAsArrayRector\Source\MessageType;

function messageAsArray()
{
    $message = new MessageType();

    $value = $message->getMessage('key', true);
}

?>
-----
<?php

use Rector\Guzzle\Tests\Rector\MethodCall\MessageAsArrayRector\Source\MessageType;

function messageAsArray()
{
    $message = new MessageType();

    $value = $message->getMessageAsArray('key');
}

?>
