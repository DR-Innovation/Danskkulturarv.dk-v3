var tinyMCEPreInit;var wpActiveEditor;(function(c){function d(g){c("#"+g).addClass("mceEditor");if(typeof tinyMCE=="object"&&typeof tinyMCE.execCommand=="function"){b(g);tinyMCEPreInit.mceInit[g]=tinyMCEPreInit.mceInit["black-studio-tinymce-widget"];tinyMCEPreInit.mceInit[g]["selector"]="#"+g;try{tinymce.init(tinymce.extend({},tinyMCEPreInit.mceInit["black-studio-tinymce-widget"],tinyMCEPreInit.mceInit[g]));tinyMCE.execCommand("mceAddControl",false,g)}catch(f){alert(f)}}}function b(g){if(typeof tinyMCE=="object"&&typeof tinyMCE.execCommand=="function"){if(typeof tinyMCE.get(g)=="object"&&typeof tinyMCE.get(g).getContent=="function"){var f=tinyMCE.get(g).getContent();tinyMCE.get(g).remove();c("textarea#"+g).val(f)}}}function a(f){c("div.widget-inside:has(#"+f+") input[id^=widget-black-studio-tinymce][id$=type][value=visual]").each(function(){if(c("div.widget:has(#"+f+") :animated").size()==0&&typeof tinyMCE.get(f)!="object"&&c("#"+f).is(":visible")){c("a[id^=widget-black-studio-tinymce][id$=visual]",c(this).closest("div.widget-inside")).click()}else{if(typeof tinyMCE.get(f)!="object"){setTimeout(function(){a(f);f=null},100)}else{c("a[id^=widget-black-studio-tinymce][id$=visual]",c(this).closest("div.widget-inside")).click()}}})}function e(f){c("div.widget-inside:has(#"+f+") input[id^=widget-black-studio-tinymce][id$=type][value=visual]").each(function(){if(c.active==0&&typeof tinyMCE.get(f)!="object"&&c("#"+f).is(":visible")){c("a[id^=widget-black-studio-tinymce][id$=visual]",c(this).closest("div.widget-inside")).click()}else{if(c("div.widget:has(#"+f+") div.widget-inside").is(":visible")&&typeof tinyMCE.get(f)!="object"){setTimeout(function(){e(f);f=null},100)}}})}c(document).ready(function(){c(document).on("click","div.widget:has(textarea[id^=widget-black-studio-tinymce]) .widget-title, div.widget:has(textarea[id^=widget-black-studio-tinymce]) a.widget-action",function(g){var i=c(this).closest("div.widget");var h=c("textarea[id^=widget-black-studio-tinymce]",i);c("input[name=savewidget]",i).on("click",function(j){var l=c(this).closest("div.widget");var k=c("textarea[id^=widget-black-studio-tinymce]",l);if(typeof tinyMCE.get(k.attr("id"))=="object"){b(k.attr("id"))}c(this).unbind("ajaxSuccess").ajaxSuccess(function(n,o,m){var p=c("textarea[id^=widget-black-studio-tinymce]",c(this).closest("div.widget-inside"));e(p.attr("id"))})});c("#wpbody-content").css("overflow","visible");i.css("position","relative").css("z-index","100");a(h.attr("id"));c(".insert-media",i).data("editor",h.attr("id"))});c("div.widget[id*=black-studio-tinymce] input[name=savewidget]").on("click",function(g){var i=c(this).closest("div.widget");var h=c("textarea[id^=widget-black-studio-tinymce]",i);if(typeof tinyMCE.get(h.attr("id"))=="object"){b(h.attr("id"))}c(this).unbind("ajaxSuccess").ajaxSuccess(function(k,l,j){var m=c("textarea[id^=widget-black-studio-tinymce]",c(this).closest("div.widget-inside"));e(m.attr("id"))})});c(document).on("click","a[id^=widget-black-studio-tinymce][id$=visual]",function(g){var h=c(this).closest("div.widget-inside,div.panel-dialog");c("input[id^=widget-black-studio-tinymce][id$=type]",h).val("visual");c(this).addClass("active");c("a[id^=widget-black-studio-tinymce][id$=html]",h).removeClass("active");d(c("textarea[id^=widget-black-studio-tinymce]",h).attr("id"))});c(document).on("click","a[id^=widget-black-studio-tinymce][id$=html]",function(g){var h=c(this).closest("div.widget-inside,div.panel-dialog");c("input[id^=widget-black-studio-tinymce][id$=type]",h).val("html");c(this).addClass("active");c("a[id^=widget-black-studio-tinymce][id$=visual]",h).removeClass("active");b(c("textarea[id^=widget-black-studio-tinymce]",h).attr("id"))});c(document).on("click",".editor_media_buttons a",function(){var g=c(this).closest("div.widget-inside");wpActiveEditor=c("textarea[id^=widget-black-studio-tinymce]",g).attr("id")});if(c("body.widgets_access").size()>0){var f=c("textarea[id^=widget-black-studio-tinymce]");a(f.attr("id"))}})})(jQuery);