<?php

$tpl = erLhcoreClassTemplate::getInstance( 'lhchatsettings/newstartsettings.tpl.php');

$startSettings = new erLhcoreClassModelChatStartSettings();

$data = (array)$startSettings->data_array;

if (isset($_POST['UpdateConfig']) || isset($_POST['SaveConfig']))
{
    if (!isset($_POST['csfr_token']) || !$currentUser->validateCSFRToken($_POST['csfr_token'])) {
        erLhcoreClassModule::redirect('chat/startchatformsettings');
        exit;
    }

    $Errors = erLhcoreClassAdminChatValidatorHelper::validateStartChatForm($data);

    if (!isset($_POST['DepartmentID']) || !is_numeric($_POST['DepartmentID'])) {
        $Errors[] = 'Please choose a department';
    } else {
        $startSettings->department_id = (int)$_POST['DepartmentID'];
    }

    if (!isset($_POST['name']) || empty($_POST['name'])) {
        $Errors[] = 'Please enter a name';
    } else {
        $startSettings->name = $_POST['name'];
    }

    if ( count($Errors) == 0 ) {

        $startSettings->data = serialize($data);
        $startSettings->saveThis();

        $tpl->set('updated',true);
      
        if ( isset($_POST['SaveConfig']) ) {
            erLhcoreClassModule::redirect('chatsettings/startsettingslist');
            exit;
        }

    } else {
        $tpl->set('errors',$Errors);
    }
}

$tpl->set('start_chat_data',$data);
$tpl->set('start_chat_item',$startSettings);

$Result['content'] = $tpl->fetch();
$Result['additional_footer_js'] = '<script src="'.erLhcoreClassDesign::designJS('js/angular.lhc.startchatformgenerator.js').'"></script>';
$Result['path'] = array(
    array(
        'url' => erLhcoreClassDesign::baseurl('system/configuration'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('department/new', 'System configuration')
    ),
    array(
        'url' => erLhcoreClassDesign::baseurl('chatsettings/startchatformsettingsindex'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhchatsettings/startchat', 'Start chat form settings')
    ),
    array(
        'url' => erLhcoreClassDesign::baseurl('chatsettings/startsettingslist'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhchatsettings/startchat', 'Start chat settings list')
    ),
    array(
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhchatsettings/startchat', 'New start chat settings')
    )
)
?>