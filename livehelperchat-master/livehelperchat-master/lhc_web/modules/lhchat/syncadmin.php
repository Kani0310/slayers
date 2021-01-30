<?php

header ( 'content-type: application/json; charset=utf-8' );

$content = 'false';
$content_status = 'false';
$userOwner = 'true';
$chatsGone = [];

$hasAccessToReadArray = array();

if (isset($_POST['chats']) && is_array($_POST['chats']) && count($_POST['chats']) > 0)
{
    $ReturnMessages = array();
    $ReturnStatuses = array();

    $tpl = erLhcoreClassTemplate::getInstance( 'lhchat/syncadmin.tpl.php');
    $currentUser = erLhcoreClassUser::instance();

    if (!isset($_SERVER['HTTP_X_CSRFTOKEN']) || !$currentUser->validateCSFRToken($_SERVER['HTTP_X_CSRFTOKEN'])) {
    	echo json_encode(array('error' => 'true', 'result' => 'Invalid CSRF Token' ));
    	exit;
    }
    
    // Set online condition configurations
    erLhcoreClassChat::$trackActivity = (int)erLhcoreClassModelChatConfig::fetchCache('track_activity')->current_value == 1;
    erLhcoreClassChat::$trackTimeout = (int)erLhcoreClassModelChatConfig::fetchCache('checkstatus_timeout')->current_value;
    erLhcoreClassChat::$onlineCondition = (int)erLhcoreClassModelChatConfig::fetchCache('online_if')->current_value;
        
    // We do not need a session anymore
    session_write_close();
    
    $db = ezcDbInstance::get();        

    $db->beginTransaction();
    try {
        foreach ($_POST['chats'] as $chat_id_list)
        {
            list($chat_id, $MessageID ) = explode(',',$chat_id_list);
            $chat_id = (int)$chat_id;
            $MessageID = (int)$MessageID;

            $Chat = erLhcoreClassModelChat::fetch($chat_id);

            if (!($Chat instanceof erLhcoreClassModelChat)) {
                $chatsGone[] = $chat_id;
                continue;
            }

            $Chat->updateIgnoreColumns = array('last_msg_id');

            if ( isset($hasAccessToReadArray[$chat_id]) || erLhcoreClassChat::hasAccessToRead($Chat) )
            {
                $hasAccessToReadArray[$chat_id] = true;

                if ( ($Chat->last_msg_id > (int)$MessageID) && count($Messages = erLhcoreClassChat::getPendingMessages($chat_id,$MessageID)) > 0)
                {
                    // If chat had flag that it contains unread messages set to 0
                    if ($Chat->has_unread_messages == 1 || $Chat->unread_messages_informed == 1) {
                         $Chat->has_unread_messages = 0;
                         $Chat->unread_messages_informed = 0;
                         $Chat->updateThis(array('update' => array('has_unread_messages','unread_messages_informed')));
                    }

                    // Auto accept transfered chats if I have opened this chat
                    if ($Chat->status == erLhcoreClassModelChat::STATUS_OPERATORS_CHAT) {

                       $q = $db->createDeleteQuery();

                       // Delete transfered chat's to me
                       $q->deleteFrom( 'lh_transfer' )->where( $q->expr->eq( 'chat_id', $Chat->id ), $q->expr->eq( 'transfer_to_user_id', $currentUser->getUserID() ) );
                       $stmt = $q->prepare();
                       $stmt->execute();
                    }

                    $newMessagesNumber = count($Messages);

                    $tpl->set('messages',$Messages);
                    $tpl->set('chat',$Chat);
                    $tpl->set('current_user_id',$currentUser->getUserID());

                    $msgText = '';
                    if ($userOwner == 'true') {
                        foreach ($Messages as $msg) {
                            if ($msg['user_id'] != $currentUser->getUserID()) {
                                $userOwner = 'false';
                                $msgText = $msg['msg'];
                                break;
                            }
                        }
                    }
                    // Get first message opertor id
                    reset($Messages);
                    $firstNewMessage = current($Messages);

                    // Get last message
                    end($Messages);
                    $LastMessageIDs = current($Messages);

                    // Fetch content
                    $templateResult = $tpl->fetch();

                    $response = array('chat_id' => $chat_id,'nck' => $Chat->nick, 'msfrom' => $MessageID, 'msop' => $firstNewMessage['user_id'], 'mn' => $newMessagesNumber, 'msg' => $msgText, 'content' => $templateResult, 'message_id' => $LastMessageIDs['id']);

                    erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.syncadmin',array('response' => & $response, 'messages' => $Messages, 'chat' => $Chat));

                    $ReturnMessages[] = $response;
                }

                $lp = $Chat->lsync > 0 ? time()-$Chat->lsync : false;

                if ($Chat->is_user_typing == true) {
                    $ReturnStatuses[$chat_id] = array('lmt' => max($Chat->last_user_msg_time, $Chat->last_op_msg_time, $Chat->pnd_time), 'cs' => $Chat->status, 'co' => $Chat->user_id, 'cdur' => $Chat->chat_duration_front, 'lmsg' => erLhcoreClassChat::formatSeconds(time() - ($Chat->last_user_msg_time > 0 ? $Chat->last_user_msg_time : $Chat->time)), 'chat_id' => $chat_id, 'lp' => $lp, 'um' => $Chat->has_unread_op_messages, 'us' => $Chat->user_status_front, 'tp' => 'true','tx' => htmlspecialchars($Chat->user_typing_txt));
                } else {
                    $ReturnStatuses[$chat_id] = array('lmt' => max($Chat->last_user_msg_time, $Chat->last_op_msg_time, $Chat->pnd_time), 'cs' => $Chat->status, 'co' => $Chat->user_id, 'cdur' => $Chat->chat_duration_front, 'lmsg' => erLhcoreClassChat::formatSeconds(time() - ($Chat->last_user_msg_time > 0 ? $Chat->last_user_msg_time : $Chat->time)), 'chat_id' => $chat_id, 'lp' => $lp, 'um' => $Chat->has_unread_op_messages, 'us' => $Chat->user_status_front, 'tp' => 'false');
                }

                if ($Chat->operation_admin != '') {
                    $ReturnStatuses[$chat_id]['oad'] = 1;
                }
            } else {
                $chatsGone[] = $chat_id;
            }

        }
        $db->commit();
    } catch (Exception $e) {
        $db->rollback();
    }

    if (count($ReturnMessages) > 0) {
        $content = $ReturnMessages;
    }

    if (count($ReturnStatuses) > 0) {
        $content_status = $ReturnStatuses;
    }
}

$response = array('error' => 'false','uw' => $userOwner, 'result_status' => $content_status, 'result' => $content);

if (!empty($chatsGone)) {
    $response['cg'] = $chatsGone;
}

echo erLhcoreClassChat::safe_json_encode($response);
exit;
?>