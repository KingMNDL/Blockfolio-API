<?php

// The check is to ensure we don't use .env in production
putenv('ENVIRONMENT_FILE=.env.test');

require __DIR__ . '/../../bootstrap.php';
