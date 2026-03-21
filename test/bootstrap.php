<?php
declare(strict_types=1);

/**
 * Test suite bootstrap for Horde\Injector
 *
 * Loads composer autoloader to make all classes available for testing.
 */

// Locate composer autoloader
$autoloadCandidates = [
    __DIR__ . '/../vendor/autoload.php',      // Standalone component
    __DIR__ . '/../../../autoload.php',       // Installed via composer
];

foreach ($autoloadCandidates as $autoloadFile) {
    if (file_exists($autoloadFile)) {
        require_once $autoloadFile;
        return;
    }
}

// If we reach here, autoloader not found
fwrite(
    STDERR,
    'Unable to find composer autoloader. Run: composer install' . PHP_EOL
);
exit(1);
