(function($){
   
    $(document).ready(function(){

        if( curlStatus === 'off' ){
            $('#miraget-noCurl').css('display','inline-block');
        }

        if( miragetLeadsToken === "error"){
            
            const encodeUri = encodeURIComponent(urlInfo) ;
            const apiUrl = "https://miraget.com/api/?url=" + encodeUri;

            console.log( "Starting ..." );
 
            $('#re-mi-to').css('visibility','visible') ;

            var jqxhr = $.get( apiUrl, function() { })
            .done(function(d) {
                
                if( typeof d === "object"){

                    if( d.hasOwnProperty('Token')){
                         
                        $('#api_key').val(d.Token) ;
                        $('#miraget_update_token').trigger( "click" );

                    }
                }

                $('#re-mi-to').css('visibility','hidden') ;

            })
            .fail(function(e) {
               
               console.log("Error: ",e)
               $('#re-mi-to').css('visibility','hidden') ;

            })
            .always(function() {
                
            });
        }
    })

})(jQuery) ;