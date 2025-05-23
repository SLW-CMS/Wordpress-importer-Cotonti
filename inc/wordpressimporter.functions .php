<?php
/**
 * WordPress Importer functions
 *
 * @package WordPress Importer
 * @version 1.0.0
 * @author Rootali
 * @copyright Copyright (c) 2025 Rootali
 * @license BSD
 */

defined('COT_CODE') or die('Wrong URL');

// Define tables
$db_wordpressimporter_imports = (isset($db_wordpressimporter_imports)) ? $db_wordpressimporter_imports : $db_x . 'wordpressimporter_imports';

/**
 * Log error message to file
 * 
 * @param string $error Error message
 * @return void
 */
function wpi_log_error($error)
{
    global $cfg;
    
    $log_path = $cfg['plugin']['wordpressimporter']['upload_path'] . 'import_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $message = "[$timestamp] $error\n";
    
    @file_put_contents($log_path, $message, FILE_APPEND);
}

/**
 * Check database connection and permissions
 * 
 * @return array Status information
 */
function wpi_check_db_access()
{
    global $db, $db_x;
    
    $result = array(
        'success' => false,
        'message' => '',
        'error' => ''
    );
    
    try {
        // Try to run a simple query
        $db->query("SELECT 1");
        
        // Check if we can write to structure table
        $can_write = $db->query("SHOW GRANTS FOR CURRENT_USER()")->fetchAll();
        $result['success'] = true;
        $result['message'] = 'Database access is successful.';
        $result['table_prefix'] = $db_x;
        
    } catch (Exception $e) {
        $result['error'] = 'Database error: ' . $e->getMessage();
        wpi_log_error($result['error']);
    }
    
    return $result;
}

/**
 * Create necessary database tables
 */
function wpi_create_tables()
{
    global $db, $db_wordpressimporter_imports;
    
    if (!$db->tableExists($db_wordpressimporter_imports)) {
        try {
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
                `imp_selection` text,
                PRIMARY KEY  (`imp_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
            
            return true;
        } catch (Exception $e) {
            wpi_log_error("Error creating import table: " . $e->getMessage());
            return false;
        }
    }
    
    return true;
}

/**
 * Create a random filename for an uploaded XML file
 * 
 * @return string Random filename
 */
function wpi_generate_filename()
{
    return md5(uniqid(rand(), true)) . '.xml';
}

/**
 * Upload a WordPress XML file to temporary directory
 * 
 * @param string $input_name Form input name for the file
 * @return array Status and information about uploaded file
 */
function wpi_upload_file($input_name)
{
    global $cfg;
    
    $result = array(
        'success' => false,
        'filename' => '',
        'error' => ''
    );
    
    // Check if file was uploaded
    if (!isset($_FILES[$input_name]) || $_FILES[$input_name]['error'] != UPLOAD_ERR_OK) {
        $result['error'] = 'error_upload';
        return $result;
    }
    
    // Check file type
    $file_info = pathinfo($_FILES[$input_name]['name']);
    $extension = strtolower($file_info['extension']);
    
    if ($extension != 'xml') {
        $result['error'] = 'error_type_not_xml';
        return $result;
    }
    
    // Create directory if it doesn't exist
    $upload_path = $cfg['plugin']['wordpressimporter']['upload_path'];
    if (!file_exists($upload_path)) {
        if (!mkdir($upload_path, 0755, true)) {
            $result['error'] = 'error_create_upload_dir';
            wpi_log_error("Failed to create upload directory: $upload_path");
            return $result;
        }
    }
    
    // Generate unique filename
    $filename = wpi_generate_filename();
    $filepath = $upload_path . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($_FILES[$input_name]['tmp_name'], $filepath)) {
        $result['success'] = true;
        $result['filename'] = $filename;
    } else {
        $result['error'] = 'error_move_uploaded';
        wpi_log_error("Failed to move uploaded file to: $filepath");
    }
    
    return $result;
}

/**
 * Parse WordPress XML file and return basic information
 * 
 * @param string $filename Filename of uploaded WordPress XML
 * @return array XML info or error
 */
function wpi_parse_wordpress_xml($filename)
{
    global $cfg;
    
    $result = array(
        'success' => false,
        'info' => array(),
        'error' => ''
    );
    
    $filepath = $cfg['plugin']['wordpressimporter']['upload_path'] . $filename;
    
    if (!file_exists($filepath)) {
        $result['error'] = 'error_file_not_found';
        wpi_log_error("XML file not found: $filepath");
        return $result;
    }
    
    try {
        // Try to load XML file
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($filepath);
        
        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            $error_msg = "XML Parse Errors: ";
            foreach ($errors as $error) {
                $error_msg .= "Line: {$error->line}, Col: {$error->column}, Message: {$error->message}\n";
            }
            wpi_log_error($error_msg);
            $result['error'] = 'error_xml_parse';
            return $result;
        }
        
        // Check if this is a WordPress export file
        if (!isset($xml->channel) || !isset($xml->channel->title)) {
            $result['error'] = 'error_not_wordpress_export';
            return $result;
        }
        
        // Set XML namespaces
        $namespaces = $xml->getNamespaces(true);
        $wp = $xml->channel->children($namespaces['wp'] ?? 'http://wordpress.org/export/1.2/');
        $content = $xml->channel->children($namespaces['content'] ?? 'http://purl.org/rss/1.0/modules/content/');
        
        // Count items
        $posts_count = 0;
        $pages_count = 0;
        $categories_count = 0;
        $tags_count = 0;
        $attachments_count = 0;
        
        if (isset($xml->channel->item)) {
            foreach ($xml->channel->item as $item) {
                $wp_item = $item->children($namespaces['wp'] ?? 'http://wordpress.org/export/1.2/');
                $post_type = isset($wp_item->post_type) ? (string)$wp_item->post_type : '';
                
                switch ($post_type) {
                    case 'post':
                        $posts_count++;
                        break;
                    case 'page':
                        $pages_count++;
                        break;
                    case 'attachment':
                        $attachments_count++;
                        break;
                }
            }
        }
        
        if (isset($wp->category)) {
            $categories_count = count($wp->category);
        }
        
        if (isset($wp->tag)) {
            $tags_count = count($wp->tag);
        }
        
        // Prepare info array
        $result['info'] = array(
            'title' => (string)$xml->channel->title,
            'link' => (string)$xml->channel->link,
            'description' => (string)$xml->channel->description,
            'posts_count' => $posts_count,
            'pages_count' => $pages_count,
            'categories_count' => $categories_count,
            'tags_count' => $tags_count,
            'attachments_count' => $attachments_count,
            'generator' => (string)$xml->channel->generator,
            'file' => $filename
        );
        
        $result['success'] = true;
        
    } catch (Exception $e) {
        $result['error'] = 'error_exception: ' . $e->getMessage();
        wpi_log_error("Exception parsing XML: " . $e->getMessage());
    }
    
    return $result;
}

/**
 * Create a new import record in database
 * 
 * @param array $info WordPress XML info
 * @return int Import ID
 */
function wpi_create_import($info)
{
    global $db, $db_wordpressimporter_imports, $sys;
    
    try {
        $import = array(
            'imp_file' => $info['file'],
            'imp_title' => $info['title'],
            'imp_date' => $sys['now'],
            'imp_posts_count' => $info['posts_count'],
            'imp_pages_count' => $info['pages_count'],
            'imp_categories_count' => $info['categories_count'],
            'imp_tags_count' => $info['tags_count'],
            'imp_attachments_count' => $info['attachments_count'],
            'imp_processed_posts' => 0,
            'imp_processed_pages' => 0,
            'imp_processed_categories' => 0,
            'imp_processed_tags' => 0,
            'imp_processed_attachments' => 0,
            'imp_status' => 'new',
            'imp_selection' => null
        );
        
        $db->insert($db_wordpressimporter_imports, $import);
        return $db->lastInsertId();
    } catch (Exception $e) {
        wpi_log_error("Error creating import record: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get import record by ID
 * 
 * @param int $import_id Import ID
 * @return array Import record or false
 */
function wpi_get_import($import_id)
{
    global $db, $db_wordpressimporter_imports;
    
    try {
        return $db->query("SELECT * FROM $db_wordpressimporter_imports WHERE imp_id = ?", $import_id)->fetch();
    } catch (Exception $e) {
        wpi_log_error("Error getting import record: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete import record and associated file
 * 
 * @param int $import_id Import ID
 * @return bool Success
 */
function wpi_delete_import($import_id)
{
    global $db, $db_wordpressimporter_imports, $cfg;
    
    $import = wpi_get_import($import_id);
    
    if (!$import) {
        return false;
    }
    
    try {
        // Delete file
        $filepath = $cfg['plugin']['wordpressimporter']['upload_path'] . $import['imp_file'];
        if (file_exists($filepath)) {
            @unlink($filepath);
        }
        
        // Delete import record
        return $db->delete($db_wordpressimporter_imports, "imp_id = ?", $import_id) > 0;
    } catch (Exception $e) {
        wpi_log_error("Error deleting import: " . $e->getMessage());
        return false;
    }
}

/**
 * Update import status
 * 
 * @param int $import_id Import ID
 * @param string $status New status
 * @param array $processed Optional array of processed items counts
 * @param string $selection_json Optional JSON string with selection options
 * @return bool Success
 */
function wpi_update_import_status($import_id, $status, $processed = array(), $selection_json = null)
{
    global $db, $db_wordpressimporter_imports;
    
    try {
        $update = array('imp_status' => $status);
        
        // Update processed items counts if provided
        if (isset($processed['posts'])) {
            $update['imp_processed_posts'] = (int)$processed['posts'];
        }
        
        if (isset($processed['pages'])) {
            $update['imp_processed_pages'] = (int)$processed['pages'];
        }
        
        if (isset($processed['categories'])) {
            $update['imp_processed_categories'] = (int)$processed['categories'];
        }
        
        if (isset($processed['tags'])) {
            $update['imp_processed_tags'] = (int)$processed['tags'];
        }
        
        if (isset($processed['attachments'])) {
            $update['imp_processed_attachments'] = (int)$processed['attachments'];
        }
        
        // Add selection JSON if provided
        if ($selection_json !== null) {
            $update['imp_selection'] = $selection_json;
        }
        
        return $db->update($db_wordpressimporter_imports, $update, "imp_id = ?", $import_id) > 0;
    } catch (Exception $e) {
        wpi_log_error("Error updating import status: " . $e->getMessage());
        return false;
    }
}

/**
 * Import WordPress categories into Cotonti structure
 * 
 * @param string $xml_path Path to WordPress XML file
 * @param int $offset Offset to start from
 * @param int $limit Maximum number of categories to import
 * @return array Result information
 */
function wpi_import_categories($xml_path, $offset = 0, $limit = 5)
{
    global $db, $db_structure;
    
    $result = array(
        'success' => true,
        'processed' => 0,
        'total' => 0,
        'message' => '',
        'error' => ''
    );
    
    try {
        // Load XML file
        $xml = simplexml_load_file($xml_path);
        if ($xml === false) {
            $result['success'] = false;
            $result['error'] = 'Error loading XML file';
            wpi_log_error("Error loading XML file: $xml_path");
            return $result;
        }
        
        $namespaces = $xml->getNamespaces(true);
        $wp = $xml->channel->children($namespaces['wp'] ?? 'http://wordpress.org/export/1.2/');
        
        // Get categories
        $categories = array();
        if (isset($wp->category)) {
            foreach ($wp->category as $category) {
                $categories[] = array(
                    'nicename' => (string)$category->category_nicename,
                    'name' => (string)$category->cat_name,
                    'parent' => (string)$category->category_parent
                );
            }
        }
        
        // Apply offset and limit
        $total_categories = count($categories);
        $result['total'] = $total_categories;
        $categories = array_slice($categories, $offset, $limit);
        
        // Initialize parent mapping
        $parent_map = array();
        
        // First pass - create all categories
        foreach ($categories as $category) {
            // Generate code for category (slug)
            $cat_code = cot_alphaonly($category['nicename']);
            if (empty($cat_code)) {
                $cat_code = cot_alphaonly($category['name']);
            }
            if (empty($cat_code)) {
                $cat_code = 'cat_' . rand(1000, 9999);
            }
            
            // Check if category already exists
            $exists = $db->query("SELECT COUNT(*) FROM $db_structure 
                               WHERE structure_code = ? AND structure_area = 'page'", 
                               array($cat_code))->fetchColumn();
            
            if (!$exists) {
                // Create category
                $cat_data = array(
                    'structure_area' => 'page',
                    'structure_code' => $cat_code,
                    'structure_path' => '',  // Will be updated in second pass
                    'structure_tpl' => '',
                    'structure_title' => $category['name'],
                    'structure_desc' => '',
                    'structure_icon' => '',
                    'structure_locked' => 0,
                    'structure_count' => 0
                );
                
                $db->insert($db_structure, $cat_data);
                
                // Store original nicename to code mapping for parent relationships
                $parent_map[$category['nicename']] = $cat_code;
            } else {
                // Store mapping even if category exists
                $parent_map[$category['nicename']] = $cat_code;
            }
            
            $result['processed']++;
        }
        
        // Second pass - update parent paths
        foreach ($categories as $category) {
            if (!empty($category['parent']) && isset($parent_map[$category['parent']])) {
                $cat_code = $parent_map[$category['nicename']];
                $parent_code = $parent_map[$category['parent']];
                
                // Get parent path
                $parent_path = $db->query("SELECT structure_path FROM $db_structure 
                                        WHERE structure_code = ? AND structure_area = 'page'", 
                                        array($parent_code))->fetchColumn();
                
                if ($parent_path) {
                    // Update current category path
                    $new_path = empty($parent_path) ? $cat_code : $parent_path . '.' . $cat_code;
                    
                    $db->update($db_structure, 
                              array('structure_path' => $new_path), 
                              "structure_code = ? AND structure_area = 'page'", 
                              array($cat_code));
                }
            } else {
                // No parent, so set the path to just the code
                $cat_code = $parent_map[$category['nicename']];
                $db->update($db_structure, 
                          array('structure_path' => $cat_code), 
                          "structure_code = ? AND structure_area = 'page'", 
                          array($cat_code));
            }
        }
        
        $result['message'] = $result['processed'] . ' categories imported successfully.';
        
    } catch (Exception $e) {
        $result['success'] = false;
        $result['error'] = 'Error importing categories: ' . $e->getMessage();
        wpi_log_error("Error importing categories: " . $e->getMessage());
    }
    
    return $result;
}

/**
 * Import WordPress tags into Cotonti tags
 * 
 * @param string $xml_path Path to WordPress XML file
 * @param int $offset Offset to start from
 * @param int $limit Maximum number of tags to import
 * @return array Result information
 */
function wpi_import_tags($xml_path, $offset = 0, $limit = 10)
{
    global $db, $db_tag;
    
    $result = array(
        'success' => true,
        'processed' => 0,
        'total' => 0,
        'message' => '',
        'error' => ''
    );
    
    try {
        // Load XML file
        $xml = simplexml_load_file($xml_path);
        if ($xml === false) {
            $result['success'] = false;
            $result['error'] = 'Error loading XML file';
            wpi_log_error("Error loading XML file: $xml_path");
            return $result;
        }
        
        $namespaces = $xml->getNamespaces(true);
        $wp = $xml->channel->children($namespaces['wp'] ?? 'http://wordpress.org/export/1.2/');
        
        // Get tags
        $tags = array();
        if (isset($wp->tag)) {
            foreach ($wp->tag as $tag) {
                $tags[] = array(
                    'nicename' => (string)$tag->tag_slug,
                    'name' => (string)$tag->tag_name
                );
            }
        }
        
        // Apply offset and limit
        $total_tags = count($tags);
        $result['total'] = $total_tags;
        $tags = array_slice($tags, $offset, $limit);
        
        // Insert tags into database
        foreach ($tags as $tag) {
            $tag_name = cot_tag_prep($tag['name']);
            
            if (empty($tag_name)) {
                continue;
            }
            
            try {
                // Cotonti tags tablosunun yapısını kontrol et - bazı versiyonlarda farklı olabilir
                // İlk olarak 'tag' sütunu olabilir
                try {
                    // Kontrol et - tag alanı varsa
                    $sql = "SHOW COLUMNS FROM $db_tag LIKE 'tag'";
                    $hasTagColumn = $db->query($sql)->rowCount() > 0;
                    
                    if ($hasTagColumn) {
                        // Check if tag already exists
                        $sql = "SELECT COUNT(*) FROM $db_tag WHERE tag = ?";
                        $exists = $db->query($sql, array($tag_name))->fetchColumn();
                        
                        if (!$exists) {
                            // Create tag
                            $sql = "INSERT INTO $db_tag (tag) VALUES (?)";
                            $db->query($sql, array($tag_name));
                        }
                    } else {
                        // tag_name alanını kontrol et
                        $sql = "SELECT COUNT(*) FROM $db_tag WHERE tag_name = ?";
                        $exists = $db->query($sql, array($tag_name))->fetchColumn();
                        
                        if (!$exists) {
                            // Create tag
                            $sql = "INSERT INTO $db_tag (tag_name, tag_count) VALUES (?, ?)";
                            $db->query($sql, array($tag_name, 0));
                        }
                    }
                    
                    $result['processed']++;
                } catch (Exception $columnEx) {
                    wpi_log_error("Tag table column check error: " . $columnEx->getMessage());
                    
                    // Alternatif yöntem - doğrudan deneme yap, tag_name kullan
                    $sql = "INSERT INTO $db_tag (tag_name, tag_count) VALUES (?, ?)";
                    $db->query($sql, array($tag_name, 0));
                    $result['processed']++;
                }
            } catch (Exception $tagEx) {
                wpi_log_error("Single tag import error for '$tag_name': " . $tagEx->getMessage());
                // Tek bir etiketi eklerken hata olduğunda, tüm import durmasın, sadece log at ve devam et
                continue;
            }
        }
        
        $result['message'] = $result['processed'] . ' tags imported successfully.';
        
    } catch (Exception $e) {
        $result['success'] = false;
        $result['error'] = 'Error importing tags: ' . $e->getMessage();
        wpi_log_error("Error importing tags: " . $e->getMessage());
    }
    
    return $result;
}

/**
 * Import WordPress posts into Cotonti pages
 * 
 * @param string $xml_path Path to WordPress XML file
 * @param int $offset Offset to start from
 * @param int $limit Maximum number of posts to import
 * @return array Result information
 */
function wpi_import_posts($xml_path, $offset = 0, $limit = 5)
{
    global $db, $db_pages, $db_structure, $db_tag, $db_tag_references, $cfg, $usr, $L;
    
    $result = array(
        'success' => true,
        'processed' => 0,
        'total' => 0,
        'message' => '',
        'error' => ''
    );
    
    try {
        // Load XML file
        $xml = simplexml_load_file($xml_path);
        if ($xml === false) {
            $result['success'] = false;
            $result['error'] = 'Error loading XML file';
            wpi_log_error("Error loading XML file: $xml_path");
            return $result;
        }
        
        $namespaces = $xml->getNamespaces(true);
        $wp = $xml->channel->children($namespaces['wp'] ?? 'http://wordpress.org/export/1.2/');
        $content = $xml->channel->children($namespaces['content'] ?? 'http://purl.org/rss/1.0/modules/content/');
        $excerpt = $xml->channel->children($namespaces['excerpt'] ?? 'http://wordpress.org/export/1.2/excerpt/');
        
        // Get all posts
        $posts = array();
        $total_posts = 0;
        
        if (isset($xml->channel->item)) {
            foreach ($xml->channel->item as $item) {
                $wp_item = $item->children($namespaces['wp'] ?? 'http://wordpress.org/export/1.2/');
                $post_type = isset($wp_item->post_type) ? (string)$wp_item->post_type : '';
                $status = isset($wp_item->status) ? (string)$wp_item->status : '';
                
                if ($post_type == 'post' && ($status == 'publish' || $status == 'draft')) {
                    $total_posts++;
                    
                    // Skip if not in current batch
                    if ($total_posts <= $offset || count($posts) >= $limit) {
                        continue;
                    }
                    
                    $content_encoded = '';
                    if (isset($item->children($namespaces['content'])->encoded)) {
                        $content_encoded = (string)$item->children($namespaces['content'])->encoded;
                    }
                    
                    $excerpt_encoded = '';
                    if (isset($item->children($namespaces['excerpt'])->encoded)) {
                        $excerpt_encoded = (string)$item->children($namespaces['excerpt'])->encoded;
                    }
                    
                    $post = array(
                        'title' => (string)$item->title,
                        'link' => (string)$item->link,
                        'pubDate' => (string)$item->pubDate,
                        'content' => $content_encoded,
                        'excerpt' => $excerpt_encoded,
                        'post_id' => (string)$wp_item->post_id,
                        'post_date' => (string)$wp_item->post_date,
                        'post_date_gmt' => (string)$wp_item->post_date_gmt,
                        'post_name' => (string)$wp_item->post_name,
                        'status' => $status,
                        'post_parent' => (string)$wp_item->post_parent,
                        'categories' => array(),
                        'tags' => array()
                    );
                    
                    // Get categories and tags
                    if (isset($item->category)) {
                        foreach ($item->category as $category) {
                            $domain = (string)$category['domain'];
                            
                            if ($domain == 'category') {
                                $post['categories'][] = array(
                                    'nicename' => (string)$category['nicename'],
                                    'name' => (string)$category
                                );
                            } elseif ($domain == 'post_tag') {
                                $post['tags'][] = (string)$category;
                            }
                        }
                    }
                    
                    $posts[] = $post;
                }
            }
        }
        
        $result['total'] = $total_posts;
        
        // Process each post
        foreach ($posts as $post) {
            // Convert post date to timestamp
            $post_date = strtotime($post['post_date_gmt']);
            if (!$post_date) {
                $post_date = time();
            }
            
            // Determine page category
            $cat_code = isset($cfg['plugin']['wordpressimporter']['default_category']) ? 
                $cfg['plugin']['wordpressimporter']['default_category'] : 'news';
            
            if (!empty($post['categories'])) {
                // Try to find matching category in Cotonti
                foreach ($post['categories'] as $category) {
                    $cat_nicename = $category['nicename'];
                    $cat_code_check = cot_alphaonly($cat_nicename);
                    
                    // Check if category exists in Cotonti
                    $exists = $db->query("SELECT COUNT(*) FROM $db_structure 
                                       WHERE structure_code = ? AND structure_area = 'page'", 
                                       array($cat_code_check))->fetchColumn();
                    
                    if ($exists) {
                        $cat_code = $cat_code_check;
                        break;
                    }
                }
            }
            
            // Create page data
            $page_data = array(
                'page_title' => $post['title'],
                'page_desc' => $post['excerpt'] ? strip_tags($post['excerpt']) : cot_string_truncate(strip_tags($post['content']), 200),
                'page_text' => $post['content'],
                'page_author' => $usr['name'],
                'page_ownerid' => $usr['id'],
                'page_date' => $post_date,
                'page_begin' => $post_date,
                'page_expire' => 0,
                'page_cat' => $cat_code,
                'page_alias' => $post['post_name'] ? $post['post_name'] : cot_makealias($post['title']),
                'page_state' => ($post['status'] == 'publish') ? 0 : 1 // 0 = published, 1 = draft
            );
            
            // Insert page
            $db->insert($db_pages, $page_data);
            $page_id = $db->lastInsertId();
            
            if ($page_id) {
                // Update category count
                $db->query("UPDATE $db_structure SET structure_count = structure_count + 1 
                          WHERE structure_code = ? AND structure_area = 'page'", 
                          array($cat_code));
                
                // Import tags
                if (!empty($post['tags'])) {
                    foreach ($post['tags'] as $tag) {
                        $tag_name = cot_tag_prep($tag);
                        
                        if (empty($tag_name)) {
                            continue;
                        }
                        
                        // Check if tag exists
                        $tag_id = $db->query("SELECT tag_id FROM $db_tag WHERE tag_name = ?", 
                                           array($tag_name))->fetchColumn();
                        
                        if (!$tag_id) {
                            // Create tag
                            $tag_data = array(
                                'tag_name' => $tag_name,
                                'tag_count' => 1
                            );
                            
                            $db->insert($db_tag, $tag_data);
                            $tag_id = $db->lastInsertId();
                        } else {
                            // Update tag count
                            $db->query("UPDATE $db_tag SET tag_count = tag_count + 1 
                                      WHERE tag_id = ?", array($tag_id));
                        }
                        
                        // Create tag reference
                        $tag_ref_data = array(
                            'tag' => $tag_name,
                            'tag_item' => $page_id,
                            'tag_area' => 'pages',
                            'tag_locale' => ''
                        );
                        
                        $db->insert($db_tag_references, $tag_ref_data);
                    }
                }
                
                $result['processed']++;
            }
        }
        
        $result['message'] = $result['processed'] . ' posts imported successfully.';
        
    } catch (Exception $e) {
        $result['success'] = false;
        $result['error'] = 'Error importing posts: ' . $e->getMessage();
        wpi_log_error("Error importing posts: " . $e->getMessage());
    }
    
    return $result;
}

/**
 * Import WordPress pages into Cotonti pages
 * 
 * @param string $xml_path Path to WordPress XML file
 * @param int $offset Offset to start from
 * @param int $limit Maximum number of pages to import
 * @return array Result information
 */
function wpi_import_pages($xml_path, $offset = 0, $limit = 5)
{
    global $db, $db_pages, $db_structure, $cfg, $usr;
    
    $result = array(
        'success' => true,
        'processed' => 0,
        'total' => 0,
        'message' => '',
        'error' => ''
    );
    
    try {
        // Load XML file
        $xml = simplexml_load_file($xml_path);
        if ($xml === false) {
            $result['success'] = false;
            $result['error'] = 'Error loading XML file';
            wpi_log_error("Error loading XML file: $xml_path");
            return $result;
        }
        
        $namespaces = $xml->getNamespaces(true);
        $wp = $xml->channel->children($namespaces['wp'] ?? 'http://wordpress.org/export/1.2/');
        $content = $xml->channel->children($namespaces['content'] ?? 'http://purl.org/rss/1.0/modules/content/');
        $excerpt = $xml->channel->children($namespaces['excerpt'] ?? 'http://wordpress.org/export/1.2/excerpt/');
        
        // Get all pages
        $pages = array();
        $total_pages = 0;
        
        if (isset($xml->channel->item)) {
            foreach ($xml->channel->item as $item) {
                $wp_item = $item->children($namespaces['wp'] ?? 'http://wordpress.org/export/1.2/');
                $post_type = isset($wp_item->post_type) ? (string)$wp_item->post_type : '';
                $status = isset($wp_item->status) ? (string)$wp_item->status : '';
                
                if ($post_type == 'page' && ($status == 'publish' || $status == 'draft')) {
                    $total_pages++;
                    
                    // Skip if not in current batch
                    if ($total_pages <= $offset || count($pages) >= $limit) {
                        continue;
                    }
                    
                    $content_encoded = '';
                    if (isset($item->children($namespaces['content'])->encoded)) {
                        $content_encoded = (string)$item->children($namespaces['content'])->encoded;
                    }
                    
                    $excerpt_encoded = '';
                    if (isset($item->children($namespaces['excerpt'])->encoded)) {
                        $excerpt_encoded = (string)$item->children($namespaces['excerpt'])->encoded;
                    }
                    
                    $page = array(
                        'title' => (string)$item->title,
                        'link' => (string)$item->link,
                        'pubDate' => (string)$item->pubDate,
                        'content' => $content_encoded,
                        'excerpt' => $excerpt_encoded,
                        'post_id' => (string)$wp_item->post_id,
                        'post_date' => (string)$wp_item->post_date,
                        'post_date_gmt' => (string)$wp_item->post_date_gmt,
                        'post_name' => (string)$wp_item->post_name,
                        'status' => $status,
                        'post_parent' => (string)$wp_item->post_parent,
                        'menu_order' => (string)$wp_item->menu_order
                    );
                    
                    $pages[] = $page;
                }
            }
        }
        
        $result['total'] = $total_pages;
        
        // Process each page
        foreach ($pages as $page) {
            // Convert page date to timestamp
            $page_date = strtotime($page['post_date_gmt']);
            if (!$page_date) {
                $page_date = time();
            }
            
            // Create page data
            $page_data = array(
                'page_title' => $page['title'],
                'page_desc' => $page['excerpt'] ? strip_tags($page['excerpt']) : cot_string_truncate(strip_tags($page['content']), 200),
                'page_text' => $page['content'],
                'page_author' => $usr['name'],
                'page_ownerid' => $usr['id'],
                'page_date' => $page_date,
                'page_begin' => $page_date,
                'page_expire' => 0,
                'page_cat' => 'system', // All WordPress pages go to system category in Cotonti
                'page_alias' => $page['post_name'] ? $page['post_name'] : cot_makealias($page['title']),
                'page_state' => ($page['status'] == 'publish') ? 0 : 1 // 0 = published, 1 = draft
            );
            
            // Insert page
            $db->insert($db_pages, $page_data);
            $page_id = $db->lastInsertId();
            
            if ($page_id) {
                // Update category count
                $db->query("UPDATE $db_structure SET structure_count = structure_count + 1 
                          WHERE structure_code = 'system' AND structure_area = 'page'");
                
                $result['processed']++;
            }
        }
        
        $result['message'] = $result['processed'] . ' pages imported successfully.';
        
    } catch (Exception $e) {
        $result['success'] = false;
        $result['error'] = 'Error importing pages: ' . $e->getMessage();
        wpi_log_error("Error importing pages: " . $e->getMessage());
    }
    
    return $result;
}

/**
 * Import WordPress attachments into Cotonti uploads
 * 
 * @param string $xml_path Path to WordPress XML file
 * @param int $offset Offset to start from
 * @param int $limit Maximum number of attachments to import
 * @return array Result information
 */
function wpi_import_attachments($xml_path, $offset = 0, $limit = 3)
{
    global $db, $cfg, $usr;
    
    $result = array(
        'success' => true,
        'processed' => 0,
        'total' => 0,
        'message' => '',
        'error' => ''
    );
    
    try {
        // Load XML file
        $xml = simplexml_load_file($xml_path);
        if ($xml === false) {
            $result['success'] = false;
            $result['error'] = 'Error loading XML file';
            wpi_log_error("Error loading XML file: $xml_path");
            return $result;
        }
        
        $namespaces = $xml->getNamespaces(true);
        $wp = $xml->channel->children($namespaces['wp'] ?? 'http://wordpress.org/export/1.2/');
        
        // Get all attachments
        $attachments = array();
        $total_attachments = 0;
        
        if (isset($xml->channel->item)) {
            foreach ($xml->channel->item as $item) {
                $wp_item = $item->children($namespaces['wp'] ?? 'http://wordpress.org/export/1.2/');
                $post_type = isset($wp_item->post_type) ? (string)$wp_item->post_type : '';
                
                if ($post_type == 'attachment') {
                    $total_attachments++;
                    
                    // Skip if not in current batch
                    if ($total_attachments <= $offset || count($attachments) >= $limit) {
                        continue;
                    }
                    
                    // Get attachment metadata
                    $attachment_url = '';
                    if (isset($wp_item->attachment_url)) {
                        $attachment_url = (string)$wp_item->attachment_url;
                    }
                    
                    if (!empty($attachment_url)) {
                        $attachments[] = array(
                            'title' => (string)$item->title,
                            'url' => $attachment_url,
                            'post_id' => (string)$wp_item->post_id,
                            'post_parent' => (string)$wp_item->post_parent
                        );
                    }
                }
            }
        }
        
        $result['total'] = $total_attachments;
        
        // Check if PFS module is installed and enabled
        $pfs_dir = $cfg['pfs_dir'] ?? null;
        
        if (empty($pfs_dir)) {
            // Fallback to custom upload directory
            $pfs_dir = $cfg['plugin']['wordpressimporter']['upload_path'] . 'files/';
            if (!file_exists($pfs_dir)) {
                mkdir($pfs_dir, 0755, true);
            }
        }
        
        // Process each attachment
        foreach ($attachments as $attachment) {
            // Generate unique filename
            $file_info = pathinfo($attachment['url']);
            $filename = wpi_unique_filename($pfs_dir, $file_info['basename']);
            
            // Download file
            $file_content = @file_get_contents($attachment['url']);
            
            if ($file_content !== false) {
                // Save file
                if (file_put_contents($pfs_dir . $filename, $file_content)) {
                    $result['processed']++;
                    
                    // If PFS module is active, create a PFS entry
                    if (isset($db_pfs) && cot_module_active('pfs')) {
                        global $db_pfs;
                        
                        $filesize = filesize($pfs_dir . $filename);
                        $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        
                        $pfs_entry = array(
                            'pfs_userid' => $usr['id'],
                            'pfs_date' => time(),
                            'pfs_file' => $filename,
                            'pfs_extension' => $file_extension,
                            'pfs_folderid' => 0,
                            'pfs_desc' => $attachment['title'],
                            'pfs_size' => $filesize,
                            'pfs_count' => 0
                        );
                        
                        $db->insert($db_pfs, $pfs_entry);
                    }
                }
            }
        }
        
        $result['message'] = $result['processed'] . ' attachments imported successfully.';
        
    } catch (Exception $e) {
        $result['success'] = false;
        $result['error'] = 'Error importing attachments: ' . $e->getMessage();
        wpi_log_error("Error importing attachments: " . $e->getMessage());
    }
    
    return $result;
}

/**
 * Generate a unique filename to avoid overwriting existing files
 * 
 * @param string $path Directory path
 * @param string $filename Original filename
 * @return string Unique filename
 */
function wpi_unique_filename($path, $filename)
{
    $file_info = pathinfo($filename);
    $filename_base = $file_info['filename'];
    $extension = isset($file_info['extension']) ? '.' . $file_info['extension'] : '';
    
    $i = 1;
    $new_filename = $filename;
    
    while (file_exists($path . $new_filename)) {
        $new_filename = $filename_base . '-' . $i . $extension;
        $i++;
    }
    
    return $new_filename;
}

/**
 * Prepare a tag name for storage
 * 
 * @param string $tag Original tag name
 * @return string Prepared tag name
 */
function cot_tag_prep($tag)
{
    $tag = mb_strtolower($tag);
    $tag = preg_replace('/[^\p{L}\p{N}\-_\s]/u', '', $tag);
    $tag = trim($tag);
    
    return $tag;
}
