/**
 * Created by etema on 13/03/2016.
 */
$( document ).ready( function () {
    $("#booking").validate({
        rules: {
            username: {
                required: true,
                minlength: 2,
                lettersonly:true
            },
            room:{required:true},
            datetimepickerinput:{required:true},
            lengthtime:{required:true}
        },
        messages: {
            username: {
                required: "Please enter a username",
                minlength: "Please enter at least 2 letters for Username"
            },
            room: "Please select a room",
            datetimepickerinput:"Please pick booking date",
            lengthtime:"Please select booking duration"
        },
        highlight: function(element, errorClass, validClass) {
            console.log(element);
            $(element).closest('.form-group').addClass('has-error');
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).closest('.form-group').removeClass('has-error');
        },
        errorElement: "span",
        errorPlacement: function (error, element) {
            // RE override default error driving to cover input-group elements
            error.addClass("help-block");

           if(element.parent('.input-group').length){
               error.insertAfter(element.parent());
           }else{
               error.insertAfter(element);
           }
        },
        submitHandler: function(form){
            $("#book").attr("disabled",true);
            //e.preventDefault();

            $('#book').html('Loading...');
            $.post('./control.php',
                {
                    op: 'record',
                    start_time: $('#datetimepickerinput').val(),
                    end_time: $('#lengthtime').val(),
                    username: $('#username').val(),
                    room_id: $('#room').attr('room-code')
                }, function (data) {
                    if( typeof data.error == 'undefined' && typeof data.username == 'undefined' ){
                        $('#alert').css({'color':'green'});
                        $('#alert').html('This Booking has been successfully made for you.');
                        $('#booking')[0].reset();

                    }else if( typeof data.error != 'undefined' ){
                        $('#alert').css({'color':'red'});
                        $('#alert').html(data.error);
                    }else{
                        var msg = 'Sorry! This booking has been made by ' +data.username + '<br>' +
                                'Booking info: From ' + data.start_time + ' since '+data.end_time;

                        $('#alert').css({'color':'blue'});
                        $('#alert').html(msg);
                    }

                    $('#book').html('Book!');
                    $("#book").removeAttr("disabled");
                }, 'json');

        }
    });
    //initialize dateTimePicker
    $('#datetimepicker').datetimepicker({
        sideBySide: true,
        stepping:15
    });
    //initialize toolTip
    $('[data-toggle="tooltip"]').tooltip();

});


/* inline list population */
jQuery(function($){
    function search(object){
        for(var i=0; i<=object.length-1; i++){
            if( object[i]['key-name'] == 'title' ) return i;
        }

        return -1;
    }
    $.fn.productSuggestion = function(){

        if( !this.is('input') ) return false;

        $(this).attr('autocomplete', 'off');

        //First get list id
        var listID = typeof $(this).attr('list') != 'undefined' ? $(this).attr('list') : '';

        if( listID == '' ){
            var randomID = 'datalist' + Math.floor( (Math.random()+1)*200 );
            var s = '<select id="' + randomID + '" multiple="" do-not-apply="true" style="display:none; heigth: 200px; z-index:999999; width: 500px;"></select>'

            $(s).appendTo('body');

            $(this).attr('list', randomID);
            listID = randomID;
        }

        var allowHide = false;
        var parentID = $(this);

        //Limit selected option
        $('#' + listID).on("click", "option", function () {
            if ( 1 <= $(this).siblings(":selected").length ) {
                $(this).removeAttr("selected");
            }
        });

        $('#' + listID).on('mousedown', function () {
            allowHide = false;
        }).on('mouseup', function () {
            allowHide = true;
            $(parentID).val( $('#' + listID + ' option:selected').attr('room-name') );
            $(parentID).attr('room-code', $('#' + listID).val() );

            $('#' + listID).css({'display':'none'})

        });

        $(this).on('keyup', function (e) {
            if( $(this).val().length < 2 ) return false;

            $.ajax('/control.php',{
                async:true,
                method: 'POST',
                data:{
                    op:'suggestion',
                    q: $(this).val()
                },
                dataType:'json',//json
                success:function(data){
                    $('#' + listID).html('');
                    for(var i=0; i<=data.length-1; i++){
                        var title = data[i]['title'];
                        var des = data[i]['des'];
                        var pic = data[i]['pic'];

                        var op = document.createElement('option');
                        op.value = data[i]['id'];
                        op.text = title + ': ' + des.substr(0, 40) + '...';
                        $(op).attr('room-name', title);
                        //$(op).attr('style', 'background-image: url(.' + data[i]['pic'] + '); background-repeat: no-repeat');

                        $(op).appendTo('#' + listID);

                    }
                }
            });

        }).on('change', function (e) {

        }).focus(function () {
            $('#'+listID).css({'display':'block'})

            //Set position
            //Get Width
            var w = $(this).width() + 200;
            var t = $(this).offset().top + ($(this).height()+$(this).height());
            var l = $(this).offset().left;

            $('#' + listID).css({'width': w + 'px', position:'fixed', top: t + 'px', left: l + 'px'});
            allowHide = true;

        }).focusout(function () {
            if( allowHide )
                $('#'+listID).css({'display':'none'})
        })

    }
});

