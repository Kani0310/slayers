<?php include(erLhcoreClassDesign::designtpl('lhsystem/configuration_links/proactive_pre.tpl.php'));?>
<?php if ($system_configuration_proactive_enabled == true && $currentUser->hasAccessTo('lhchat','administratechatvariables')) : ?>
<li><a href="<?php echo erLhcoreClassDesign::baseurl('abstract/list')?>/ProactiveChatVariables"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','Pro active chat variables');?></a></li>
<?php endif;?>