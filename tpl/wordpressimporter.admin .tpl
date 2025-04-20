<!-- BEGIN: MAIN -->
{FILE "{PHP.cfg.themes_dir}/{PHP.cfg.defaulttheme}/warnings.tpl"}

<div class="block">
    <h2>{PHP.L.wordpressimporter_title}</h2>
    <p>{PHP.L.wordpressimporter_description}</p>
</div>

<!-- BEGIN: NO_IMPORTS -->
<div class="block">
    <p>{PHP.L.wordpressimporter_no_imports}</p>
</div>
<!-- END: NO_IMPORTS -->

<!-- BEGIN: IMPORT_ROW -->
<div class="block">
    <table class="cells">
        <tr>
            <td class="width20">{PHP.L.wordpressimporter_title_site}:</td>
            <td class="width80"><strong>{IMPORT_ROW_TITLE}</strong></td>
        </tr>
        <tr>
            <td>{PHP.L.wordpressimporter_date}:</td>
            <td>{IMPORT_ROW_DATE}</td>
        </tr>
        <tr>
            <td>{PHP.L.wordpressimporter_content}:</td>
            <td>
                {PHP.L.wordpressimporter_posts}: {IMPORT_ROW_POSTS} | 
                {PHP.L.wordpressimporter_pages}: {IMPORT_ROW_PAGES} | 
                {PHP.L.wordpressimporter_categories}: {IMPORT_ROW_CATEGORIES} | 
                {PHP.L.wordpressimporter_tags}: {IMPORT_ROW_TAGS} | 
                {PHP.L.wordpressimporter_attachments}: {IMPORT_ROW_ATTACHMENTS}
            </td>
        </tr>
        <tr>
            <td>{PHP.L.wordpressimporter_status}:</td>
            <td>{IMPORT_ROW_STATUS}</td>
        </tr>
        <tr>
            <td>{PHP.L.wordpressimporter_actions}:</td>
            <td>
                <!-- IF {IMPORT_ROW_STATUS} == 'new' -->
                <form action="{IMPORT_ROW_SELECT_URL}" method="post">
                    <input type="hidden" name="x" value="{PHP.sys.xk}" />
                    <div style="margin-bottom:10px;">
                        <a href="{IMPORT_ROW_START_URL}" class="button">{PHP.L.wordpressimporter_import_all}</a>
                        <input type="submit" name="action" value="import" class="button" /> 
                    </div>
                    <div class="options">
                        <div style="margin-bottom:5px;">
                            <label><input type="checkbox" name="import_categories" value="1" checked="checked"> {PHP.L.wordpressimporter_categories} ({IMPORT_ROW_CATEGORIES})</label>
                        </div>
                        <div style="margin-bottom:5px;">
                            <label><input type="checkbox" name="import_tags" value="1" checked="checked"> {PHP.L.wordpressimporter_tags} ({IMPORT_ROW_TAGS})</label>
                        </div>
                        <div style="margin-bottom:5px;">
                            <label><input type="checkbox" name="import_posts" value="1" checked="checked"> {PHP.L.wordpressimporter_posts} ({IMPORT_ROW_POSTS})</label>
                        </div>
                        <div style="margin-bottom:5px;">
                            <label><input type="checkbox" name="import_pages" value="1" checked="checked"> {PHP.L.wordpressimporter_pages} ({IMPORT_ROW_PAGES})</label>
                        </div>
                        <div style="margin-bottom:5px;">
                            <label><input type="checkbox" name="import_attachments" value="1"> {PHP.L.wordpressimporter_attachments} ({IMPORT_ROW_ATTACHMENTS})</label>
                        </div>
                    </div>
                </form>
                <!-- ENDIF -->
                
                <!-- IF {IMPORT_ROW_STATUS} == 'processing' -->
                <a href="{IMPORT_ROW_CONTINUE_URL}" class="button">{PHP.L.wordpressimporter_action_continue}</a>
                <!-- ENDIF -->
                
                <!-- IF {IMPORT_ROW_STATUS} == 'completed' -->
                <span class="success">{PHP.L.wordpressimporter_completed}</span>
                <!-- ENDIF -->
                
                <a href="{IMPORT_ROW_DELETE_URL}" class="button" onclick="return confirm('{PHP.L.wordpressimporter_confirm_delete}')">{PHP.L.Delete}</a>
            </td>
        </tr>
    </table>
</div>
<!-- END: IMPORT_ROW -->

<div class="block">
    <h3>{PHP.L.wordpressimporter_upload_title}</h3>
    <form action="{UPLOAD_FORM_ACTION}" method="post" enctype="multipart/form-data">
        <table class="cells">
            <tr>
                <td class="width20">{PHP.L.wordpressimporter_select_file}:</td>
                <td class="width80">
                    <input type="file" name="wordpress_xml" />
                    <p class="small">{PHP.L.wordpressimporter_max_size}: {UPLOAD_FORM_MAX_SIZE}</p>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="submit" class="button" value="{PHP.L.wordpressimporter_upload}" />
                </td>
            </tr>
        </table>
    </form>
</div>

<div class="block">
    <h3>{PHP.L.wordpressimporter_instructions_title}</h3>
    <ol>
        <li>{PHP.L.wordpressimporter_instruction_1}</li>
        <li>{PHP.L.wordpressimporter_instruction_2}</li>
        <li>{PHP.L.wordpressimporter_instruction_3}</li>
        <li>{PHP.L.wordpressimporter_instruction_4}</li>
        <li>{PHP.L.wordpressimporter_instruction_5}</li>
    </ol>
</div>
<!-- END: MAIN -->
