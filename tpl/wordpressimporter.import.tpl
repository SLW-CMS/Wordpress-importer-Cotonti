<!-- BEGIN: MAIN -->
{REFRESH_META}
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
            <label>{PHP.L.wordpressimporter_categories}: {IMPORT_PROCESSED_CATEGORIES}/{IMPORT_CATEGORIES_COUNT} ({IMPORT_CATEGORIES_PERCENT}%)</label>
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
        <!-- IF {IMPORT_STATUS} == 'processing' -->
        <p>{PHP.L.wordpressimporter_please_wait}</p>
        <p class="processing-message">{IMPORT_STATUS_MESSAGE}</p>
        <p class="small">{PHP.L.wordpressimporter_page_refresh}</p>
        <!-- ENDIF -->
        
        <!-- IF {IMPORT_STATUS} == 'completed' -->
        <p class="success">{PHP.L.wordpressimporter_import_complete}</p>
        <!-- ENDIF -->
        
        <!-- IF {IMPORT_STATUS} == 'failed' -->
        <p class="error">{PHP.L.wordpressimporter_import_failed}</p>
        <p>{IMPORT_STATUS_MESSAGE}</p>
        <!-- ENDIF -->
    </div>
    
    <div class="import-actions">
        <!-- IF {IMPORT_STATUS} == 'processing' -->
        <a href="{CONTINUE_URL}" class="button">{PHP.L.Refresh}</a>
        <!-- ENDIF -->
        <a href="{BACK_URL}" class="button">
            <!-- IF {IMPORT_STATUS} == 'completed' -->
            {PHP.L.wordpressimporter_back_to_list}
            <!-- ELSE -->
            {PHP.L.Cancel}
            <!-- ENDIF -->
        </a>
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
.processing-message {
    font-weight: bold;
}
.success {
    color: #5cb85c;
    font-weight: bold;
}
.error {
    color: #d9534f;
    font-weight: bold;
}
</style>
<!-- END: MAIN -->
