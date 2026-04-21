<?php
/**
 * PHP Extensions Checker for JOMS LMS
 * Run this script to verify all required PHP extensions are enabled
 *
 * Usage: php check-php-extensions.php
 */

echo "\n";
echo "===========================================\n";
echo "  JOMS LMS - PHP Extensions Checker\n";
echo "===========================================\n\n";

$requiredExtensions = [
    'mbstring' => [
        'required_by' => 'Laravel Framework',
        'purpose' => 'String manipulation functions',
        'xampp_enabled' => true
    ],
    'xml' => [
        'required_by' => 'maatwebsite/excel, Laravel',
        'purpose' => 'XML parsing and Excel export',
        'xampp_enabled' => true
    ],
    'gd' => [
        'required_by' => 'QR code generation, PDF, images',
        'purpose' => 'Image processing (QR codes, PDF)',
        'xampp_enabled' => true
    ],
    'zip' => [
        'required_by' => 'maatwebsite/excel, Composer',
        'purpose' => 'ZIP file creation and extraction',
        'xampp_enabled' => true
    ],
    'fileinfo' => [
        'required_by' => 'Laravel file uploads',
        'purpose' => 'File type detection',
        'xampp_enabled' => true
    ],
    'curl' => [
        'required_by' => 'Laravel HTTP client, PHPMailer',
        'purpose' => 'HTTP requests and API calls',
        'xampp_enabled' => true
    ],
    'openssl' => [
        'required_by' => 'Laravel encryption, HTTPS',
        'purpose' => 'SSL/TLS encryption',
        'xampp_enabled' => true
    ],
    'pdo_mysql' => [
        'required_by' => 'Laravel Database',
        'purpose' => 'MySQL database connection',
        'xampp_enabled' => true
    ],
    'session' => [
        'required_by' => 'Laravel Sessions',
        'purpose' => 'Session management',
        'xampp_enabled' => true
    ],
    'tokenizer' => [
        'required_by' => 'Laravel Framework',
        'purpose' => 'Code parsing',
        'xampp_enabled' => true
    ],
    'ctype' => [
        'required_by' => 'Laravel Framework',
        'purpose' => 'Character type checking',
        'xampp_enabled' => true
    ],
    'json' => [
        'required_by' => 'Laravel Framework',
        'purpose' => 'JSON encoding/decoding',
        'xampp_enabled' => true
    ],
    'intl' => [
        'required_by' => 'Laravel (optional but recommended)',
        'purpose' => 'Internationalization',
        'xampp_enabled' => true
    ],
    'bcmath' => [
        'required_by' => 'Laravel (optional but recommended)',
        'purpose' => 'Precision mathematics',
        'xampp_enabled' => true
    ]
];

$allPassed = true;
$missingExtensions = [];
$missingOptional = [];

// Check each extension
foreach ($requiredExtensions as $extension => $info) {
    $loaded = extension_loaded($extension);
    $isRequired = in_array($extension, ['mbstring', 'xml', 'gd', 'zip', 'fileinfo', 'curl', 'openssl', 'pdo_mysql', 'session', 'tokenizer', 'ctype', 'json']);

    $status = $loaded ? '[OK]' : '[MISSING]';
    $color = $loaded ? "\033[32m" : "\033[31m"; // Green or Red
    $reset = "\033[0m";

    echo "{$color}{$status}\033[0m {$extension}\n";
    echo "    Used by: {$info['required_by']}\n";
    echo "    Purpose: {$info['purpose']}\n";

    if (!$loaded) {
        if ($isRequired) {
            $missingExtensions[] = $extension;
            $allPassed = false;
        } else {
            $missingOptional[] = $extension;
        }
        echo "    \033[31m! This extension needs to be enabled in php.ini\033[0m\n";
    }
    echo "\n";
}

// Summary
echo "-------------------------------------------\n";
if ($allPassed && empty($missingOptional)) {
    echo "\033[32mSUCCESS:\033[0m All required PHP extensions are loaded!\n\n";
    echo "You can now run:\n";
    echo "  composer install\n";
    echo "  php artisan migrate\n";
} else {
    if (!empty($missingExtensions)) {
        echo "\033[31mERROR:\033[0m Missing required extensions: " . implode(', ', $missingExtensions) . "\n\n";
    }
    if (!empty($missingOptional)) {
        echo "\033[33mWARNING:\033[0m Missing optional extensions: " . implode(', ', $missingOptional) . "\n\n";
    }

    echo "To enable extensions in XAMPP:\n";
    echo "1. Open: C:\\xampp\\php\\php.ini\n";
    echo "2. Find each extension line (e.g., ;extension=gd)\n";
    echo "3. Remove the semicolon (;) at the start\n";
    echo "4. Save the file and restart Apache\n\n";

    echo "Lines to uncomment in php.ini:\n";
    foreach (array_merge($missingExtensions, $missingOptional) as $ext) {
        $iniName = $ext === 'pdo_mysql' ? 'pdo_mysql' : $ext;
        echo "  extension={$iniName}\n";
    }
    echo "\n";
}

echo "===========================================\n\n";

// Also check PHP version
echo "PHP Version: " . PHP_VERSION . "\n";
if (version_compare(PHP_VERSION, '8.2.0', '<')) {
    echo "\033[31mERROR:\033[0m PHP 8.2 or higher is required. You are using " . PHP_VERSION . "\n";
    $allPassed = false;
} else {
    echo "\033[32mOK:\033[0m PHP version meets requirements\n";
}
echo "\n";

exit($allPassed ? 0 : 1);
