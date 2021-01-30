<?php
header ( 'content-type: application/json; charset=utf-8' );
$itemsID = array();
$itemsTypes = array();
$notificationsTypes = array();

$type = 'pending_chat';
$notification_message_type = erTranslationClassLhTranslation::getInstance()->getTranslation('chat/startchat','Pending Chat');

foreach ($Params['user_parameters_unordered']['id'] as $itemNotification) {
    $partsAlerts = explode('__',$itemNotification);
    $item = array_shift($partsAlerts);

    if (is_numeric($item)) {
        if (!in_array($item, $itemsID)) {
            $itemsID[] = $item;
            $itemsTypes[$item] = $type;
            $notificationsTypes[$item] = $partsAlerts;
        }
    } else {
        $type = $item;
    }    
}

$items = erLhcoreClassChat::getList(array(
    'ignore_fields' => erLhcoreClassChat::$chatListIgnoreField,
    'filterin' => array(
        'id' => $itemsID
    )
));

$returnArray = array();

foreach ($items as $item) {
    
    $nick = $item->nick;
    $department = (string)$item->department;
    $messageNotification = '';
    $forceShow = false;

    if ($itemsTypes[$item->id] == 'unread_chat') {
        $notification_message_type = erTranslationClassLhTranslation::getInstance()->getTranslation('chat/startchat','Unread message');
    } elseif ($itemsTypes[$item->id] == 'active_chats') {
        $notification_message_type = erTranslationClassLhTranslation::getInstance()->getTranslation('chat/startchat','Alert notification');

        $alertText = [];
        foreach ($notificationsTypes[$item->id] as $identifier) {
            $alert = erLhAbstractModelChatAlertIcon::findOne(array('filter' => array('identifier' => $identifier)));
            if ($alert instanceof erLhAbstractModelChatAlertIcon) {
                $alertText[] = $alert->name;
            }
        }

        $messageNotification = implode($alertText,"\n");

        $forceShow = true;
    } elseif ($itemsTypes[$item->id] == 'pending_chat') {
        $notification_message_type = erTranslationClassLhTranslation::getInstance()->getTranslation('chat/startchat','Pending Chat');
    } elseif ($itemsTypes[$item->id] == 'bot_chats') {
        $notification_message_type = erTranslationClassLhTranslation::getInstance()->getTranslation('chat/startchat','Bot Chat');
    } elseif ($itemsTypes[$item->id] == 'transfer_chat' || ($itemsTypes[$item->id] == 'transfer_chat_dep')) {
        
        if ($item->status == erLhcoreClassModelChat::STATUS_OPERATORS_CHAT) {
            $notification_message_type = erTranslationClassLhTranslation::getInstance()->getTranslation('chat/startchat','New message from operator');
            $nick = '';
            $department = '';
        } else {
            $notification_message_type = erTranslationClassLhTranslation::getInstance()->getTranslation('chat/startchat','Transfer Chat');
        }
        
    } elseif ($itemsTypes[$item->id] == 'active_chat') {
        $notification_message_type = erTranslationClassLhTranslation::getInstance()->getTranslation('chat/startchat','Assigned Chat');
    }
    
    $type = $itemsTypes[$item->id];
    
    if ($type == 'active_chat' || $type == 'bot_chats') {
        $type = 'pending_chat';
    } elseif ($type == 'transfer_chat_dep') {
        $type = 'transfer_chat';
    }

    $titleParts = array_filter(array($notification_message_type, $nick, $department));

    // do not show notification if i'm not chat owner and it's already belongs to other user
    if ($forceShow == false && $item->user_id > 0 && $type != 'transfer_chat' && $item->user_id != $currentUser->getUserID()) {
        continue;
    }

    if ($messageNotification == ''){
        $messageNotification = erLhcoreClassChat::getGetLastChatMessagePending($item->id);
    }

    $returnArray[] = array(
        'nick' => implode(' | ', $titleParts),
        'msg' => $messageNotification,
        'nt' => $item->nick,
        'last_id_identifier' => $type,
        'last_id' => $item->id
    );
}

echo erLhcoreClassChat::safe_json_encode($returnArray);

exit();
?>