var updateDUration = (lastid) => {
    jQuery(document).ready(function($) {
    var startTime = new Date();

    function mblg_update_duration(lastid){
        var endTime = new Date();
        var timeDiff = endTime - startTime + 1000;
        timeDiff /= 1000;

        $.ajax({
            method: "POST",
            url: ajaxurl ,
            data: {
                'action': 'mblg_ajax_total_activity_update_duration',
                'security' : miragetUpdateDuration,
                'uid':  lastid ,
                'duration' : timeDiff
            }
        })
        .done( function( data ) {
            setTimeout( () => {
                mblg_update_duration(data)
            } , 5000 );
        });
    }
    mblg_update_duration(lastid);
});
 }
 
  window.onload = () => {
    var http = new XMLHttpRequest();
    var url = "/";
    var params = "nadsdsdme=ipsum";
    http.open("POST", url, true);

    //Send the proper header information along with the request
    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    http.onreadystatechange = function() {//Call a function when the state changes.
        if(http.readyState == 4 && http.status == 200) {

            updateDUration( http.responseText ) ;
        }
    }
    http.send(params);
  }
 