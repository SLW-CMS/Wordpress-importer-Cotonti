<?php
/* ====================
[BEGIN_COT_EXT]
Code=wordpressimporter
Name=WordPress Importer
Description=Advanced WordPress XML content importer with selective import capability
Version=1.0.0
Date=2025-04-20
Author=Rootali
Copyright=Rootali
Notes=Advanced WordPress XML content importer with selective import capability
Auth_guests=R
Lock_guests=W12345A
Auth_members=RW
Lock_members=12345
Requires_modules=page
[END_COT_EXT]

[BEGIN_COT_EXT_CONFIG]
upload_path=01:string:./datas/wordpressimport/:Path to store temporarily uploaded WordPress XML files
import_attachments=02:radio::1:Import media attachments
max_execution_time=03:string::300:Maximum execution time in seconds for import process
batch_size=04:string::10:Number of items to process in one batch
default_category=05:string:news:Default category if no matching category found
[END_COT_EXT_CONFIG]
==================== */

/**
 * WordPress Importer setup file
 *
 * @package WordPress Importer
 * @version 1.0.0
 * @author Rootali
 * @copyright Copyright (c) 2025 Rootali
 * @license BSD
 */

defined('COT_CODE') or die('Wrong URL');