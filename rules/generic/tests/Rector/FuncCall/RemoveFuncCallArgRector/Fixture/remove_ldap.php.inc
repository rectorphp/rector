<?php

namespace Rector\Generic\Tests\Rector\FuncCall\RemoveFuncCallArgRector\Fixture;

class SomeClass
{
    public function run()
    {
        $ber_identifier = 'random_value';
        $name = ldap_first_attribute($this->_ldap->getResource(), $this->_current, $ber_identifier);

        return $name;
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\FuncCall\RemoveFuncCallArgRector\Fixture;

class SomeClass
{
    public function run()
    {
        $ber_identifier = 'random_value';
        $name = ldap_first_attribute($this->_ldap->getResource(), $this->_current);

        return $name;
    }
}

?>
