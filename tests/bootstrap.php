<?php

declare(strict_types=1);

/*
 * This file is part of the Magpie PHP SDK.
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Set up test environment
if (!defined('MAGPIE_TEST_MODE')) {
    define('MAGPIE_TEST_MODE', true);
}

// Ensure we have required environment variables for testing
if (empty($_ENV['MAGPIE_SECRET_KEY'])) {
    $_ENV['MAGPIE_SECRET_KEY'] = 'sk_test_fake_key_for_testing';
}

echo "Magpie PHP SDK Test Suite\n";
echo "========================\n\n";
