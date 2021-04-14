<?php

class NoNamespace
{
    public function run()
    {
        return new class {
            public function execute()
            {
            }
        };
    }
}
-----
<?php

class AnonymousFor_NoNamespace
{
    public function execute()
    {
    }
}
class NoNamespace
{
    public function run()
    {
        return new AnonymousFor_NoNamespace();
    }
}