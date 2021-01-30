<?php

class erLhcoreClassGenericBotActionCommand {

    public static function process($chat, $action, $trigger, $params)
    {
        if (isset($params['presentation']) && $params['presentation'] == true) {
            return;
        }

        if ($action['content']['command'] == 'stopchat') {

            $isOnline = (isset($action['content']['payload_ignore_status']) && $action['content']['payload_ignore_status'] == true) || erLhcoreClassChat::isOnline($chat->dep_id,false, array(
                'exclude_bot' => true,
                'exclude_online_hours' => (isset($action['content']['payload_ignore_dep_hours']) && $action['content']['payload_ignore_dep_hours'] == true)
            ));

            if ($isOnline == false && isset($action['content']['payload']) && is_numeric($action['content']['payload'])) {

                $handler = erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.genericbot_chat_command_transfer', array(
                    'action' => $action,
                    'chat' => & $chat,
                    'is_online' => false
                ));

                if ($handler !== false) {
                    $trigger = $handler['trigger'];
                } else {
                    $trigger = erLhcoreClassModelGenericBotTrigger::fetch($action['content']['payload']);
                }

                if (($trigger instanceof erLhcoreClassModelGenericBotTrigger)){
                    erLhcoreClassGenericBotWorkflow::processTrigger($chat, $trigger, true);
                }

            } else if ($isOnline == true) {

                $chat->status = erLhcoreClassModelChat::STATUS_PENDING_CHAT;
                $chat->status_sub_sub = 2; // Will be used to indicate that we have to show notification for this chat if it appears on list
                $chat->pnd_time = time();
                // We do not have to set this
                // Because it triggers auto responder of not replying
                // $chat->last_op_msg_time = time();
                $chat->updateThis();

                // We have to reset auto responder
                if ($chat->auto_responder instanceof erLhAbstractModelAutoResponderChat) {
                    $chat->auto_responder->wait_timeout_send = 0;
                    $chat->auto_responder->pending_send_status = 0;
                    $chat->auto_responder->active_send_status = 0;
                    $chat->auto_responder->updateThis();
                }

                // If chat is transferred to pending state we don't want to process any old events
                $eventPending = erLhcoreClassModelGenericBotChatEvent::findOne(array('filter' => array('chat_id' => $chat->id)));

                if ($eventPending instanceof erLhcoreClassModelGenericBotChatEvent) {
                    $eventPending->removeThis();
                }

                // Because we want that mobile app would receive notification
                // By default these listeners are not set if visitors sends a message and chat is not active
                if (erLhcoreClassChatEventDispatcher::getInstance()->disableMobile == true && erLhcoreClassChatEventDispatcher::getInstance()->globalListenersSet == true) {
                    erLhcoreClassChatEventDispatcher::getInstance()->disableMobile = false;
                    erLhcoreClassChatEventDispatcher::getInstance()->globalListenersSet = false;
                    erLhcoreClassChatEventDispatcher::getInstance()->setGlobalListeners();
                }

                $handler = erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.genericbot_chat_command_transfer', array(
                    'action' => $action,
                    'chat' => & $chat,
                    'is_online' => true
                ));

                if ($handler !== false) {
                    $trigger = $handler['trigger'];
                } else {
                    if (isset($action['content']['payload_online']) && is_numeric($action['content']['payload_online'])) {
                        $trigger = erLhcoreClassModelGenericBotTrigger::fetch($action['content']['payload_online']);
                    } else {
                        $trigger = null;
                    }
                }

                if (($trigger instanceof erLhcoreClassModelGenericBotTrigger)){
                    erLhcoreClassGenericBotWorkflow::processTrigger($chat, $trigger, true);
                }
            }

        } elseif ($action['content']['command'] == 'transfertobot') {
            $chat->status = erLhcoreClassModelChat::STATUS_BOT_CHAT;
            $chat->last_op_msg_time = time();
            $chat->updateThis();

            if (isset($action['content']['payload']) && is_numeric($action['content']['payload'])) {

                $handler = erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.genericbot_chat_command_transfer', array(
                    'action' => $action,
                    'chat' => & $chat,
                ));

                if ($handler !== false) {
                    $trigger = $handler['trigger'];
                } else {
                    $trigger = erLhcoreClassModelGenericBotTrigger::fetch($action['content']['payload']);
                }

                if (($trigger instanceof erLhcoreClassModelGenericBotTrigger)){
                    erLhcoreClassGenericBotWorkflow::processTrigger($chat, $trigger, true);
                }
            }
        } elseif ($action['content']['command'] == 'closechat') {

            $chat->pnd_time = time();
            $chat->last_op_msg_time = time();

            if (isset($action['content']['close_widget']) && $action['content']['close_widget'] == true) {
                // Send execute JS message
                $msg = new erLhcoreClassModelmsg();
                $msg->msg = '';
                $msg->meta_msg = '{"content":{"execute_js":{"chat_event":"endChat","payload":""}}}';
                $msg->chat_id = $chat->id;
                $msg->user_id = -2;
                $msg->time = time();
                $msg->name_support = erLhcoreClassGenericBotWorkflow::getDefaultNick($chat);;
                $msg->saveThis();
            }

            $handler = erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.genericbot_chat_command_transfer', array(
                'action' => $action,
                'chat' => & $chat,
            ));

            if ($handler === false) {
                erLhcoreClassChatHelper::closeChat(array(
                    'chat' => & $chat,
                    'bot' => true
                ));
            }

        } elseif ($action['content']['command'] == 'chatattribute') {
            
            $variablesArray = (array)$chat->additional_data_array;

            $variablesAppend = json_decode($action['content']['payload'],true);

            if (is_array($variablesAppend)) {

                $updatedIdentifiers = array();

                // Update and insert new one.
                foreach ($variablesAppend as $value) {
                    if (isset($value['identifier']) && isset($value['key']) && $value['key'] != '' && $value['identifier'] != '') {
                        foreach ($variablesArray as $indexVariable => $variableData) {
                            if ($variableData['identifier'] == $value['identifier']) {
                                if (isset($value['value'])) {
                                    $valueItem = isset($params['replace_array']) ? str_replace(array_keys($params['replace_array']),array_values($params['replace_array']),$value['value']) : $value['value'];
                                    $variablesArray[$indexVariable]['value'] = erLhcoreClassGenericBotWorkflow::translateMessage($valueItem, array('chat' => $chat, 'args' => $params));
                                } else {
                                    unset($variablesArray[$indexVariable]);
                                }
                                $updatedIdentifiers[] = $value['identifier'];
                            }
                        }
                    }
                }

                foreach ($variablesAppend as $value) {
                    if (isset($value['identifier']) && isset($value['key']) && isset($value['value']) && $value['key'] != '' && $value['identifier'] != '' && !in_array($value['identifier'],$updatedIdentifiers)) {
                        $valueItem = (isset($params['replace_array']) ? str_replace(array_keys($params['replace_array']),array_values($params['replace_array']),$value['value']) : $value['value']);
                        $variablesArray[] = array(
                            'identifier' => $value['identifier'],
                            'key' => $value['key'],
                            'value' => erLhcoreClassGenericBotWorkflow::translateMessage($valueItem, array('chat' => $chat, 'args' => $params))
                        );
                    }
                }

                $variablesArray = array_values($variablesArray);

                $chat->additional_data = json_encode($variablesArray);
                $chat->additional_data_array = $variablesArray;
                $chat->updateThis(array('update' => array('additional_data')));
            }

        } elseif ($action['content']['command'] == 'chatvariable') {

                $variablesArray = (array)$chat->chat_variables_array;

                $variablesAppend = json_decode($action['content']['payload'],true);

                if (is_array($variablesAppend)) {
                    foreach ($variablesAppend as $key => $value) {
                        if (isset($params['replace_array']) && isset($value)) {
                            $valueItem = str_replace(array_keys($params['replace_array']),array_values($params['replace_array']),$value);
                            $variablesArray[$key] = erLhcoreClassGenericBotWorkflow::translateMessage($valueItem, array('chat' => $chat, 'args' => $params));
                        } else {
                            if (isset($value)) {
                                $variablesArray[$key] = erLhcoreClassGenericBotWorkflow::translateMessage($value, array('chat' => $chat, 'args' => $params));
                            } elseif (isset($variablesArray[$key])) {
                                unset($variablesArray[$key]);
                            }
                        }
                    }
                }

                $chat->chat_variables = json_encode($variablesArray);
                $chat->chat_variables_array = $variablesArray;
                $chat->saveThis();

        } elseif ($action['content']['command'] == 'setchatattribute') {

                // Replace variables if any
                $action['content']['payload_arg'] = isset($params['replace_array']) ? str_replace(array_keys($params['replace_array']),array_values($params['replace_array']),$action['content']['payload_arg']) : $action['content']['payload_arg'];

                $eventArgs = array('old' => $chat->{$action['content']['payload']}, 'attr' => $action['content']['payload'], 'new' => $action['content']['payload_arg']);
                
                $chat->{$action['content']['payload']} = erLhcoreClassGenericBotWorkflow::translateMessage($action['content']['payload_arg'], array('chat' => $chat, 'args' => $params));

                $updateDepartmentStats = false;

                if ($eventArgs['attr'] == 'dep_id' && $eventArgs['old'] != $action['content']['payload_arg']) {
                    erLhAbstractModelAutoResponder::updateAutoResponder($chat);

                    $department = erLhcoreClassModelDepartament::fetch($chat->dep_id);

                    if ($department instanceof erLhcoreClassModelDepartament) {
                        if ($department->department_transfer_id > 0) {
                            $chat->transfer_if_na = 1;
                            $chat->transfer_timeout_ts = time();
                            $chat->transfer_timeout_ac = $department->transfer_timeout;
                        }

                        if ($department->inform_unread == 1) {
                            $chat->reinform_timeout = $department->inform_unread_delay;
                        }

                        if ($department->priority > $chat->priority) {
                            $chat->priority = $department->priority;
                        }

                        $updateDepartmentStats = true;

                    }
                }

                if ($eventArgs['attr'] == 'status' && $eventArgs['old'] != $action['content']['payload_arg']) {
                    $chat->pnd_time = time();
                }

                if ($eventArgs['attr'] == 'user_id' && $eventArgs['old'] != $action['content']['payload_arg']) {
                    $chat->status_sub = erLhcoreClassModelChat::STATUS_SUB_OWNER_CHANGED;
                }

                $chat->saveThis();

                if ($updateDepartmentStats == true) {
                    erLhcoreClassChat::updateDepartmentStats($department);
                }

        } elseif ($action['content']['command'] == 'setdepartment') {

            // Department was changed
            if ($chat->dep_id != $action['content']['payload']) {

                $department = erLhcoreClassModelDepartament::fetch($action['content']['payload']);

                if ($department instanceof erLhcoreClassModelDepartament) {
                    $chat->dep_id = $department->id;

                    erLhAbstractModelAutoResponder::updateAutoResponder($chat);

                    if ($department->department_transfer_id > 0) {
                        $chat->transfer_if_na = 1;
                        $chat->transfer_timeout_ts = time();
                        $chat->transfer_timeout_ac = $department->transfer_timeout;
                    }

                    if ($department->inform_unread == 1) {
                        $chat->reinform_timeout = $department->inform_unread_delay;
                    }

                    if ($department->priority > $chat->priority) {
                        $chat->priority = $department->priority;
                    }

                    $chat->saveThis();

                    erLhcoreClassChat::updateDepartmentStats($department);
                }
            }

        } elseif ($action['content']['command'] == 'removeprocess') {

            $q = ezcDbInstance::get()->createDeleteQuery();

            // Repeat counter remove
            $q->deleteFrom( 'lh_generic_bot_repeat_restrict' )->where( $q->expr->eq( 'chat_id', $chat->id ) );
            $stmt = $q->prepare();
            $stmt->execute();

            // Bot chat event remove
            $q->deleteFrom( 'lh_generic_bot_chat_event' )->where( $q->expr->eq( 'chat_id', $chat->id ) );
            $stmt = $q->prepare();
            $stmt->execute();

            // Bot chat event remove
            $q->deleteFrom( 'lh_generic_bot_pending_event' )->where( $q->expr->eq( 'chat_id', $chat->id ) );
            $stmt = $q->prepare();
            $stmt->execute();
            
        } elseif ($action['content']['command'] == 'setsubject') {

            $remove = isset($action['content']['remove_subject']) && $action['content']['remove_subject'] == true;
            if ($remove == true && is_numeric($action['content']['payload'])) {
                $subjectChat = erLhAbstractModelSubjectChat::findOne(array('filter' => array('subject_id' => (int)$action['content']['payload'], 'chat_id' => $chat->id)));
                if ($subjectChat instanceof erLhAbstractModelSubjectChat) {
                    $subjectChat->removeThis();
                }
            } else if (is_numeric($action['content']['payload']) && ($subject = erLhAbstractModelSubject::fetch((int)$action['content']['payload'])) instanceof erLhAbstractModelSubject) {
                $subjectChat = erLhAbstractModelSubjectChat::findOne(array('filter' => array('subject_id' => (int)$action['content']['payload'], 'chat_id' => $chat->id)));
                if (!($subjectChat instanceof erLhAbstractModelSubjectChat)) {
                    $subjectChat = new erLhAbstractModelSubjectChat();
                    $subjectChat->subject_id = $subject->id;
                    $subjectChat->chat_id = $chat->id;
                    $subjectChat->saveThis();
                }
            }

        } elseif ($action['content']['command'] == 'dispatchevent') {
                erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.genericbot_chat_command_dispatch_event', array(
                    'action' => $action,
                    'chat' => & $chat,
                    'replace_array' => (isset($params['replace_array']) ? $params['replace_array'] : [])
                ));
        }
    }
}

?>