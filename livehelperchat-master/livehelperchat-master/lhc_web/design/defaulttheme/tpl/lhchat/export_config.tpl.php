<?php
$modalHeaderClass = 'pt-1 pb-1 pl-2 pr-2';
$modalHeaderTitle = erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Export XLS');
$modalSize = 'md';
$modalBodyClass = 'p-1'
?>
<?php include(erLhcoreClassDesign::designtpl('lhkernel/modal_header.tpl.php'));?>

<form action="<?php echo htmlspecialchars($action_url)?>/(export)/1" method="post" target="_blank">
    <div class="modal-body">
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label><input type="checkbox" name="exportOptions[]" value="2"> <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Include content')?></label>
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label><input type="checkbox" name="exportOptions[]" value="3"> <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Include survey')?></label>
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label><input type="checkbox" name="exportOptions[]" value="4"> <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Include messages statistic')?></label>
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label><input type="checkbox" name="exportOptions[]" value="5"> <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Include subject')?></label>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="export_action" value="doExport">
    <div class="modal-footer">
        <button type="submit" class="btn btn-primary btn-sm"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/lists/search_panel','Export')?></button>
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/buttons','Close')?></button>
    </div>
</form>

<?php include(erLhcoreClassDesign::designtpl('lhkernel/modal_footer.tpl.php'));?>