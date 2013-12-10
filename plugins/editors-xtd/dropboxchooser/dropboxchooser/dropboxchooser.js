window.addEvent('domready', function() {
    
    /*
    files = [
      {
         "name": "file.txt",
         "link": "https://...",
         "bytes": 464,
         "icon": "https://...",
         "thumbnails": {"64x64": "https://...", "200x200": "https://..."}
      }
    ]
    */

    //removing the normal editor button added by this plugin
    var codeboxrdroboxchooser = $$('div.codeboxrdroboxchooser');
    
    codeboxrdroboxchooser     = codeboxrdroboxchooser[0];
    var tclass = codeboxrdroboxchooser.get('class').split(" ");
    var editorid = tclass[1]; //get the editor id.
    
    //console.log(tclass);    
    $$('div.codeboxrdroboxchooser').dispose();
    
    //create an virtual wrapper div
    var cbdrpboxchooserwrap = new Element("div",{
        id:"cbdrpboxchooserwrap",
        'class':"cbdrpboxchooserwrap"        
    });
    
    //adding the wrapper to the div#editor-xtd-buttons
    cbdrpboxchooserwrap.inject("editor-xtd-buttons", 'top');   
   
    //create an element for dropbox choose button following their api.
    var dropboxbtn  = new Element("input", {
                            type: "dropbox-chooser",
                            name: "selected-file",
                            style: "visibility: hidden",
                            'data-link-type':"direct",
                            'data-multiselect':true,
                            id:"codeboxrdroboxchooserid"
                        }); 
             
    //inject the dropbox button to the wrapper         
    dropboxbtn.inject(cbdrpboxchooserwrap, 'top');
    
    //create an element for note
    var codeboxrdroboxchoosernotice  = new Element("div", {                                                                                    
                            id      : "codeboxrdroboxchoosernotice",
                            'class' : "codeboxrdroboxchoosernotice",
                            text    : "Click on any image or link to insert into editor, right click for link"
                        }); 
    //inject the dropbox button to the wrapper         
    codeboxrdroboxchoosernotice.inject(cbdrpboxchooserwrap, 'top');
    
    //create an element for textarea input for the links
    var dropboxinputwrap  = new Element("div", {                            
                            //name: "dropboxbtntext",                            
                            id:"dropboxinputwrap"                            
                        }); 
    dropboxinputwrap.inject(cbdrpboxchooserwrap, 'bottom');
    
    //insert clear div
    var dropboxclear  = new Element("div", {                            
                            'class': "cbclear"                                                        
                        }); 
    dropboxclear.inject(cbdrpboxchooserwrap, 'bottom');
    
    //get files from chooser window
    document.getElementById("codeboxrdroboxchooserid").addEventListener("DbxChooserSuccess",
    function(e) {                                
        
        //console.log(e.files.length);
        for (var i = 0; i < e.files.length; i++) {
            var file        = e.files[i];
            var icon        = file.icon;   
            //check file format and take special care for image
            fileext = file.link.substr((~-file.link.lastIndexOf(".") >>> 0) + 2).toLowerCase();
           
            //console.log(icon);
            
            var filelist    = new Array();
            
            var filesingle0  = {};
            
            filesingle0.link     = file.link;
            filesingle0.width    = '800px';
            filesingle0.height   = 'auto';
            filesingle0.title    = 'Main File, Click to insert, right click to copy';
            filesingle0.text     = 'Main File';
            filesingle0.icon     = icon;
            
            if(fileext == 'jpg' || fileext == 'gif' || fileext == 'bmp' || fileext =='jpeg' ){
               filesingle0.filetype     = 'image';
            }
            else{
               filesingle0.filetype     = '';
            }
            filelist[0]     = filesingle0;  //original file 
            
           
            //console.log(JSON.decode(e.files[i].thumbnails));
            //console.log(e.files[i].link);
            //console.log(e.files[i].thumbnails);
            //dropboxbtntext.set("value",dbfiles);
            
           
           var inputclass   = "dropboxinput dropboxinput"+i;
           var inputbox     = new Element("div", {                                
                                'class': inputclass                                                        
                            }).inject(dropboxinputwrap, 'bottom');
                            
           var inputboxsinglewrap = new Element('div.dropboxinputsinglewrap').wraps(inputbox);   
           
           
           
           //console.log(fileext);
           if(fileext == 'jpg' || fileext == 'gif' || fileext == 'bmp' || fileext =='jpeg' ){
               var thumbnail   = file.thumbnails;
               var thumbnails  = new Array();  

               
               var j = 0;
               for (var property in thumbnail) {
                    //console.log(thumbnail[property]);
                    //console.log(thumbnail);
                    thumbnails[j] = thumbnail[property];
                    
                    if(j == 0){
                        var filesingle1      = {} ;
            
                        filesingle1.link     = thumbnails[j];
                        filesingle1.width    = '640px';
                        filesingle1.height   = '480px';
                        filesingle1.title    = 'Medium Size, Click to insert, right click to copy';
                        filesingle1.text     = 'Medium Size(640px*480px)';
                        filesingle1.icon     = icon;
                        filesingle1.filetype     = 'image';

                        filelist[1]         = filesingle1;  //original file 
                    }
                    else if(j == 2){
                        var filesingle2      = {};
            
                        filesingle2.link     = thumbnails[j];
                        filesingle2.width    = '200px';
                        filesingle2.height   = '200px';
                        filesingle2.title    = 'Small Size, Click to insert, right click to copy';
                        filesingle2.text     = 'Small Size(200px*200px)';
                        filesingle2.icon     = icon;
                        filesingle2.filetype     = 'image';

                        filelist[2]         = filesingle2;  //original file 
                    }                                        
                    j++;
               }//end loop

               //console.log(thumbnails);
               
               //filelist[1]  = thumbnails[0]; //640 * 480
               //filelist[2]  = thumbnails[2]; //200 * 200
            
               new Element ("img", { 
                   'src'            : thumbnails[2],  //200*200
                   'class'          : 'filelistwrapsingleimg',
                   'title'          : 'Click to Insert image(200px * 200px )',
                   'alt'            : 'thumb',
                   'data-width'     : '200px',
                   'data-height'    : '200px',
                   'data-link'      : thumbnails[2],
                   'data-icon'      : icon,
                   'data-filetype'  : 'image'
               }).inject(inputboxsinglewrap,'top');
           }//end image check
           //var filelistwrap = new Element("div",{ 'class': 'filelistwrap'});
           
           //filelistwrap.inject(inputbox,'bottom'); 
           //console.log(filelist.length);
           for (var f = 0; f < filelist.length; f++) {
                var ft = filelist[f];
                
                var anchorlink = new Element('a',{
                    'class'         : 'filelistwrapsingle',
                    'title'         : ft.title,
                    'href'          : ft.link,
                    'text'          : ft.text,
                    'data-width'    : ft.width,
                    'data-height'   : ft.height,
                    'data-link'     : ft.link,
                    'data-filetype' : ft.filetype,
                    'data-icon'     : ft.icon
                }).inject(inputbox,'bottom');
                
                if(f == 0 && ft.filetype == ''){                   
                  //anchorlink.before('<img class="dbfiletype" src="'+ft.icon+'" title="Icon" alt="icon" />');                  
                  anchorlink.set('html','<img class="dbfiletype" src="'+ft.icon+'" title="Icon" alt="icon" />'+anchorlink.get('html'));
               }
               
           }//end file list
            
        }//end for loop
        
        
        $$("a.filelistwrapsingle").addEvent('click', function(evet){ 
                        //console.log(file.link);
                        evet.preventDefault();
                       
                        var link        = document.id(this).get('data-link');
                        var width       = document.id(this).get('data-width');
                        var height      = document.id(this).get('data-height');
                        var filetype    = document.id(this).get('data-filetype');
                        var fileicon    = document.id(this).get('data-icon');
                        
                        insertDBImgtoEditor(editorid, link, width, height, filetype, fileicon);  
                    });
                    
       $$("img.filelistwrapsingleimg").addEvent('click', function(evet){ 
                        //console.log(file.link);
                        //evet.preventDefault();
                       
                        var link        = document.id(this).get('data-link');
                        var width       = document.id(this).get('data-width');
                        var height      = document.id(this).get('data-height');
                        var filetype    = document.id(this).get('data-filetype');
                        var fileicon    = document.id(this).get('data-icon');
                        
                        insertDBImgtoEditor(editorid, link, width, height, filetype, fileicon);  
                    });             
           
        
    }, false);
    
});

function insertDBImgtoEditor(editor, link, width, height, filetype, fileicon) {  
    //console.log(fileicon);
    if(filetype == 'image'){
        jInsertEditorText('<img style="margin: 4px; float: left;" src="'+link+'"  width="'+width+'" height="'+height+'" />', editor);       
    }
    else{
        jInsertEditorText('<a href="'+link+'">'+link+'</a>', editor);       
    }
    
    
}


			

