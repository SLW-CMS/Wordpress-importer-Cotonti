<?php
/**
 * WordPress Importer uninstall script
 *
 * @package WordPress Importer
 * @version 1.0.0
 * @author Rootali
 * @copyright Copyright (c) 2025 Rootali
 * @license BSD
 */

defined('COT_CODE') or die('Wrong URL');

require_once cot_incfile('wordpressimporter', 'plug');

// Drop table
if ($db->tableExists($db_wordpressimporter_imports)) {
    $db->query("DROP TABLE IF EXISTS `$db_wordpressimporter_imports`");
}

// Delete all files in upload directory
$upload_path = $cfg['plugin']['wordpressimporter']['upload_path'];
if (file_exists($upload_path)) {
    $files = glob($upload_path . '*.xml');
    foreach ($files as $file) {
        @unlink($file);
    }
    
    // Try to remove directory
    @rmdir($upload_path);
}