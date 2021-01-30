<?php

$def = new ezcPersistentObjectDefinition();
$def->table = "lh_userdep";
$def->class = "erLhcoreClassModelUserDep";

$def->idProperty = new ezcPersistentObjectIdProperty();
$def->idProperty->columnName = 'id';
$def->idProperty->propertyName = 'id';
$def->idProperty->generator = new ezcPersistentGeneratorDefinition(  'ezcPersistentNativeGenerator' );

$def->properties['user_id'] = new ezcPersistentObjectProperty();
$def->properties['user_id']->columnName   = 'user_id';
$def->properties['user_id']->propertyName = 'user_id';
$def->properties['user_id']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT; 

$def->properties['dep_id'] = new ezcPersistentObjectProperty();
$def->properties['dep_id']->columnName   = 'dep_id';
$def->properties['dep_id']->propertyName = 'dep_id';
$def->properties['dep_id']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT; 

// Last activity
$def->properties['last_activity'] = new ezcPersistentObjectProperty();
$def->properties['last_activity']->columnName   = 'last_activity';
$def->properties['last_activity']->propertyName = 'last_activity';
$def->properties['last_activity']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT;

// Last dom activity
$def->properties['lastd_activity'] = new ezcPersistentObjectProperty();
$def->properties['lastd_activity']->columnName   = 'lastd_activity';
$def->properties['lastd_activity']->propertyName = 'lastd_activity';
$def->properties['lastd_activity']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT;

$def->properties['hide_online_ts'] = new ezcPersistentObjectProperty();
$def->properties['hide_online_ts']->columnName   = 'hide_online_ts';
$def->properties['hide_online_ts']->propertyName = 'hide_online_ts';
$def->properties['hide_online_ts']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT; 

$def->properties['hide_online'] = new ezcPersistentObjectProperty();
$def->properties['hide_online']->columnName   = 'hide_online';
$def->properties['hide_online']->propertyName = 'hide_online';
$def->properties['hide_online']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT; 

$def->properties['last_accepted'] = new ezcPersistentObjectProperty();
$def->properties['last_accepted']->columnName   = 'last_accepted';
$def->properties['last_accepted']->propertyName = 'last_accepted';
$def->properties['last_accepted']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT; 

$def->properties['active_chats'] = new ezcPersistentObjectProperty();
$def->properties['active_chats']->columnName   = 'active_chats';
$def->properties['active_chats']->propertyName = 'active_chats';
$def->properties['active_chats']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT;

$def->properties['pending_chats'] = new ezcPersistentObjectProperty();
$def->properties['pending_chats']->columnName   = 'pending_chats';
$def->properties['pending_chats']->propertyName = 'pending_chats';
$def->properties['pending_chats']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT;

$def->properties['inactive_chats'] = new ezcPersistentObjectProperty();
$def->properties['inactive_chats']->columnName   = 'inactive_chats';
$def->properties['inactive_chats']->propertyName = 'inactive_chats';
$def->properties['inactive_chats']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT;

$def->properties['always_on'] = new ezcPersistentObjectProperty();
$def->properties['always_on']->columnName   = 'always_on';
$def->properties['always_on']->propertyName = 'always_on';
$def->properties['always_on']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT;

return $def; 

?>