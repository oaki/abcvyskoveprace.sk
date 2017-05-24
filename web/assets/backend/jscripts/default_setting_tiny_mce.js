tinyMCE.init({
    // General options
    mode  : "textareas",
    theme : "advanced",

    editor_selector : "mceEditor",
    width: "100%",

    plugins : "spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,filemanager",

    // Theme options
    theme_advanced_buttons1 : "bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,undo,redo,|,forecolor,bullist,numlist,|,outdent,indent,|,link,unlink,image,media,cleanup,coder,removeformat,visualaid",
    theme_advanced_buttons2 : "formatselect,pastetext,pasteword,tablecontrols,|,fullscreen,code",
    theme_advanced_buttons3 : "",

    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align    : "left",

    theme_advanced_resizing : false,

    language        : "sk",
    relative_urls   : false,
    entity_encoding : "raw"

});

