Live helper chat
==============

It's an open-source powered application, which brings simplicity and usability in one place. With live helper chat you can bring live support on your site for free. http://livehelperchat.com.

[![Apple store](https://livehelperchat.com/design/defaulttheme/images/apps/apple.svg)](https://apps.apple.com/us/app/id1530399116) [![Google Play](https://livehelperchat.com/design/defaulttheme/images/apps/google-play.png?v=2)](https://play.google.com/store/apps/details?id=com.livehelperchat.chat) [![Deploy to DO](https://mp-assets1.sfo2.digitaloceanspaces.com/deploy-to-do/do-btn-blue.svg)](https://marketplace.digitalocean.com/apps/live-helper-chat/?refcode=09c74421e3c2&utm_campaign=Referral_Invite&utm_medium=Referral_Program&utm_source=CopyPaste)

[![Codemagic build status](https://api.codemagic.io/apps/5f50c50be2db272d7690ae45/5f50c50be2db272d7690ae44/status_badge.svg)](https://codemagic.io/apps/5f50c50be2db272d7690ae45/5f50c50be2db272d7690ae44/latest_build)

## Need help?
* Documentation - https://doc.livehelperchat.com
* Forum - https://forum.livehelperchat.com/
* Chat (Discord) https://discord.gg/YsZXQVh
* [Laravel version of Live Helper Chat](https://github.com/LiveHelperChat/livehelperchat_laravel)

## Demo

http://livehelperchat.com/demo-12c.html

## Integrations

 * [Mobile app](https://github.com/LiveHelperChat/lhc_messenger) flutter
 * [Voice & Video & ScreenShare](https://doc.livehelperchat.com/docs/voice-video-screenshare) powered by [agora](https://www.agora.io/en/)
 * [Rest API](https://api.livehelperchat.com)
 * [Bot](https://doc.livehelperchat.com/docs/how-to-use-bot) with possibility to integrate any third party AI
 * [Telegram](https://github.com/LiveHelperChat/telegram)
 * [Rasa](https://doc.livehelperchat.com/docs/bot/rasa-integration)
 * [Facebook messenger](https://github.com/LiveHelperChat/fbmessenger)
 * [Insult detection](https://github.com/LiveHelperChat/lhcinsult) powered by [DeepPavlov.ai](https://demo.deeppavlov.ai/#/en/insult) and [NudeNet](https://github.com/notAI-tech/NudeNet)
 * [SMS, WhatsApp](https://github.com/LiveHelperChat/twilio)
 * [Elasticsearch](https://github.com/LiveHelperChat/elasticsearch) get statistic for millions of chats in seconds
 * [Node.js](https://github.com/LiveHelperChat/NodeJS-Helper)
 * [Docker](https://github.com/LiveHelperChat/docker-standalone)
 * [Background worker for heavy tasks](https://github.com/LiveHelperChat/lhc-php-resque) offload Rest API calls
 * Integrate any [third party Rest API](https://doc.livehelperchat.com/docs/bot/rest-api)
 * [Google Authentication](https://github.com/LiveHelperChat/lhcgoogleauth) login using Google account
 * [2FA](https://github.com/LiveHelperChat/2fa) `Authenticator` mobile app support
 * [Amazon S3](https://github.com/LiveHelperChat/amazon-s3) scale infinitely by storing app files in the cloud
 * [Desktop app](https://github.com/LiveHelperChat/electron) written with electron
 * [Sentiment analysis using DeepPavlov](https://github.com/LiveHelperChat/sentiment)

## Quick development guide
 * After app is installed disable cache and enable debug output. 
   * https://github.com/LiveHelperChat/livehelperchat/blob/master/lhc_web/settings/settings.ini.default.php#L13-L16
   * Change the following values to
    ```
    * debug_output => true
   * templatecache => false
   * templatecompile => false
   * modulecompile => false
   ```
 * To compile JS from lhc_web folder execute. This will compile main JS and old widget javascript files.
   * `npm install && gulp`
 * To compile new widget V2
   * There is two apps [wrapper](https://github.com/LiveHelperChat/livehelperchat/tree/master/lhc_web/design/defaulttheme/widget/wrapper) and [widget](https://github.com/LiveHelperChat/livehelperchat/tree/master/lhc_web/design/defaulttheme/widget/react-app)
   * `cd lhc_web/design/defaulttheme/widget/wrapper && npm install && npm run build`
   * `cd lhc_web/design/defaulttheme/widget/react-app && npm install && npm run build && npm run build-ie`
 * Recompile static JS/CSS files. This is required if you change core JS files. It also avoids missing CSS/JS files if more than one server is used.
   * `php cron.php -s site_admin -c cron/util/generate_css -p 1 && gulp js-static`

## Extensions
https://github.com/LiveHelperChat

## Translations contribution
https://www.transifex.com/projects/p/live-helper-chat/

## Folders structure

 * Directories content:
  * lhc_web - WEB application folder.
 
## Features

Few main features

 * [Bot](https://doc.livehelperchat.com/docs/how-to-use-bot) with possibility to integrate any third party AI
 * XMPP support for notifications about new chats. (IPhone, IPad, Android, Blackberry, GTalk etc...)
 * Chrome extension
 * Repeatable sound notifications
 * Work hours
 * See what user see with screenshot feature
 * Drag & Drop widgets, minimize/maximize widgets
 * Multiple chats same time
 * See what users are typing before they send a message
 * Multiple operators
 * Send delayed canned messages as it was real user typing
 * Chats archive
 * Priority queue
 * Chats statistic generation, top chats
 * Resume chat after user closed chat
 * All chats in single window with tabs interface, tabs are remembered before they are closed
 * Chat transcript print
 * Chat transcript send by mail
 * Site widget
 * Page embed mode for live support script or widget mode, or standard mode.
 * Multilanguage
 * Chats transfering
 * Departments
 * Files upload
 * Chat search
 * Automatic transfers between departments
 * Option to generate JS for different departments
 * Option to prefill form fields. 
 * Option to add custom form fields. It can be either user variables or hidden fields. Usefull if you are integrating with third party system and want to pass user_id for example.
 * Cronjobs
 * Callbacks
 * Closed chat callback
 * Unanswered chat callback
 * Asynchronous status loading, not blocking site javascript.
 * XML, JSON export module
 * Option to send transcript to users e-mail
 * SMTP support
 * HTTPS support
 * No third parties cookies dependency
 * Previous users chats
 * Online users tracking, including geo detection
 * GEO detection using three different sources
 * Option to configure start chat fields
 * Sounds on pending chats and new messages
 * Google chrome notifications on pending messages.
 * Browser title blinking then there is pending message.
 * Option to limit pro active chat invitation messages based on pending chats.
 * Option to configure frequency for pro active chat invitation message. You can set after how many hours for the same user invitation message should be shown again.
 * Users blocking
 * Top performance with enabled cache
 * Windows, Linux and Mac native applications.
 * Advanced embed code generation with numerous options of includable code.
 * Template override system
 * Module override system
 * Support for custom extensions
 * Changeable footer and header content
 * Option to send messges to anonymous site visitors,
 * Canned messages
 * Informing then operator or user is typing.
 * Option to see what user is typing before he sends a message
 * Canned messages for desktop client
 * Voting module
 * FAQ module
 * Online users map
 * Pro active chat invitatio
 * Remember me functionality
 * Total pageviews tracking
 * Total pageviews including previous visits tracking
 * Visits tracking, how many times user has been on your page.
 * Time spent on site
 * Auto responder
 * BB Code support. Links recognition. Smiles and few other hidden features :)
 * First user visit tracking
 * Option for customers mute sounds 
 * Option for operators mute messages sounds and new pending chat's sound.
 * Option to monitor online operators.
 * Option to have different pro active messages for different domains. This can be archieved using different identifiers.
 * Dekstop client supports HTTPS
 * Protection against spammers using advanced captcha technique without requiring users to enter any captcha code.
 * Option for operator set online or offline mode.
 * Desktop client for
  * Windows
  * Linux 
  * Mac
 * Flexible permission system:
  * Roles
  * Groups
  * Users

Forum:
http://forum.livehelperchat.com/
