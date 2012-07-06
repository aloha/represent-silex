$('.editplace').bind('click', function(){
   console.log(this);
});


// add modal form submit
$("#modal_editform").submit(function(event) {
    event.preventDefault(); 
    // get values
    var $form = $( this ),
    owner_name = $form.find( '#edit_owner_name' ).val(),
    owner_email = $form.find( '#edit_owner_email' ).val(),
    title = $form.find( '#edit_title' ).val(),
    type = $form.find( '#edit_type' ).val(),
    address = $form.find( '#edit_address' ).val(),
    uri = $form.find( '#edit_uri' ).val(),
    description = $form.find( '#edit_description' ).val(),
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
            $("#modal_editform #result").html("Place successfully edited."); 
            $("#modal_editform #result").addClass("alert alert-info");
            $("#modal_editform p").css("display", "none");
            $("#modal_editform fieldset").css("display", "none");
            $("#modal_editform .btn-primary").css("display", "none");
              
        // if submission failed, show error
        } else {
            $("#modal_editform #result").html(data); 
            $("#modal_editform #result").addClass("alert alert-danger");
        }
    }
    );
});