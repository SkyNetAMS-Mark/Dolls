<?php
/**
 * Bootstrap file - Load all required files
 */

// Load configuration (which also starts the session)
require_once __DIR__ . '/config.php';

// Load helper functions
require_once __DIR__ . '/functions.php';

// Load Database class (NOT DatabaseModel.php - they're the same)
require_once __DIR__ . '/Database.php';

// Load Product class (NOT ProductModel.php - they're the same)
require_once __DIR__ . '/Product.php';

// Load Bid class (NOT BidModel.php - they're the same)
require_once __DIR__ . '/Bid.php';

// Load User class (NOT UserModel.php - they're the same)
require_once __DIR__ . '/User.php';

// Initialize database
$db = Database::getInstance();
