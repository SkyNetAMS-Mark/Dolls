<?php
/**
 * Bootstrap file - Load all required files
 */

// Load configuration
require_once __DIR__ . '/config.php';

// Load helper functions
require_once __DIR__ . '/includes/functions.php';

// Load classes
// This is the corrected list that matches your actual files
require_once __DIR__ . '/classes/DatabaseModel.php';
require_once __DIR__ . '/classes/ProductModel.php';
require_once __DIR__ . '/classes/BidModel.php';
require_once __DIR__ . '/classes/UserModel.php';


// Initialize database
$db = Database::getInstance();