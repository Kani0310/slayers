<?php

class erLhcoreClassModelChatBlockedUser
{

    use erLhcoreClassDBTrait;

    public static $dbTable = 'lh_chat_blocked_user';

    public static $dbTableId = 'id';

    public static $dbSessionHandler = 'erLhcoreClassChat::getSession';

    public static $dbSortOrder = 'DESC';

    public function getState()
    {
        return array(
            'id' => $this->id,
            'ip' => $this->ip,
            'user_id' => $this->user_id,
            'datets' => $this->datets,
            'chat_id' => $this->chat_id,
            'dep_id' => $this->dep_id,
            'nick' => $this->nick,
            'btype' => $this->btype,
            'expires' => $this->expires,
        );
    }

    public function __get($var)
    {
        switch ($var) {
            case 'datets_front':
                return date(erLhcoreClassModule::$dateDateHourFormat, $this->datets);

            case 'expires_front':
                return $this->expires > 0 && $this->expires > time() ? erLhcoreClassChat::formatSeconds($this->expires - time()) : (($this->expires > 0) ? 'Exp.' : '∞');

            case 'user':
                try {
                    $this->user = erLhcoreClassModelUser::fetch($this->user_id);
                } catch (Exception $e) {
                    $this->user = '-';
                }
                return $this->user;

            case 'department':
                $this->department = null;
                if ($this->dep_id > 0) {
                    $this->department = erLhcoreClassModelDepartament::fetch($this->dep_id);
                }
                return $this->department;

            default:
                break;
        }
    }

    public function beforeSave()
    {
        $this->datets = time();
    }

    public static function isBlocked($params)
    {
        $db = ezcDbInstance::get();

        return erLhcoreClassModelChatBlockedUser::getCount(array(
                'customfilter' => array(
                    '(
                        (`ip` = ' . $db->quote($params['ip']) .' AND btype IN (0,3,4)) 
                        OR (`nick` = ' . $db->quote($params['nick']) . ' AND btype IN (1,3)) 
                        OR (`nick` = ' . $db->quote($params['nick']) . ' AND `dep_id` = ' . $db->quote($params['dep_id']) . ' AND btype IN (2,4))
                    ) AND (expires = 0 OR expires > ' . time() . ')'
                )
        )) > 0;
    }

    public static function blockChat($params) {

        $filter = array();

        if (!isset($params['btype']) || $params['btype'] == self::BLOCK_IP) {
            $filter = array('filter' => array('btype' => self::BLOCK_IP, 'ip' => $params['chat']->ip));
            $params['btype'] = self::BLOCK_IP;
        } elseif ($params['btype'] == self::BLOCK_NICK) {
            $filter = array('filter' => array('btype' => self::BLOCK_NICK, 'nick' => $params['chat']->nick));
        } elseif ($params['btype'] == self::BLOCK_NICK_DEP) {
            $filter = array('filter' => array('btype' => self::BLOCK_NICK_DEP,  'dep_id' =>  $params['chat']->dep_id, 'nick' => $params['chat']->nick));
        } elseif ($params['btype'] == self::BLOCK_ALL_IP_NICK_DEP) {
            $filter = array('filter' => array('btype' => self::BLOCK_ALL_IP_NICK_DEP, 'dep_id' =>  $params['chat']->dep_id, 'nick' => $params['chat']->nick));
        } elseif ($params['btype'] == self::BLOCK_ALL_IP_NICK) {
            $filter = array('filter' => array('btype' => self::BLOCK_ALL_IP_NICK, 'nick' => $params['chat']->nick));
        }

        if (erLhcoreClassModelChatBlockedUser::getCount($filter) == 0)
        {
            $block = new erLhcoreClassModelChatBlockedUser();
            $block->ip = $params['chat']->ip;
            $block->user_id = erLhcoreClassUser::instance()->getUserID();
            $block->chat_id = $params['chat']->id;
            $block->dep_id = $params['chat']->dep_id;
            $block->nick = $params['chat']->nick;
            $block->btype = $params['btype'];
            $block->expires = isset($params['expires']) ? (int)$params['expires'] : 0;
            $block->saveThis();
        }
    }

    const BLOCK_IP = 0;
    const BLOCK_NICK = 1;
    const BLOCK_NICK_DEP = 2;
    const BLOCK_ALL_IP_NICK = 3;
    const BLOCK_ALL_IP_NICK_DEP = 4;

    public $id = null;
    public $ip = '';
    public $user_id = 0;
    public $datets = null;
    public $chat_id = 0;
    public $dep_id = 0;
    public $nick = '';
    public $btype = 0;
    public $expires = 0;
}

?>