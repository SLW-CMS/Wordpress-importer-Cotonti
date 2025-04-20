<?php
/**
 * WordPress Importer English language file
 *
 * @package WordPress Importer
 * @version 1.0.0
 * @author Rootali
 * @copyright Copyright (c) 2025 Rootali
 * @license BSD
 */

defined('COT_CODE') or die('Wrong URL.');

$L['info_desc'] = 'Advanced WordPress XML content importer with selective import capability';

$L['wordpressimporter_title'] = 'WordPress Importer';
$L['wordpressimporter_description'] = 'Import content from WordPress XML export files to Cotonti with selective import capability';
$L['wordpressimporter_help'] = 'This tool allows you to import content from WordPress XML export files into your Cotonti site. You can select which content types to import.';

// Admin interface
$L['wordpressimporter_upload_title'] = 'Upload WordPress XML Export File';
$L['wordpressimporter_select_file'] = 'Select XML File';
$L['wordpressimporter_upload'] = 'Upload';
$L['wordpressimporter_max_size'] = 'Maximum file size';
$L['wordpressimporter_no_imports'] = 'No import records found. Please upload a WordPress XML export file to begin.';
$L['wordpressimporter_title_site'] = 'Site Title';
$L['wordpressimporter_date'] = 'Import Date';
$L['wordpressimporter_content'] = 'Content';
$L['wordpressimporter_status'] = 'Status';
$L['wordpressimporter_actions'] = 'Actions';

// Content types
$L['wordpressimporter_posts'] = 'Posts';
$L['wordpressimporter_pages'] = 'Pages';
$L['wordpressimporter_categories'] = 'Categories';
$L['wordpressimporter_tags'] = 'Tags';
$L['wordpressimporter_attachments'] = 'Attachments';

// Status labels
$L['wordpressimporter_status_new'] = 'New';
$L['wordpressimporter_status_processing'] = 'Processing';
$L['wordpressimporter_status_completed'] = 'Completed';
$L['wordpressimporter_status_failed'] = 'Failed';

// Actions
$L['wordpressimporter_select_and_import'] = 'Select & Import';
$L['wordpressimporter_action_continue'] = 'Continue Import';
$L['wordpressimporter_completed'] = 'Import Completed';
$L['wordpressimporter_confirm_delete'] = 'Are you sure you want to delete this import record? This action cannot be undone.';

// Import process
$L['wordpressimporter_import_process'] = 'WordPress Import Process';
$L['wordpressimporter_import_from'] = 'Importing from';
$L['wordpressimporter_import_progress'] = 'Import Progress';
$L['wordpressimporter_initializing'] = 'Initializing import process...';
$L['wordpressimporter_please_wait'] = 'Please wait while content is being imported...';
$L['wordpressimporter_overall_progress'] = 'Overall Progress';
$L['wordpressimporter_import_complete'] = 'Import completed successfully!';
$L['wordpressimporter_import_failed'] = 'Import process failed. Please check the error message.';
$L['wordpressimporter_back_to_list'] = 'Back to Import List';

// Selection screen
$L['wordpressimporter_select_items'] = 'Select Items to Import';
$L['wordpressimporter_select_description'] = 'Choose which items from the WordPress export file you would like to import.';
$L['wordpressimporter_import_content_from'] = 'Import content from';
$L['wordpressimporter_import_categories'] = 'Import Categories';
$L['wordpressimporter_import_tags'] = 'Import Tags';
$L['wordpressimporter_import_posts'] = 'Import Posts';
$L['wordpressimporter_import_pages'] = 'Import Pages';
$L['wordpressimporter_import_attachments'] = 'Import Attachments';
$L['wordpressimporter_attachments_warning'] = 'Note: Importing attachments may take a long time and use significant bandwidth.';
$L['wordpressimporter_start_import'] = 'Start Import';

// Messages
$L['wordpressimporter_uploaded_success'] = 'WordPress XML file uploaded and analyzed successfully.';
$L['wordpressimporter_delete_success'] = 'Import record deleted successfully.';
$L['wordpressimporter_delete_error'] = 'Error deleting import record.';
$L['wordpressimporter_db_error'] = 'Database error occurred while creating import record.';

// Error messages
$L['wordpressimporter_error_upload'] = 'Error uploading file. Please try again.';
$L['wordpressimporter_error_type_not_xml'] = 'The uploaded file is not an XML file. Please upload a valid WordPress XML export file.';
$L['wordpressimporter_error_move_uploaded'] = 'Error moving uploaded file. Please check permissions and try again.';
$L['wordpressimporter_error_file_not_found'] = 'XML file not found. Please re-upload.';
$L['wordpressimporter_error_xml_parse'] = 'Error parsing XML file. The file may be corrupted or not a valid WordPress export file.';
$L['wordpressimporter_error_not_wordpress_export'] = 'The uploaded file is not a valid WordPress export file.';

// Instructions
$L['wordpressimporter_instructions_title'] = 'How to use this importer';
$L['wordpressimporter_instruction_1'] = 'In your WordPress admin panel, go to Tools > Export to create an XML export file.';
$L['wordpressimporter_instruction_2'] = 'Select what content you want to export (All content, Posts, Pages, etc.).';
$L['wordpressimporter_instruction_3'] = 'Download the export file to your computer.';
$L['wordpressimporter_instruction_4'] = 'Upload the XML file using the form above.';
$L['wordpressimporter_instruction_5'] = 'After analysis, select which items to import and start the import process.';

// Configuration
$L['cfg_upload_path'] = 'Upload path for WordPress XML files';
$L['cfg_upload_path_hint'] = 'Path to store temporarily uploaded WordPress XML files';
$L['cfg_import_attachments'] = 'Import media attachments';
$L['cfg_import_attachments_hint'] = 'Allow importing media attachments from WordPress';
$L['cfg_max_execution_time'] = 'Maximum execution time';
$L['cfg_max_execution_time_hint'] = 'Maximum execution time in seconds for each import step (0 for no limit)';
$L['cfg_batch_size'] = 'Batch size';
$L['cfg_batch_size_hint'] = 'Number of items to process in one batch';
$L['cfg_default_category'] = 'Default category';
$L['cfg_default_category_hint'] = 'Default category if no matching category found';

// Actions for import/delete
$L['wordpressimporter_import_all'] = 'Import All';
$L['wordpressimporter_delete_all'] = 'Delete All';
$L['wordpressimporter_import_selected'] = 'Import Selected';
$L['wordpressimporter_delete_selected'] = 'Delete Selected';
$L['wordpressimporter_select_and_import'] = 'Select & Import';
$L['wordpressimporter_import_started'] = 'Import process started with selected items.';
$L['wordpressimporter_import_all_started'] = 'Import process started with all items.';
$L['wordpressimporter_delete_all_success'] = 'All import data has been deleted successfully.';
$L['wordpressimporter_delete_selected_success'] = 'Selected import data has been deleted successfully.';
