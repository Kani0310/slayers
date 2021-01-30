<?php

class erLhcoreClassModelChatOnlineUserFootprint {

   public function getState()
   {
       return array(
               'id'             => $this->id,
               'chat_id'        => $this->chat_id,
               'online_user_id' => $this->online_user_id,
               'page'       	=> $this->page,
               'vtime'          => $this->vtime
       );
   }

   public function setState( array $properties )
   {
       foreach ( $properties as $key => $val )
       {
           $this->$key = $val;
       }
   }

   public static function fetch($chat_id) {
       	 $chat = erLhcoreClassChat::getSession()->load( 'erLhcoreClassModelChatOnlineUserFootprint', (int)$chat_id );
       	 return $chat;
   }

   public function removeThis() {
       erLhcoreClassChat::getSession()->delete($this);
   }

   public function __get($var) {
       switch ($var) {

       	case 'vtime_front':
       		  return date(erLhcoreClassModule::$dateDateHourFormat,$this->vtime);
       		break;

       	case 'time_ago':
       			$this->time_ago = '';

       			if ( $this->vtime > 0 ) {

       				$periods         = array("s.", "m.", "h.", "d.", "w.", "m.", "y.", "dec.");
       				$lengths         = array("60","60","24","7","4.35","12","10");

       				$difference     = time() - $this->vtime;

       				for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
       					$difference /= $lengths[$j];
       				}

       				$difference = round($difference);

       				$this->time_ago = "$difference $periods[$j]";
       			};

       			return $this->time_ago;
       		break;

       	default:
       		break;
       }
   }

   public static function getCount($params = array())
   {
       $session = erLhcoreClassChat::getSession();
       $q = $session->database->createSelectQuery();
       $q->select( "COUNT(id)" )->from( "lh_chat_online_user_footprint" );

       if (isset($params['filter']) && count($params['filter']) > 0)
       {
           $conditions = array();

           foreach ($params['filter'] as $field => $fieldValue)
           {
               $conditions[] = $q->expr->eq( $field, $q->bindValue($fieldValue) );
           }

           $q->where(
                 $conditions
           );
      }

      $stmt = $q->prepare();
      $stmt->execute();
      $result = $stmt->fetchColumn();

      return $result;
   }

   public static function addPageView(erLhcoreClassModelChatOnlineUser $onlineUser) {
   		$item = new self();
   		$item->chat_id = $onlineUser->chat_id;
   		$item->online_user_id = $onlineUser->id;
   		$item->vtime = time();
   		$item->page = isset($_POST['l']) ? substr($_POST['l'],0,250) :  (isset($_GET['l']) ? rawurldecode($_GET['l']) : substr(erLhcoreClassModelChatOnlineUser::getReferer(),0,250));
   		erLhcoreClassChat::getSession()->save( $item );
   		
   		erLhcoreClassChatEventDispatcher::getInstance()->dispatch('onlinefootprint.created', array('item' => & $item));
   }

   public static function assignChatToPageviews(erLhcoreClassModelChatOnlineUser $onlineUser, $backgroundJob = false) {
       // No extension has overridden this
       // Perhaps background worker does this?
       $statusWorkflow = erLhcoreClassChatEventDispatcher::getInstance()->dispatch('online.assign_chat_to_pageviews',array('online_user' => & $onlineUser));

       if ($statusWorkflow === false) {
           if ($backgroundJob === false) {
               // Update instantly
               $db = ezcDbInstance::get();
               $stmt = $db->prepare('UPDATE lh_chat_online_user_footprint SET chat_id = :chat_id WHERE online_user_id = :online_user_id');
               $stmt->bindValue(':chat_id',(int)$onlineUser->chat_id,PDO::PARAM_INT);
               $stmt->bindValue(':online_user_id',(int)$onlineUser->id,PDO::PARAM_INT);
               $stmt->execute();
           } else {
               // Background will process event
               $db = ezcDbInstance::get();
               $stmt = $db->prepare("INSERT INTO lh_chat_online_user_footprint_update (online_user_id,command,args,ctime) VALUES (:online_user_id, 'set_chat',:chat_id,:ctime)");
               $stmt->bindValue(':online_user_id',(int)$onlineUser->id,PDO::PARAM_INT);
               $stmt->bindValue(':chat_id',(int)$onlineUser->chat_id,PDO::PARAM_STR);
               $stmt->bindValue(':ctime',time(),PDO::PARAM_STR);
               $stmt->execute();
           }
       }
   }

   public static function getList($paramsSearch = array())
   {
       $paramsDefault = array('limit' => 32, 'offset' => 0);

       $params = array_merge($paramsDefault,$paramsSearch);

       $session = erLhcoreClassChat::getSession();
       $q = $session->createFindQuery( 'erLhcoreClassModelChatOnlineUserFootprint' );

       $conditions = array();

      if (isset($params['filter']) && count($params['filter']) > 0)
      {
           foreach ($params['filter'] as $field => $fieldValue)
           {
               $conditions[] = $q->expr->eq( $field, $q->bindValue($fieldValue) );
           }
      }

      if (isset($params['filterin']) && count($params['filterin']) > 0)
      {
           foreach ($params['filterin'] as $field => $fieldValue)
           {
               $conditions[] = $q->expr->in( $field, $fieldValue );
           }
      }

      if (isset($params['filterlt']) && count($params['filterlt']) > 0)
      {
           foreach ($params['filterlt'] as $field => $fieldValue)
           {
               $conditions[] = $q->expr->lt( $field, $q->bindValue($fieldValue) );
           }
      }

      if (isset($params['filtergt']) && count($params['filtergt']) > 0)
      {
           foreach ($params['filtergt'] as $field => $fieldValue)
           {
               $conditions[] = $q->expr->gt( $field,$q->bindValue( $fieldValue ));
           }
      }

      if (count($conditions) > 0)
      {
          $q->where(
                     $conditions
          );
      }

      $q->limit($params['limit'],$params['offset']);

      $q->orderBy(isset($params['sort']) ? $params['sort'] : 'id DESC' );

      $objects = $session->find( $q );

      return $objects;
   }

   public function saveThis() {
        erLhcoreClassChat::getSession()->saveOrUpdate( $this );
   }

   public $id = null;
   public $chat_id = '';
   public $online_user_id = '';
   public $page = '';
   public $vtime = '';
}

?>