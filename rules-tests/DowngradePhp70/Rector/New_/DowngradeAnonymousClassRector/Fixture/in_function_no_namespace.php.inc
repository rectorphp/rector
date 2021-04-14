<?php

function inFunctionNoNamespace()
{
    return new class {
            public function execute()
            {
            }
    };
}
-----
<?php

class AnonymousFor_inFunctionNoNamespace
{
    public function execute()
    {
    }
}
function inFunctionNoNamespace()
{
    return new AnonymousFor_inFunctionNoNamespace();
}