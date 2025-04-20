<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=ajax
[END_COT_EXT]
==================== */

/**
 * WordPress Importer AJAX processor
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

// Set execution time limit if configured
if (isset($cfg['plugin']['wordpressimporter']['max_execution_time'])) {
    $max_time = (int)$cfg['plugin']['wordpressimporter']['max_execution_time'];
    if ($max_time > 0) {
        @set_time_limit($max_time);
    }
}

// Set batch size 
$batch_size = (int)$cfg['plugin']['wordpressimporter']['batch_size'];
if ($batch_size <= 0) {
    $batch_size = 10;
}

// Get import record
$id = cot_import('id', 'G', 'INT');
$a = cot_import('a', 'G', 'ALP');

if ($a == 'ajax_import' && $id > 0) {
    // Process AJAX import request
    $response = array(
        'success' => false,
        'message' => '',
        'error' => '',
        'complete' => false,
        'percent' => 0,
        'processed_categories' => 0,
        'processed_tags' => 0,
        'processed_posts' => 0,
        'processed_pages' => 0,
        'processed_attachments' => 0
    );
    
    // Get import record
    $import = wpi_get_import($id);
    
    if (!$import || $import['imp_status'] != 'processing') {
        $response['error'] = 'Invalid import or wrong status.';
        echo json_encode($response);
        exit;
    }
    
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
    
    // Get full file path
    $filepath = $cfg['plugin']['wordpressimporter']['upload_path'] . $import['imp_file'];
    
    // Check which content type to process next
    if ($selection['categories'] && $import['imp_processed_categories'] < $import['imp_categories_count']) {
        // Process categories
        $result = wpi_import_categories($filepath, $import['imp_processed_categories'], $batch_size);
        
        if ($result['success']) {
            // Update processed count
            $processed = array(
                'categories' => $import['imp_processed_categories'] + $result['processed']
            );
            
            wpi_update_import_status($id, 'processing', $processed);
            
            $response['success'] = true;
            $response['message'] = 'Processed ' . $result['processed'] . ' categories.';
            $response['processed_categories'] = $import['imp_processed_categories'] + $result['processed'];
        } else {
            $response['error'] = $result['error'];
        }
    } 
    else if ($selection['tags'] && $import['imp_processed_tags'] < $import['imp_tags_count']) {
        // Process tags
        $result = wpi_import_tags($filepath, $import['imp_processed_tags'], $batch_size);
        
        if ($result['success']) {
            // Update processed count
            $processed = array(
                'tags' => $import['imp_processed_tags'] + $result['processed']
            );
            
            wpi_update_import_status($id, 'processing', $processed);
            
            $response['success'] = true;
            $response['message'] = 'Processed ' . $result['processed'] . ' tags.';
            $response['processed_tags'] = $import['imp_processed_tags'] + $result['processed'];
        } else {
            $response['error'] = $result['error'];
        }
    } 
    else if ($selection['posts'] && $import['imp_processed_posts'] < $import['imp_posts_count']) {
        // Process posts
        $result = wpi_import_posts($filepath, $import['imp_processed_posts'], $batch_size);
        
        if ($result['success']) {
            // Update processed count
            $processed = array(
                'posts' => $import['imp_processed_posts'] + $result['processed']
            );
            
            wpi_update_import_status($id, 'processing', $processed);
            
            $response['success'] = true;
            $response['message'] = 'Processed ' . $result['processed'] . ' posts.';
            $response['processed_posts'] = $import['imp_processed_posts'] + $result['processed'];
        } else {
            $response['error'] = $result['error'];
        }
    } 
    else if ($selection['pages'] && $import['imp_processed_pages'] < $import['imp_pages_count']) {
        // Process pages
        $result = wpi_import_pages($filepath, $import['imp_processed_pages'], $batch_size);
        
        if ($result['success']) {
            // Update processed count
            $processed = array(
                'pages' => $import['imp_processed_pages'] + $result['processed']
            );
            
            wpi_update_import_status($id, 'processing', $processed);
            
            $response['success'] = true;
            $response['message'] = 'Processed ' . $result['processed'] . ' pages.';
            $response['processed_pages'] = $import['imp_processed_pages'] + $result['processed'];
        } else {
            $response['error'] = $result['error'];
        }
    } 
    else if ($selection['attachments'] && $import['imp_processed_attachments'] < $import['imp_attachments_count']) {
        // Process attachments
        $result = wpi_import_attachments($filepath, $import['imp_processed_attachments'], $batch_size);
        
        if ($result['success']) {
            // Update processed count
            $processed = array(
                'attachments' => $import['imp_processed_attachments'] + $result['processed']
            );
            
            wpi_update_import_status($id, 'processing', $processed);
            
            $response['success'] = true;
            $response['message'] = 'Processed ' . $result['processed'] . ' attachments.';
            $response['processed_attachments'] = $import['imp_processed_attachments'] + $result['processed'];
        } else {
            $response['error'] = $result['error'];
        }
    } 
    else {
        // All content types are complete
        wpi_update_import_status($id, 'completed');
        
        $response['success'] = true;
        $response['complete'] = true;
        $response['message'] = 'Import completed successfully.';
    }
    
    // Get updated import record
    $import = wpi_get_import($id);
    
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
    
    $response['percent'] = $total_items > 0 ? round(($processed_items / $total_items) * 100) : 100;
    $response['processed_categories'] = $import['imp_processed_categories'];
    $response['processed_tags'] = $import['imp_processed_tags'];
    $response['processed_posts'] = $import['imp_processed_posts'];
    $response['processed_pages'] = $import['imp_processed_pages'];
    $response['processed_attachments'] = $import['imp_processed_attachments'];
    
    // Return JSON response
    echo json_encode($response);
    exit;
}

// Invalid request
header('HTTP/1.1 400 Bad Request');
echo 'Invalid request';
exit;