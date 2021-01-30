<?php
$replaceArrayImages = array();
for ($i = 1; $i < 5; $i++) {
    $replaceArrayImages[] = array(
            'id' => "{proactive_img_{$i}}",
            'val' => $object->{"design_data_img_".$i."_url"},
    );
}
?>

<div class="row" ng-controller="ProactiveDesignerCtrl as prdesign" ng-init='prdesign.replaceArray = <?php echo json_encode($replaceArrayImages);?>'>

    <div class="col-12">
        <p>
            <small>
            <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('abstract/proactivechatinvitation','You can also use 5 images using keywords as {proactive_img_1}, {proactive_img_2}, {proactive_img_3}, {proactive_img_4}, {proactive_img_5}. You can use these events. These events should be used on onclick event.')?>
            <ul>
                <li>{readmessage} - Opens invitation window</li>
                <li>{hideInvitation} - Hides invitation</li>
            </ul>

            <?php include(erLhcoreClassDesign::designtpl('lhchat/getstatus/options_variable.tpl.php')); ?>

            <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('abstract/proactivechatinvitation', 'Main wrapper element should have id of');?>&nbsp;&quot;<?php echo $chatCSSPrefix?>-inv-container&quot;
            </small>
        </p>
    </div>

    <h5><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('abstract/proactivechatinvitation', 'New widget options');?></h5>
    <div class="col-12">
        <div class="form-group">
            <label><?php echo erLhcoreClassAbstract::renderInput('full_on_invitation', $fields['full_on_invitation'], $object)?> <?php echo $fields['full_on_invitation']['trans'];?></label>
        </div>
    </div>
    
    <div class="col-12">
        <div class="form-group">
            <label><?php echo erLhcoreClassAbstract::renderInput('close_above_msg', $fields['close_above_msg'], $object)?> <?php echo $fields['close_above_msg']['trans'];?></label>
        </div>
    </div>

    <div class="col-12">
        <div class="form-group">
            <label><?php echo erLhcoreClassAbstract::renderInput('photo_left_column', $fields['photo_left_column'], $object)?> <?php echo $fields['photo_left_column']['trans'];?></label>
        </div>
    </div>
    
    <div class="col-12">
        <div class="form-group">
            <label><?php echo erLhcoreClassAbstract::renderInput('hide_op_name', $fields['hide_op_name'], $object)?> <?php echo $fields['hide_op_name']['trans'];?></label>
        </div>
    </div>

    <div class="col-12">
        <div class="form-group">
            <label><?php echo erLhcoreClassAbstract::renderInput('std_header', $fields['std_header'], $object)?> <?php echo $fields['std_header']['trans'];?></label>
        </div>
    </div>

    <div class="col-12">
        <div class="form-group">
            <label><?php echo $fields['message_width']['trans'];?></label>
            <?php echo erLhcoreClassAbstract::renderInput('message_width', $fields['message_width'], $object)?>
        </div>
    </div>

    <h5><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('abstract/proactivechatinvitation', 'Old widget options');?></h5>
    <div class="col-12">
        <div class="form-group">
            <label><?php echo erLhcoreClassAbstract::renderInput('api_do_not_show', $fields['api_do_not_show'], $object)?> <?php echo $fields['api_do_not_show']['trans'];?></label>
        </div>
    </div>

    <div class="col-12">
        <div class="form-group">
            <label><?php echo erLhcoreClassAbstract::renderInput('mobile_html_only', $fields['mobile_html_only'], $object)?> <?php echo $fields['mobile_html_only']['trans'];?></label>
        </div>
    </div>

    <div class="col-6">
        <div class="form-group" >
            <div class="form-group">
                <label><?php echo $fields['mobile_html']['trans'];?></label>
                <?php echo erLhcoreClassAbstract::renderInput('mobile_html', $fields['mobile_html'], $object)?>
            </div>
        </div>
        <div class="form-group">
            <label><?php echo $fields['mobile_style']['trans'];?></label>
            <?php echo erLhcoreClassAbstract::renderInput('mobile_style', $fields['mobile_style'], $object)?>
        </div>
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label><?php echo $fields['design_data_img_1']['trans'];?></label>
                    <?php echo erLhcoreClassAbstract::renderInput('design_data_img_1', $fields['design_data_img_1'], $object)?>
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label><?php echo $fields['design_data_img_2']['trans'];?></label>
                    <?php echo erLhcoreClassAbstract::renderInput('design_data_img_2', $fields['design_data_img_2'], $object)?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label><?php echo $fields['design_data_img_3']['trans'];?></label>
                    <?php echo erLhcoreClassAbstract::renderInput('design_data_img_3', $fields['design_data_img_3'], $object)?>
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label><?php echo $fields['design_data_img_4']['trans'];?></label>
                    <?php echo erLhcoreClassAbstract::renderInput('design_data_img_4', $fields['design_data_img_4'], $object)?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label><?php echo $fields['design_data_img_5']['trans'];?></label>
                    <?php echo erLhcoreClassAbstract::renderInput('design_data_img_5', $fields['design_data_img_5'], $object)?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6">
        <style ng-bind-html="prdesign.plainStyle">

        </style>

        <div id="lhc-mobile-invitation" ng-bind-html="prdesign.plainHtml">
            
        </div>
    </div>
</div>