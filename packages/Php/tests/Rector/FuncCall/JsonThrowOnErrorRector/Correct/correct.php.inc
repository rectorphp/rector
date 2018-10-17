<?php

json_encode($content, JSON_THROW_ON_ERROR);
json_decode($json, null, null, JSON_THROW_ON_ERROR);
