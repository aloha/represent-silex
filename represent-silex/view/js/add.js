$(document).ready(function(){
    // add modal form submit
    $("#modal_addform").submit(function(event) {
        event.preventDefault(); 
        // get values
        var $form = $( this ),
        owner_name = $form.find( '#add_owner_name' ).val(),
        owner_email = $form.find( '#add_owner_email' ).val(),
        title = $form.find( '#add_title' ).val(),
        type = $form.find( '#add_type' ).val(),
        address = $form.find( '#add_address' ).val(),
        uri = $form.find( '#add_uri' ).val(),
        description = $form.find( '#add_description' ).val(),
        url = $form.attr( 'action' );

        // send data and get results
        $.post( url, {
            owner_name: owner_name, 
            owner_email: owner_email, 
            title: title, 
            type: type, 
            address: address, 
            uri: uri, 
            description: description
        },
        function( data ) {
            var content = $( data ).find( '#content' );
            
            // if submission was successful, show info alert
            if(data == "success") {
                $("#modal_addform #result").html("We've received your submission and will review it shortly. Thanks!"); 
                $("#modal_addform #result").addClass("alert alert-info");
                $("#modal_addform p").css("display", "none");
                $("#modal_addform fieldset").css("display", "none");
                $("#modal_addform .btn-primary").css("display", "none");
              
            // if submission failed, show error
            } else {
                $("#modal_addform #result").html(data); 
                $("#modal_addform #result").addClass("alert alert-danger");
            }
        }
        );
    });
});