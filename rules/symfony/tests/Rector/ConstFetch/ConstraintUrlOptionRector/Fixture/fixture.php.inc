<?php

function constraintUrlOptionRector()
{
    $containt = new Url(['checkDNS' => true]);
}

?>
-----
<?php

function constraintUrlOptionRector()
{
    $containt = new Url(['checkDNS' => \Symfony\Component\Validator\Constraints\Url::CHECK_DNS_TYPE_ANY]);
}

?>
