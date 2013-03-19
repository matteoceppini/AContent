/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$('#label_title').live("click",function(){
     $('#search_content').css('display','inline');
     $('#function').css('display','none');
     $('#button-next').css('display','none');
     $('#info_struct').css('display','none');
     
});

//Command wizard template back
var app=location.pathname;
var ap=window.location.href;
var a=ap.split('/');
var path=a[0];
var cont=1;

while(cont!=a.length-3){
    path= path + "/" + a[cont];
    cont++;
}
$('#lab_back').live("click",function(){
    var course_id = $("#lab_back").attr('name');
    var cid = $("#l_back").attr('name');
    location.href= path+'/home/editor/edit_content_wizard_step3.php?_course_id='+course_id+'&_content_id='+cid;
});

//Command wizard Delete
$('input[id="d_confirm"]').live("click",function(){
    $('input[id="d_confirm"]').css('display','none');
    $('input[id="delete"]').css('display','inline');
    $('#second_call').css('display','inline');
    $('#first_call').css('display','none');
});


// Commands for page edit_content_wizard_step2.tmpl.php
$('input[id="radio-folder"]').live("click",function(){
    $('#goals_title').css('display','none');
    $('#goals_knowledge').css('display','none');
    $('#goals_metacompetency').css('display','none');
    $('#goals_creative').css('display','none');
    $('#no_golas_with_folder').css('display','inline');
});

$('input[id="radio-contentpage"]').live("click",function(){
    $('#goals_title').css('display','inline');
    $('#goals_knowledge').css('display','inline');
    $('#goals_metacompetency').css('display','inline');
    $('#goals_creative').css('display','inline');
    $('#no_golas_with_folder').css('display','none');
});


$('#label_create_page').live("click",function(){
    $('#ftitle_page').css('display','inline');
    $('#step2_intro').css('display','none');
    $('#step2_body').css('display','inline');
    $('#buttons_body').css('display','inline');
    $('#buttons_intro').css('display','none');
    $('#hidd_folder').remove();
});

$('#label_create_folder').live("click",function(){
    $('#ftitle_folder').css('display','inline');
    $('#step2_intro').css('display','none');
    $('#step2_body').css('display','inline');
    $('#buttons_body').css('display','inline');
    $('#buttons_intro').css('display','none');
    $('#hidd_page').remove();
});






// Commands for page edit_content_wizard_layout.tmpl.php

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
var app=location.pathname;
var ap=window.location.href;
var a=ap.split('/');
var path=a[0];
var cont=1;


while(cont!=a.length-3){
    path= path + "/" + a[cont];
    cont++;
}
          
var layout_click;


$('input[id="apply_layout_to_content"]').live("click",function(){
    $('input[id="radio-'+layout_click+'"]').removeAttr('disabled');  
});

function preview(lay){

   $('#newLayoutTemplate').remove();

   $('input[id="radio-'+layout_click+'"]').removeAttr('disabled'); 

   $('input[id="radio-'+lay+'"]').attr('checked','checked');
   $('input[id="radio-'+lay+'"]').attr('disabled','disabled');

    var cid = $("#radio_"+lay).attr('name');

    addLayoutTemplate(cid,lay);
    layout_click=lay;
    
}


function addLayoutTemplate(cid,layout){

    var url =path + "/templates/system/AJAX_actions.php";
//alert(url);
    $.post(url, {content: cid}, function(structure){
        $('#content').append(createLayoutTemplate(layout,structure));
    });
    
    $('#newLayoutTemplate').fadeIn(300);
}   

function createLayoutTemplate(layout,structure)
{
    while($('#newLayoutTemplate').length !=0){
        $('#newLayoutTemplate').remove();
    }

    layout_template='<div id="newLayoutTemplate" style="margin: 10px; margin-bottom: 15px;">';
    if(layout!="nothing"){
        if(structure.length>0){
            layout_template= layout_template + 'Preview ' + layout + ':';
            layout_template= layout_template + '<link rel="stylesheet" href="'+path+'/templates/layout/'+layout+'/'+layout+'.css" type="text/css" />';
            layout_template= layout_template + '<p>'+structure+'</p>';
        }else{
                layout_template= layout_template + '<p>Contained no text, the following are two examples containing three different templates.</p>';
                layout_template= layout_template + 'Preview ' + layout + ':';
                layout_template= layout_template + '<link rel="stylesheet" href="'+path+'/templates/layout/'+layout+'/'+layout+'.css" type="text/css" />';
                layout_template= layout_template + '<div id="_header_0"><div id="_header_1">';
                layout_template= layout_template + '<h1>Obiettivi</h1></div></div><p>Il pensiero divergente (apprendimenti superiori divergenti). Analisi (analizzare, confrontare, indurre), sintesi (sintetizzare, schematizzare, dedurre), intuizione (tentare soluzioni, formulare ipotesi), invenzione. </p><ul><li>tentare soluzioni ...</li><li>formulare ipotesi ...</li><li>riconoscere problemi chiave ...</li><li>generare ...</li><li>pianificare ...</li><li>produrre soluzioni nuove ...</li><li>formulare soluzioni nuove ...</li></ul><br>';
                layout_template= layout_template + '<div><h1> Tipologia "creative based" </h1><p> La struttura/modello di tipo "creative" è centrata sulle motivazioni ed "emozioni" del soggetto che apprende. Questo tipo di LO persegue l&acute;attivazione di competenze non facilmente misurabili con procedure docimologiche oggettivanti proprio perché scarsamente predefinibili e fortemente connesse con la sfera dell&acute;individualità. Tra queste, la capacità di decentramento culturale, la disponibilità ad assumere punti di vista differenti, di attivare le forme del cosiddetto pensiero creativo ecc. Le modalità didattiche privilegiate riprendono il patrimonio formativo dell&acute;animazione culturale: consistono in strategie, anche provocatorie, di stimolazione nello studente di riflessioni che vanno oltre (precedono, accompagnano, seguono) il piano della competenza oggettiva e dell&acute;abilità professionale per toccare la sfera del significato personale assunto da "quel" sapere per il soggetto apprendente. Tali riflessioni costituiscono comunque un quadro di competenze determinante in quanto pre-condizionano in modo anche inconsapevole l&acute;atteggiamento dello studente nei confronti dell&acute;apprendimento e contribuiscono a definirne la qualità effettiva. </p></div>'; 
        }
    }else{
        if(structure.length>0){
                layout_template= layout_template + 'Preview ' + layout + ':';          
                layout_template= layout_template + '<p>'+structure+'</p>';
            }else{
                layout_template= layout_template + '<p>Contained no text, the following are two examples containing three different templates.</p>';
                layout_template= layout_template + 'Preview ' + layout + ':';
                layout_template= layout_template + '<link rel="stylesheet" href="'+path+'/templates/layout/'+layout+'/'+layout+'.css" type="text/css" />';
                layout_template= layout_template + '<div id="_header_0"><div id="_header_1">';
                layout_template= layout_template + '<h1>Obiettivi</h1></div></div><p>Il pensiero divergente (apprendimenti superiori divergenti). Analisi (analizzare, confrontare, indurre), sintesi (sintetizzare, schematizzare, dedurre), intuizione (tentare soluzioni, formulare ipotesi), invenzione. </p><ul><li>tentare soluzioni ...</li><li>formulare ipotesi ...</li><li>riconoscere problemi chiave ...</li><li>generare ...</li><li>pianificare ...</li><li>produrre soluzioni nuove ...</li><li>formulare soluzioni nuove ...</li></ul><br>';
                layout_template= layout_template + '<div><h1> Tipologia "creative based" </h1><p> La struttura/modello di tipo "creative" è centrata sulle motivazioni ed "emozioni" del soggetto che apprende. Questo tipo di LO persegue l&acute;attivazione di competenze non facilmente misurabili con procedure docimologiche oggettivanti proprio perché scarsamente predefinibili e fortemente connesse con la sfera dell&acute;individualità. Tra queste, la capacità di decentramento culturale, la disponibilità ad assumere punti di vista differenti, di attivare le forme del cosiddetto pensiero creativo ecc. Le modalità didattiche privilegiate riprendono il patrimonio formativo dell&acute;animazione culturale: consistono in strategie, anche provocatorie, di stimolazione nello studente di riflessioni che vanno oltre (precedono, accompagnano, seguono) il piano della competenza oggettiva e dell&acute;abilità professionale per toccare la sfera del significato personale assunto da "quel" sapere per il soggetto apprendente. Tali riflessioni costituiscono comunque un quadro di competenze determinante in quanto pre-condizionano in modo anche inconsapevole l&acute;atteggiamento dello studente nei confronti dell&acute;apprendimento e contribuiscono a definirne la qualità effettiva. </p></div>'; 
        }
    }
    layout_template =layout_template + '</div>';

    return layout_template;
}




