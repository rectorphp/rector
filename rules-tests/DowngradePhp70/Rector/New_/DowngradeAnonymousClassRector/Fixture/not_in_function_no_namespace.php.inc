<?php

return new class {
    public function execute()
    {
    }
};
-----
<?php

class AnonymousFor_NotInfunctionNoNamespace
{
    public function execute()
    {
    }
}
return new AnonymousFor_NotInfunctionNoNamespace();