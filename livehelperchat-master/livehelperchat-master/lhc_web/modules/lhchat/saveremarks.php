<?php
header('content-type: application/json; charset=utf-8');

if (!isset($_SERVER['HTTP_X_CSRFTOKEN']) || !$currentUser->validateCSFRToken($_SERVER['HTTP_X_CSRFTOKEN'])) {
	echo json_encode(array('error' => 'true', 'result' => 'Invalid CSRF Token' ));
	exit;
}

$definition = array(
		'data' => new ezcInputFormDefinitionElement(
				ezcInputFormDefinitionElement::OPTIONAL, 'unsafe_raw'
		)
);

$form = new ezcInputForm( INPUT_POST, $definition );

$Chat = erLhcoreClassModelChat::fetch($Params['user_parameters']['chat_id']);
$errorTpl = erLhcoreClassTemplate::getInstance( 'lhkernel/validation_error.tpl.php');

if ( erLhcoreClassChat::hasAccessToRead($Chat) )
{
	if ($form->hasInputField('data') && $form->hasValidData('data')) {
	    $errors = array();
        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.before_save_remarks',array('chat' => & $Chat, 'errors' => & $errors));

        if (empty($errors)) {

            $db = ezcDbInstance::get();

            $db->beginTransaction();

            $Chat->syncAndLock();
            $Chat->remarks = $form->data;
            $Chat->updateThis(array('update' => array('remarks')));

            $db->commit();
            
            echo json_encode(array('error' => 'false'));
            exit;
        } else {
            $errorTpl->set('errors', $errors);
            echo json_encode(array('error' => 'true', 'result' => $errorTpl->fetch()));
            exit;
        }
	} else {
        $errorTpl->set('errors', array(erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Form data not valid')));
        echo json_encode(array('error' => 'true', 'result' => $errorTpl->fetch()));
        exit;
    }
} else {
    $errorTpl->set('errors', array(erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Has no access to this chat')));
    echo json_encode(array('error' => 'true', 'result' => $errorTpl->fetch()));
    exit;
}
?>