<?php

return !strpos($docToken->getContent(), "\n");
return strpos($docToken->getContent(), "\n");
if (!strpos($docToken->getContent(), "\n")) {
    return true;
}
return 5;