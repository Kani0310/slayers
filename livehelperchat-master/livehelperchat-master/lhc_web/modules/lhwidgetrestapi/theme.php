<?php

erLhcoreClassRestAPIHandler::setHeaders('Content-type: text/css');

$theme = erLhAbstractModelWidgetTheme::fetch($Params['user_parameters']['theme']);

if ($theme->modified > 0) {
    Header("Expires:".gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
    header("Last-Modified: ".gmdate("D, d M Y H:i:s", $theme->modified)." GMT");

    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $theme->modified) {
        header("HTTP/1.1 304 Not Modified");
        exit;
    }
}

$tpl = erLhcoreClassTemplate::getInstance('lhwidgetrestapi/theme.tpl.php');
$tpl->set('theme',$theme);
$tpl->set('popup',$Params['user_parameters_unordered']['p'] == 1);

echo $tpl->fetch();

exit;
?>