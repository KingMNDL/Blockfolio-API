<?php
declare(strict_types=1);
/**
 * Application bootstrap file to load specified environment variables.
 *
 * @see ./public/index.php
 * @see ./tests/bootstrap.php
 */
use Symfony\Component\Dotenv\Dotenv;

$environmentFile = getenv('ENVIRONMENT_FILE');
// Application is started against 'fastest' library, so we need to override database name manually

if ($readableChannel = getenv('ENV_TEST_CHANNEL')) {
    // Parse current '.env.test' file
    $variables = (new Dotenv())->parse(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $environmentFile));

    $variables = updateVars(
        $variables,
        $readableChannel
    );

    $variables = updateVars(
        $variables,
        $readableChannel,
        'DATABASE_URL'
    );

    // And finally populate new variables to current environment
    // !!! Existing variables wont be overridden
    (new Dotenv())->populate($variables);
} else {
    // Load environment variables normally
    (new Dotenv())->overload(__DIR__ . DIRECTORY_SEPARATOR . $environmentFile);
}

/**
 * @param array $variables
 * @param string $readableChannel
 * @param string $dbNameIndex
 * @param string $dbUrlIndex
 * @return mixed
 */
function updateVars(array $variables, string $readableChannel, $dbNameIndex = 'DATABASE_NAME', $dbUrlIndex = 'DATABASE_URL')
{
    $databaseName = $variables[$dbNameIndex] . '_' . $readableChannel;
    // Replace DATABASE_URL variable
    $variables[$dbUrlIndex] = \str_replace(
        '/' . $variables[$dbNameIndex],
        '/' . $databaseName,
        $variables[$dbUrlIndex]
    );
    // Replace DATABASE_NAME variable
    $variables[$dbNameIndex] = $databaseName;

    //Since `Dotenv::populate()` method doc states that existing variables wont be overridden - override them with force!
    $_SERVER[$dbUrlIndex] = $variables[$dbUrlIndex];
    $_SERVER[$dbNameIndex] = $variables[$dbNameIndex];

    $_ENV[$dbUrlIndex] = $variables[$dbUrlIndex];
    $_ENV[$dbNameIndex] = $variables[$dbNameIndex];

    putenv("{$dbNameIndex}={$variables[$dbNameIndex]}");
    putenv("{$dbUrlIndex}={$variables[$dbUrlIndex]}");

    return $variables;
}
