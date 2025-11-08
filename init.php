<?php
/**
 * Bootstrap file - Load all required files
 */

// Load configuration
require_once __DIR__ . '/config.php';

// Load helper functions
require_once __DIR__ . '/includes/functions.php';

// Load classes
require_once __DIR__ . '/classes/DatabaseModel.php';

require_once __DIR__ . '/classes/Product.php';

require_once __DIR__ . '/classes/Bid.php';

require_once __DIR__ . '/classes/User.php';


// Initialize database
$db = Database::getInstance();
