<!-- BEGIN: MAIN -->
{FILE "{PHP.cfg.themes_dir}/{PHP.cfg.defaulttheme}/warnings.tpl"}

<div class="block">
    <h2>{PHP.L.wordpressimporter_import_process}</h2>
    <p>{PHP.L.wordpressimporter_import_from}: <strong>{IMPORT_TITLE}</strong></p>
</div>

<div class="block">
    <h3>{PHP.L.wordpressimporter_import_progress}</h3>
    
    <div class="progress-container">
        <div class="progress-overall">
            <label>{PHP.L.wordpressimporter_overall_progress}: {IMPORT_OVERALL_PERCENT}%</label>
            <div class="progress-bar">
                <div class="progress-fill" style="width: {IMPORT_OVERALL_PERCENT}%;"></div>
            </div>
        </div>
        
        <!-- IF {IMPORT_SELECTION_CATEGORIES} -->
        <div class="progress-item">
            <label>{PHP.L.wordpressimporter_categories}dddd: {IMPORT_PROCESSED_CATEGORIES}/{IMPORT_CATEGORIES_COUNT} ({IMPORT_CATEGORIES_PERCENT}%)</label>
            <div class="progress-bar">
                <div class="progress-fill" style="width: {IMPORT_CATEGORIES_PERCENT}%;"></div>
            </div>
        </div>
        <!-- ENDIF -->
        
        <!-- IF {IMPORT_SELECTION_TAGS} -->
        <div class="progress-item">
            <label>{PHP.L.wordpressimporter_tags}: {IMPORT_PROCESSED_TAGS}/{IMPORT_TAGS_COUNT} ({IMPORT_TAGS_PERCENT}%)</label>
            <div class="progress-bar">
                <div class="progress-fill" style="width: {IMPORT_TAGS_PERCENT}%;"></div>
            </div>
        </div>
        <!-- ENDIF -->
        
        <!-- IF {IMPORT_SELECTION_POSTS} -->
        <div class="progress-item">
            <label>{PHP.L.wordpressimporter_posts}: {IMPORT_PROCESSED_POSTS}/{IMPORT_POSTS_COUNT} ({IMPORT_POSTS_PERCENT}%)</label>
            <div class="progress-bar">
                <div class="progress-fill" style="width: {IMPORT_POSTS_PERCENT}%;"></div>
            </div>
        </div>
        <!-- ENDIF -->
        
        <!-- IF {IMPORT_SELECTION_PAGES} -->
        <div class="progress-item">
            <label>{PHP.L.wordpressimporter_pages}: {IMPORT_PROCESSED_PAGES}/{IMPORT_PAGES_COUNT} ({IMPORT_PAGES_PERCENT}%)</label>
            <div class="progress-bar">
                <div class="progress-fill" style="width: {IMPORT_PAGES_PERCENT}%;"></div>
            </div>
        </div>
        <!-- ENDIF -->
        
        <!-- IF {IMPORT_SELECTION_ATTACHMENTS} -->
        <div class="progress-item">
            <label>{PHP.L.wordpressimporter_attachments}: {IMPORT_PROCESSED_ATTACHMENTS}/{IMPORT_ATTACHMENTS_COUNT} ({IMPORT_ATTACHMENTS_PERCENT}%)</label>
            <div class="progress-bar">
                <div class="progress-fill" style="width: {IMPORT_ATTACHMENTS_PERCENT}%;"></div>
            </div>
        </div>
        <!-- ENDIF -->
    </div>
    
    <div class="import-status">
        <p id="import-status-message">{PHP.L.wordpressimporter_initializing}</p>
        <p id="import-progress-message">{PHP.L.wordpressimporter_please_wait}</p>
    </div>
    
    <div class="import-actions">
        <a href="{PHP|cot_url('admin', 'm=other&p=wordpressimporter')}" class="button" id="cancel-button">{PHP.L.Cancel}</a>
    </div>
</div>

<style type="text/css">
.progress-container {
    margin: 20px 0;
}
.progress-item, .progress-overall {
    margin-bottom: 10px;
}
.progress-bar {
    background-color: #f3f3f3;
    border: 1px solid #ddd;
    border-radius: 3px;
    height: 20px;
    width: 100%;
    position: relative;
    margin-top: 5px;
}
.progress-fill {
    background-color: #5cb85c;
    height: 100%;
    border-radius: 2px;
    transition: width 0.3s ease;
}
.import-status {
    margin: 20px 0;
    padding: 10px;
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 3px;
}
.import-actions {
    margin-top: 20px;
}
</style>

<script type="text/javascript">
$(document).ready(function() {
    var importId = {IMPORT_ID};
    var isComplete = false;
    var ajaxUrl = '{IMPORT_AJAX_URL}';
    var statusMessage = $('#import-status-message');
    var progressMessage = $('#import-progress-message');
    var cancelButton = $('#cancel-button');
    
    // Disable cancel button during import
    cancelButton.hide();
    
    // Function to update progress bars and text
    function updateProgress(data) {
        if (data.success) {
            // Update overall progress
            $('.progress-overall .progress-fill').css('width', data.percent + '%');
            $('.progress-overall label').text('{PHP.L.wordpressimporter_overall_progress}: ' + data.percent + '%');
            
            // Update individual progress bars
            if (data.processed_categories > 0) {
                var catPercent = Math.round((data.processed_categories / {IMPORT_CATEGORIES_COUNT}) * 100);
                $('.progress-item:eq(0) .progress-fill').css('width', catPercent + '%');
                $('.progress-item:eq(0) label').text('{PHP.L.wordpressimporter_categories}: ' + data.processed_categories + '/{IMPORT_CATEGORIES_COUNT} (' + catPercent + '%)');
            }
            
            if (data.processed_tags > 0) {
                var tagPercent = Math.round((data.processed_tags / {IMPORT_TAGS_COUNT}) * 100);
                $('.progress-item:eq(1) .progress-fill').css('width', tagPercent + '%');
                $('.progress-item:eq(1) label').text('{PHP.L.wordpressimporter_tags}: ' + data.processed_tags + '/{IMPORT_TAGS_COUNT} (' + tagPercent + '%)');
            }
            
            if (data.processed_posts > 0) {
                var postPercent = Math.round((data.processed_posts / {IMPORT_POSTS_COUNT}) * 100);
                $('.progress-item:eq(2) .progress-fill').css('width', postPercent + '%');
                $('.progress-item:eq(2) label').text('{PHP.L.wordpressimporter_posts}: ' + data.processed_posts + '/{IMPORT_POSTS_COUNT} (' + postPercent + '%)');
            }
            
            if (data.processed_pages > 0) {
                var pagePercent = Math.round((data.processed_pages / {IMPORT_PAGES_COUNT}) * 100);
                $('.progress-item:eq(3) .progress-fill').css('width', pagePercent + '%');
                $('.progress-item:eq(3) label').text('{PHP.L.wordpressimporter_pages}: ' + data.processed_pages + '/{IMPORT_PAGES_COUNT} (' + pagePercent + '%)');
            }
            
            if (data.processed_attachments > 0) {
                var attPercent = Math.round((data.processed_attachments / {IMPORT_ATTACHMENTS_COUNT}) * 100);
                $('.progress-item:eq(4) .progress-fill').css('width', attPercent + '%');
                $('.progress-item:eq(4) label').text('{PHP.L.wordpressimporter_attachments}: ' + data.processed_attachments + '/{IMPORT_ATTACHMENTS_COUNT} (' + attPercent + '%)');
            }
            
            statusMessage.text(data.message);
            
            if (data.complete) {
                isComplete = true;
                progressMessage.text('{PHP.L.wordpressimporter_import_complete}');
                cancelButton.text('{PHP.L.wordpressimporter_back_to_list}').show();
            }
        } else {
            statusMessage.text('{PHP.L.Error}: ' + data.error);
            progressMessage.text('{PHP.L.wordpressimporter_import_failed}');
            isComplete = true;
            cancelButton.show();
        }
    }
    
    // Function to do the AJAX request
    function processImport() {
        if (isComplete) {
            return;
        }
        
        $.ajax({
            url: ajaxUrl,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                updateProgress(data);
                
                if (!data.complete) {
                    // Continue processing with a small delay
                    setTimeout(processImport, 1000);
                }
            },
            error: function(xhr, status, error) {
                statusMessage.text('{PHP.L.Error}: ' + status + ' - ' + error);
                progressMessage.text('{PHP.L.wordpressimporter_import_failed}');
                isComplete = true;
                cancelButton.show();
            }
        });
    }
    
    // Start processing
    processImport();
});
</script>
<!-- END: MAIN -->