<!-- BEGIN: MAIN -->
{FILE "{PHP.cfg.themes_dir}/{PHP.cfg.defaulttheme}/warnings.tpl"}

<div class="block">
    <h2>{PHP.L.wordpressimporter_select_items}</h2>
    <p>{PHP.L.wordpressimporter_select_description}</p>
</div>

<div class="block">
    <h3>{PHP.L.wordpressimporter_import_content_from}: <strong>{IMPORT_TITLE}</strong></h3>
    
    <form action="{FORM_ACTION}" method="post">
        <table class="cells">
            <tr>
                <td class="width30">{PHP.L.wordpressimporter_categories} ({IMPORT_CATEGORIES_COUNT}):</td>
                <td class="width70">
                    <label>
                        <input type="checkbox" name="import_categories" value="1" checked="checked"> 
                        {PHP.L.wordpressimporter_import_categories}
                    </label>
                </td>
            </tr>
            <tr>
                <td>{PHP.L.wordpressimporter_tags} ({IMPORT_TAGS_COUNT}):</td>
                <td>
                    <label>
                        <input type="checkbox" name="import_tags" value="1" checked="checked"> 
                        {PHP.L.wordpressimporter_import_tags}
                    </label>
                </td>
            </tr>
            <tr>
                <td>{PHP.L.wordpressimporter_posts} ({IMPORT_POSTS_COUNT}):</td>
                <td>
                    <label>
                        <input type="checkbox" name="import_posts" value="1" checked="checked"> 
                        {PHP.L.wordpressimporter_import_posts}
                    </label>
                </td>
            </tr>
            <tr>
                <td>{PHP.L.wordpressimporter_pages} ({IMPORT_PAGES_COUNT}):</td>
                <td>
                    <label>
                        <input type="checkbox" name="import_pages" value="1" checked="checked"> 
                        {PHP.L.wordpressimporter_import_pages}
                    </label>
                </td>
            </tr>
            <tr>
                <td>{PHP.L.wordpressimporter_attachments} ({IMPORT_ATTACHMENTS_COUNT}):</td>
                <td>
                    <label>
                        <input type="checkbox" name="import_attachments" value="1"> 
                        {PHP.L.wordpressimporter_import_attachments}
                    </label>
                    <p class="small">{PHP.L.wordpressimporter_attachments_warning}</p>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="submit" class="button" value="{PHP.L.wordpressimporter_start_import}">
                    <a href="{PHP|cot_url('admin', 'm=other&p=wordpressimporter')}" class="button">{PHP.L.Cancel}</a>
                </td>
            </tr>
          <tr>
            <td></td>
            <td>
                <input type="submit" name="import_selected" class="button" value="{PHP.L.wordpressimporter_import_selected}">
                <input type="submit" name="delete_selected" class="button" formaction="{FORM_ACTION_DELETE}" value="{PHP.L.wordpressimporter_delete_selected}" onclick="return confirm('{PHP.L.wordpressimporter_confirm_delete}')">
                <a href="{IMPORT_ALL_URL}" class="button">{PHP.L.wordpressimporter_import_all}</a>
                <a href="{DELETE_ALL_URL}" class="button" onclick="return confirm('{PHP.L.wordpressimporter_confirm_delete}')">{PHP.L.wordpressimporter_delete_all}</a>
                <a href="{PHP|cot_url('admin', 'm=other&p=wordpressimporter')}" class="button">{PHP.L.Cancel}</a>
            </td>
        </tr>
        </table>
    </form>
</div>
<!-- END: MAIN -->