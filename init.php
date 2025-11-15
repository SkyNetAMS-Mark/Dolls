<?php
/**
 * Bootstrap file - Load all required files
 */

// Load configuration (which also starts the session)
require_once __DIR__ . '/config.php';

// Load helper functions
require_once __DIR__ . '/includes/functions.php';

// Load classes
// This is the corrected list that matches your actual files
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Product.php';
require_once __DIR__ . '/classes/Bid.php';
require_once __DIR__ . '/classes/User.php';


// Initialize database
$db = Database::getInstance();