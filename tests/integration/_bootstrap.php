<?php

define('TEMP_DIR', __DIR__.'/../_temp/integration_'.md5(time()));

mkdir(TEMP_DIR);
