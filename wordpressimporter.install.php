<?php
/**
 * WordPress Importer install script
 *
 * @package WordPress Importer
 * @version 1.0.0
 * @author Rootali
 * @copyright Copyright (c) 2025 Rootali
 * @license BSD
 */

defined('COT_CODE') or die('Wrong URL');

require_once cot_incfile('wordpressimporter', 'plug');

// Create table for import records
if (!$db->tableExists($db_wordpressimporter_imports)) {
    $db->query("CREATE TABLE IF NOT EXISTS `$db_wordpressimporter_imports` (
        `imp_id` int(11) NOT NULL auto_increment,
        `imp_file` varchar(255) NOT NULL,
        `imp_title` varchar(255) NOT NULL,
        `imp_date` int(11) NOT NULL,
        `imp_posts_count` int(11) NOT NULL default '0',
        `imp_pages_count` int(11) NOT NULL default '0',
        `imp_categories_count` int(11) NOT NULL default '0',
        `imp_tags_count` int(11) NOT NULL default '0',
        `imp_attachments_count` int(11) NOT NULL default '0',
        `imp_processed_posts` int(11) NOT NULL default '0',
        `imp_processed_pages` int(11) NOT NULL default '0',
        `imp_processed_categories` int(11) NOT NULL default '0',
        `imp_processed_tags` int(11) NOT NULL default '0',
        `imp_processed_attachments` int(11) NOT NULL default '0',
        `imp_status` varchar(20) NOT NULL default 'new',
        PRIMARY KEY  (`imp_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
}

// Create upload directory
$upload_path = $cfg['plugin']['wordpressimporter']['upload_path'];
if (!file_exists($upload_path)) {
    mkdir($upload_path, 0755, true);
}