(window.webpackJsonpLiveHelperChat=window.webpackJsonpLiveHelperChat||[]).push([[3],{30:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.activityMonitoring=void 0;var s=function(){function t(t,e){for(var i=0;i<e.length;i++){var s=e[i];s.enumerable=s.enumerable||!1,s.configurable=!0,"value"in s&&(s.writable=!0),Object.defineProperty(t,s.key,s)}}return function(e,i,s){return i&&t(e.prototype,i),s&&t(e,s),e}}(),n=i(2),o=i(1);var a=new(function(){function t(){!function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,t),this.params={},this.timeoutStatuscheck=null,this.timeoutActivity=null,this.attributes=null,this.userActive=1}return s(t,[{key:"attatchActivityListeners",value:function(){var t=this;if(this.params.track_activity){var e=function(){t.resetTimeoutActivity()};this.params.track_mouse&&(n.domEventsHandler.listen(window,"mousemove",e,"lhc_mousemove_w"),n.domEventsHandler.listen(document,"mousemove",e,"lhc_mousemove_d")),n.domEventsHandler.listen(window,"mousedown",e,"lhc_mousedown"),n.domEventsHandler.listen(window,"click",e,"lhc_click"),n.domEventsHandler.listen(window,"scroll",e,"lhc_scroll"),n.domEventsHandler.listen(window,"keypress",e,"lhc_keypress"),n.domEventsHandler.listen(window,"load",e,"lhc_load"),n.domEventsHandler.listen(document,"scroll",e,"lhc_scroll"),n.domEventsHandler.listen(document,"touchstart",e,"lhc_touchstart"),n.domEventsHandler.listen(document,"touchend",e,"lhc_touchend"),this.resetTimeoutActivity()}}},{key:"resetTimeoutActivity",value:function(){var t=this,e=0==this.userActive;this.userActive=1,1==e&&this.syncUserStatus(1),clearTimeout(this.timeoutActivity),this.timeoutActivity=setTimeout((function(){t.userActive=0,t.syncUserStatus(1)}),3e5)}},{key:"setParams",value:function(t,e){this.params=t,this.attributes=e,this.attatchActivityListeners(),this.initMonitoring()}},{key:"initMonitoring",value:function(){var t=this;clearTimeout(this.timeoutStatuscheck),this.timeoutStatuscheck=setTimeout((function(){t.syncUserStatus(0),t.initMonitoring()}),1e3*this.params.timeout)}},{key:"syncUserStatus",value:function(t){var e=this,i=this.attributes.userSession.getSessionAttributes(),s={vid:this.attributes.userSession.getVID(),wopen:this.attributes.widgetStatus.value?1:0,uaction:t,uactiv:this.userActive,dep:this.attributes.department.join(",")};i.id&&i.hash&&(s.hash=i.id+"_"+i.hash),o.helperFunctions.makeRequest(this.attributes.LHC_API.args.lhc_base_url+this.attributes.lang+"widgetrestapi/chatcheckstatus",{params:s},(function(t){1==t.change_status&&e.attributes.onlineStatus.value!=t.online&&e.attributes.onlineStatus.next(t.online)}))}}]),t}());e.activityMonitoring=a}}]);
//# sourceMappingURL=0db6b2b6ed9488eaf32d.js.map