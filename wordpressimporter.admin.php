<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=tools
[END_COT_EXT]
==================== */

/**
 * WordPress Importer admin interface
 *
 * @package WordPress Importer
 * @version 1.0.0
 * @author Rootali
 * @copyright Copyright (c) 2025 Rootali
 * @license BSD
 */

defined('COT_CODE') or die('Wrong URL.');

// Load functions API
require_once cot_incfile('wordpressimporter', 'plug', 'functions');
require_once cot_incfile('forms', 'core');

// Prepare admin menu
$adminhelp = $L['wordpressimporter_help'];
$adminsubtitle = $L['wordpressimporter_title'];

// Initialize variables
$id = cot_import('id', 'G', 'INT');
$a = cot_import('a', 'G', 'ALP');

// Create necessary table if not exists
wpi_create_tables();

// Handle actions
if ($a == 'upload' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle file upload
    $upload_result = wpi_upload_file('wordpress_xml');
    
    if ($upload_result['success']) {
        // Parse uploaded XML
        $parse_result = wpi_parse_wordpress_xml($upload_result['filename']);
        
        if ($parse_result['success']) {
            // Create import record
            $import_id = wpi_create_import($parse_result['info']);
            
            if ($import_id) {
                cot_message('wordpressimporter_uploaded_success');
                cot_redirect(cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter', 'id' => $import_id], '', true));
            } else {
                cot_error('wordpressimporter_db_error');
            }
        } else {
            cot_error($L['wordpressimporter_' . $parse_result['error']]);
        }
    } else {
        cot_error($L['wordpressimporter_' . $upload_result['error']]);
    }
    
    cot_redirect(cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter'], '', true));
} 
else if ($a == 'delete' && $id > 0) {
    // Delete import
    if (wpi_delete_import($id)) {
        cot_message('wordpressimporter_delete_success');
    } else {
        cot_error('wordpressimporter_delete_error');
    }
    
    cot_redirect(cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter'], '', true));
} 
else if ($a == 'select' && $id > 0) {
    // Show selection form for import options
    $import = wpi_get_import($id);
    
    if (!$import || $import['imp_status'] != 'new') {
        cot_redirect(cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter'], '', true));
    }
    
    $t = new XTemplate(cot_tplfile('wordpressimporter.select', 'plug'));
    
    // Assign import data to template
    $t->assign(array(
        'IMPORT_ID' => $import['imp_id'],
        'IMPORT_TITLE' => $import['imp_title'],
        'IMPORT_POSTS_COUNT' => $import['imp_posts_count'],
        'IMPORT_PAGES_COUNT' => $import['imp_pages_count'],
        'IMPORT_CATEGORIES_COUNT' => $import['imp_categories_count'],
        'IMPORT_TAGS_COUNT' => $import['imp_tags_count'],
        'IMPORT_ATTACHMENTS_COUNT' => $import['imp_attachments_count'],
        'FORM_ACTION' => cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter', 'a' => 'start', 'id' => $id]),
        'FORM_ACTION_DELETE' => cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter', 'a' => 'delete_selected', 'id' => $id]),
        'IMPORT_ALL_URL' => cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter', 'a' => 'import_all', 'id' => $id]),
        'DELETE_ALL_URL' => cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter', 'a' => 'delete_all', 'id' => $id]),
    ));
    
    cot_display_messages($t);
    
    $t->parse('MAIN');
    $pluginBody = $t->text('MAIN');
}
else if ($a == 'import_all' && $id > 0) {
    // Import all items without selection
    $import = wpi_get_import($id);
    
    if (!$import || $import['imp_status'] != 'new') {
        cot_redirect(cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter'], '', true));
    }
    
    // Create a selection array for all items
    $import_selection = [
        'categories' => 1,
        'tags' => 1,
        'posts' => 1,
        'pages' => 1,
        'attachments' => 1
    ];
    
    // Convert to JSON for storage
    $selection_json = json_encode($import_selection);
    
    // Start import process with all items selected
    wpi_update_import_status($id, 'processing', [], $selection_json);
    cot_message('wordpressimporter_import_all_started');
    cot_redirect(cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter', 'a' => 'import', 'id' => $id], '', true));
}
else if ($a == 'delete_all' && $id > 0) {
    // Delete all items related to this import
    $import = wpi_get_import($id);
    
    if (!$import) {
        cot_redirect(cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter'], '', true));
    }
    
    // First delete the import record and file
    if (wpi_delete_import($id)) {
        cot_message('wordpressimporter_delete_all_success');
    } else {
        cot_error('wordpressimporter_delete_error');
    }
    
    cot_redirect(cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter'], '', true));
}
else if ($a == 'delete_selected' && $id > 0 && $_SERVER['REQUEST_METHOD'] == 'POST') {
    // Delete selected items
    $import = wpi_get_import($id);
    
    if (!$import) {
        cot_redirect(cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter'], '', true));
    }
    
    // Get selected items to delete
    $delete_categories = (int)cot_import('import_categories', 'P', 'BOL');
    $delete_tags = (int)cot_import('import_tags', 'P', 'BOL');
    $delete_posts = (int)cot_import('import_posts', 'P', 'BOL');
    $delete_pages = (int)cot_import('import_pages', 'P', 'BOL');
    $delete_attachments = (int)cot_import('import_attachments', 'P', 'BOL');
    
    // Only delete the import record if all items are selected
    if ($delete_categories && $delete_tags && $delete_posts && $delete_pages && $delete_attachments) {
        if (wpi_delete_import($id)) {
            cot_message('wordpressimporter_delete_selected_success');
        } else {
            cot_error('wordpressimporter_delete_error');
        }
    } else {
        // Mark selected items for deletion in selection array
        $delete_selection = [
            'categories' => $delete_categories ? 0 : 1,  // Invert for keeping, not deleting
            'tags' => $delete_tags ? 0 : 1,
            'posts' => $delete_posts ? 0 : 1,
            'pages' => $delete_pages ? 0 : 1,
            'attachments' => $delete_attachments ? 0 : 1
        ];
        
        // Convert to JSON for storage
        $selection_json = json_encode($delete_selection);
        
        // Update import with items to keep
        wpi_update_import_status($id, 'new', [], $selection_json);
        cot_message('wordpressimporter_delete_selected_success');
    }
    
    cot_redirect(cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter'], '', true));
}
else if ($a == 'start' && $id > 0) {
    // Get import selection options
    $import_categories = (int)cot_import('import_categories', 'P', 'BOL');
    $import_tags = (int)cot_import('import_tags', 'P', 'BOL');
    $import_posts = (int)cot_import('import_posts', 'P', 'BOL');
    $import_pages = (int)cot_import('import_pages', 'P', 'BOL');
    $import_attachments = (int)cot_import('import_attachments', 'P', 'BOL');
    
    // Create a selection array to store in database
    $import_selection = [
        'categories' => $import_categories,
        'tags' => $import_tags,
        'posts' => $import_posts,
        'pages' => $import_pages,
        'attachments' => $import_attachments
    ];
    
    // Convert to JSON for storage
    $selection_json = json_encode($import_selection);
    
    // Start import process with selection options
    wpi_update_import_status($id, 'processing', [], $selection_json);
    cot_message('wordpressimporter_import_started');
    cot_redirect(cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter', 'a' => 'import', 'id' => $id], '', true));
}
else if ($a == 'import' && $id > 0) {
    // Import process page
    $import = wpi_get_import($id);
    
    if (!$import || $import['imp_status'] != 'processing') {
        cot_redirect(cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter'], '', true));
    }
    
    $t = new XTemplate(cot_tplfile('wordpressimporter.import', 'plug'));

    // Decode selection preferences
    $selection = json_decode($import['imp_selection'], true);
    if (!$selection) {
        $selection = [
            'categories' => 1,
            'tags' => 1,
            'posts' => 1,
            'pages' => 1,
            'attachments' => 0
        ];
    }

    // Calculate percentages
    $categories_percent = $import['imp_categories_count'] > 0 && $selection['categories'] ? 
        round(($import['imp_processed_categories'] / $import['imp_categories_count']) * 100) : 100;
    $tags_percent = $import['imp_tags_count'] > 0 && $selection['tags'] ? 
        round(($import['imp_processed_tags'] / $import['imp_tags_count']) * 100) : 100;
    $posts_percent = $import['imp_posts_count'] > 0 && $selection['posts'] ? 
        round(($import['imp_processed_posts'] / $import['imp_posts_count']) * 100) : 100;
    $pages_percent = $import['imp_pages_count'] > 0 && $selection['pages'] ? 
        round(($import['imp_processed_pages'] / $import['imp_pages_count']) * 100) : 100;
    $attachments_percent = $import['imp_attachments_count'] > 0 && $selection['attachments'] ? 
        round(($import['imp_processed_attachments'] / $import['imp_attachments_count']) * 100) : 100;
    
    // Calculate overall progress
    $total_items = 
        ($selection['categories'] ? $import['imp_categories_count'] : 0) +
        ($selection['tags'] ? $import['imp_tags_count'] : 0) +
        ($selection['posts'] ? $import['imp_posts_count'] : 0) +
        ($selection['pages'] ? $import['imp_pages_count'] : 0) +
        ($selection['attachments'] ? $import['imp_attachments_count'] : 0);
    
    $processed_items = 
        ($selection['categories'] ? $import['imp_processed_categories'] : 0) +
        ($selection['tags'] ? $import['imp_processed_tags'] : 0) +
        ($selection['posts'] ? $import['imp_processed_posts'] : 0) +
        ($selection['pages'] ? $import['imp_processed_pages'] : 0) +
        ($selection['attachments'] ? $import['imp_processed_attachments'] : 0);
    
    $overall_percent = $total_items > 0 ? round(($processed_items / $total_items) * 100) : 100;
    
    $t->assign(array(
        'IMPORT_ID' => $import['imp_id'],
        'IMPORT_TITLE' => $import['imp_title'],
        'IMPORT_FILE' => $import['imp_file'],
        'IMPORT_POSTS_COUNT' => $import['imp_posts_count'],
        'IMPORT_PAGES_COUNT' => $import['imp_pages_count'],
        'IMPORT_CATEGORIES_COUNT' => $import['imp_categories_count'],
        'IMPORT_TAGS_COUNT' => $import['imp_tags_count'],
        'IMPORT_ATTACHMENTS_COUNT' => $import['imp_attachments_count'],
        'IMPORT_PROCESSED_POSTS' => $import['imp_processed_posts'],
        'IMPORT_PROCESSED_PAGES' => $import['imp_processed_pages'],
        'IMPORT_PROCESSED_CATEGORIES' => $import['imp_processed_categories'],
        'IMPORT_PROCESSED_TAGS' => $import['imp_processed_tags'],
        'IMPORT_PROCESSED_ATTACHMENTS' => $import['imp_processed_attachments'],
        'IMPORT_CATEGORIES_PERCENT' => $categories_percent,
        'IMPORT_TAGS_PERCENT' => $tags_percent,
        'IMPORT_POSTS_PERCENT' => $posts_percent,
        'IMPORT_PAGES_PERCENT' => $pages_percent,
        'IMPORT_ATTACHMENTS_PERCENT' => $attachments_percent,
        'IMPORT_OVERALL_PERCENT' => $overall_percent,
        'IMPORT_AJAX_URL' => cot_url('plug', ['e' => 'wordpressimporter', 'a' => 'ajax_import', 'id' => $import['imp_id']]),
        'IMPORT_SELECTION_CATEGORIES' => $selection['categories'],
        'IMPORT_SELECTION_TAGS' => $selection['tags'],
        'IMPORT_SELECTION_POSTS' => $selection['posts'],
        'IMPORT_SELECTION_PAGES' => $selection['pages'],
        'IMPORT_SELECTION_ATTACHMENTS' => $selection['attachments'],
    ));
    
    cot_display_messages($t);
    
    $t->parse('MAIN');
    $pluginBody = $t->text('MAIN');
}
else {
    // Main admin page - list imports
    $t = new XTemplate(cot_tplfile('wordpressimporter.admin', 'plug'));
    
    // List existing imports
    $imports = $db->query("SELECT * FROM $db_wordpressimporter_imports ORDER BY imp_date DESC")->fetchAll();
    
    if (count($imports) > 0) {
        foreach ($imports as $import) {
            $t->assign(array(
                'IMPORT_ROW_ID' => $import['imp_id'],
                'IMPORT_ROW_TITLE' => $import['imp_title'],
                'IMPORT_ROW_DATE' => date('Y-m-d H:i', $import['imp_date']),
                'IMPORT_ROW_POSTS' => $import['imp_posts_count'],
                'IMPORT_ROW_PAGES' => $import['imp_pages_count'],
                'IMPORT_ROW_CATEGORIES' => $import['imp_categories_count'],
                'IMPORT_ROW_TAGS' => $import['imp_tags_count'],
                'IMPORT_ROW_ATTACHMENTS' => $import['imp_attachments_count'],
                'IMPORT_ROW_STATUS' => $L['wordpressimporter_status_' . $import['imp_status']],
                'IMPORT_ROW_SELECT_URL' => cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter', 'a' => 'select', 'id' => $import['imp_id']]),
                'IMPORT_ROW_START_URL' => cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter', 'a' => 'import_all', 'id' => $import['imp_id']]),
                'IMPORT_ROW_DELETE_ALL_URL' => cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter', 'a' => 'delete_all', 'id' => $import['imp_id']]),
                'IMPORT_ROW_CONTINUE_URL' => cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter', 'a' => 'import', 'id' => $import['imp_id']]),
                'IMPORT_ROW_DELETE_URL' => cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter', 'a' => 'delete', 'id' => $import['imp_id']])
            ));
            
            $t->parse('MAIN.IMPORT_ROW');
        }
    } else {
        $t->parse('MAIN.NO_IMPORTS');
    }
    
    // Upload form
    $max_size = 0;
    // PHP'nin upload_max_filesize ayarını al
    $upload_max = ini_get('upload_max_filesize');
    $val = trim($upload_max);
    $last = strtolower($val[strlen($val) - 1]);
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    $max_size = $val;
    
    $t->assign(array(
        'UPLOAD_FORM_ACTION' => cot_url('admin', ['m' => 'other', 'p' => 'wordpressimporter', 'a' => 'upload']),
        'UPLOAD_FORM_MAX_SIZE' => floor($max_size / 1024) . ' KB'
    ));
    
    cot_display_messages($t);
    
    $t->parse('MAIN');
    $pluginBody = $t->text('MAIN');
}