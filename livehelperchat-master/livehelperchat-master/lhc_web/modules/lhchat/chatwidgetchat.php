<?php

// For IE to support headers if chat is installed on different domain
header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');

$tpl = erLhcoreClassTemplate::getInstance( 'lhchat/chat.tpl.php');

$embedMode = false;
$modeAppend = '';
if ((string)$Params['user_parameters_unordered']['mode'] == 'embed') {
	$embedMode = true;
	$modeAppend = '/(mode)/embed';
}

$noMobile = false;
if ((string)$Params['user_parameters_unordered']['mobile'] == 'false') {
    $modeAppend .= '/(mobile)/false';
    $noMobile = true;
}

if (isset($Params['user_parameters_unordered']['theme']) && (int)$Params['user_parameters_unordered']['theme'] > 0){
	try {
		$theme = erLhAbstractModelWidgetTheme::fetch($Params['user_parameters_unordered']['theme']);
        $theme->translate();
		$Result['theme'] = $theme;
		$tpl->set('theme',$theme);
		$modeAppend .= '/(theme)/'.$theme->id;
	} catch (Exception $e) {

	}
}

if ($Params['user_parameters_unordered']['sound'] !== null && is_numeric($Params['user_parameters_unordered']['sound'])) {
	erLhcoreClassModelUserSetting::setSetting('chat_message',(int)$Params['user_parameters_unordered']['sound'] == 1 ? 1 : 0);
}

if ($Params['user_parameters_unordered']['cstarted'] !== null && $Params['user_parameters_unordered']['cstarted'] != '') {
	$Result['parent_messages'][] = 'lh_callback:' . (string)strip_tags($Params['user_parameters_unordered']['cstarted']);
    $tpl->set('chat_started_now',true);
}

try {
    $db = ezcDbInstance::get();
    $db->beginTransaction();
    
    $chat = erLhcoreClassModelChat::fetchAndLock($Params['user_parameters']['chat_id']);

    if (!($chat instanceof erLhcoreClassModelChat)) {
        throw new Exception('Chat not found!');
    }

    erLhcoreClassChat::setTimeZoneByChat($chat);
 
    if (is_numeric($Params['user_parameters_unordered']['pchat'])) {
        erLhcoreClassChatPaid::openChatWidget(array(
            'tpl' => & $tpl,
            'pchat' => $Params['user_parameters_unordered']['pchat'],
            'chat' => $chat
        ));
    }

    if ($chat->hash == $Params['user_parameters']['hash'])
    {
        $survey = is_numeric($Params['user_parameters_unordered']['survey']) ? (int)$Params['user_parameters_unordered']['survey'] : false;
        $tpl->set('chat_id',$Params['user_parameters']['chat_id']);
        $tpl->set('hash',$Params['user_parameters']['hash']);
        $tpl->set('chat',$chat);
        $tpl->set('chat_widget_mode',true);
        $tpl->set('chat_embed_mode',$embedMode);
        $tpl->set('survey',$survey);

        if ($survey > 0) {
            $Result['parent_messages'][] = 'lhc_chat_survey:' . $survey;
        }

        $Result['chat'] = $chat;

        // If survey send parent message instantly
        if ($chat->status_sub == erLhcoreClassModelChat::STATUS_SUB_SURVEY_SHOW) {
            $args = erLhcoreClassChatHelper::getSubStatusArguments($chat);
            $Result['parent_messages'][] = 'lhc_chat_closed' . ($args != '' ? ':' . $args : '');
        }

        // User online
        if ($chat->user_status != 0) {
            $chat->support_informed = 1;
            $chat->user_typing = time();// Show for shorter period these status messages
            $chat->is_user_typing = 1;
            if (($refererSite = erLhcoreClassModelChatOnlineUser::getReferer()) != '') {

                if (strlen($refererSite) > 50) {
                    if ( function_exists('mb_substr') ) {
                        $refererSite = mb_substr($refererSite, 0, 50);
                    } else {
                        $refererSite = substr($refererSite, 0, 50);
                    }
                }

                $chat->user_typing_txt = $refererSite;
            } else {
                $chat->user_typing_txt = htmlspecialchars_decode(erTranslationClassLhTranslation::getInstance()->getTranslation('chat/userjoined','Visitor has joined the chat!'),ENT_QUOTES);
            }
            
            if ($chat->user_status == erLhcoreClassModelChat::USER_STATUS_PENDING_REOPEN && ($onlineuser = $chat->online_user) !== false) {
                $onlineuser->reopen_chat = 0;
                $onlineuser->saveThis();
            }

            $chat->unread_op_messages_informed = 0;
            $chat->has_unread_op_messages = 0;
            $chat->unanswered_chat = 0;

            $chat->user_status = erLhcoreClassModelChat::USER_STATUS_JOINED_CHAT;

            $nick = isset($_GET['prefill']['username']) ? trim($_GET['prefill']['username']) : '';

            // Update nick if required
            if (isset($_GET['prefill']['username']) && $chat->nick != $_GET['prefill']['username'] && !empty($nick) && $chat->nick == erTranslationClassLhTranslation::getInstance()->getTranslation('chat/startchat','Visitor')) {
                $chat->nick = $_GET['prefill']['username'];
                $chat->operation_admin .= "lhinst.updateVoteStatus(".$chat->id.");";
                
                erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.nickchanged', array('chat' => & $chat));
            }
                        
            if ($chat->unanswered_chat == 1 && $chat->status == erLhcoreClassModelChat::STATUS_ACTIVE_CHAT)
            {
                $chat->unanswered_chat = 0;
            }
            
            $chat->updateThis(array('update' => array(
                'unanswered_chat',
                'operation_admin',
                'nick',
                'user_status',
                'has_unread_op_messages',
                'unread_op_messages_informed',
                'user_typing_txt',
                'support_informed',
                'user_typing',
                'is_user_typing'
            )));
        }

        $db->commit();

        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.chatwidgetchat',array('result' => & $Result , 'tpl' => & $tpl, 'params' => & $Params, 'chat' => & $chat));

    } else {
        $tpl->setFile( 'lhchat/errors/chatnotexists.tpl.php');
    }

} catch(Exception $e) {
    $db->rollback();
   $tpl->setFile('lhchat/errors/chatnotexists.tpl.php');
}

if (isset($Params['user_parameters_unordered']['fullheight']) && $Params['user_parameters_unordered']['fullheight'] == 'true') {
    $Result['fullheight'] = true;
    $tpl->set('fullheight', true);
} else {
    $Result['fullheight'] = false;
    $tpl->set('fullheight', false);
}

$Result['content'] = $tpl->fetch();
$Result['pagelayout'] = 'widget';
$Result['pagelayout_css_append'] = 'widget-chat';
$Result['dynamic_height'] = true;
$Result['dynamic_height_message'] = 'lhc_sizing_chat';
$Result['path'] = array(array('title' => erTranslationClassLhTranslation::getInstance()->getTranslation('chat/chat','Chat started')));
$Result['is_sync_required'] = true;

if ($noMobile === true) {
    $Result['no_mobile_css'] = true;
}

if ($embedMode == true) {
	$Result['dynamic_height_message'] = 'lhc_sizing_chat_page';
	$Result['pagelayout_css_append'] = 'embed-widget';
}

?>