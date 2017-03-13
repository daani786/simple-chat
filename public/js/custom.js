function customformSubmit($formEle, redirectUrl, $image)
{
    //console.log('customformSubmit',customformSubmit);
    //console.log('formEle',$formEle);
    $formEle.submit(function( event ) {
        event.preventDefault();
        if ($formEle.parsley().validate()) {
        //if (true) {
            //submit form with ajax

            //var formData1= new FormData($formEle[0]);
            var formData = $formEle.serializeArray();
            var ajaxUrl = $formEle.attr('action');
            var formDataObj = {};
            for (var i=0; i<formData.length; i++) {
                //console.log(formData[i]['name']);
                if (formDataObj[formData[i]['name']]) {
                    formDataObj[formData[i]['name']] = formDataObj[formData[i]['name']] + ',' +formData[i]['value'];
                } else {
                    formDataObj[formData[i]['name']] = formData[i]['value'];
                }
            }
            if ($image && $image.attr('src') !== '') {
                //console.log('$image',$image.cropper("getDataURL"));
                //console.log("$image.attr('data-field')",$image.attr('data-field'));
                formDataObj[$image.attr('data-field')] = $image.cropper("getDataURL");
            }

            $.ajax({
                method: "POST",
                url : ajaxUrl,
                data:  formDataObj,
                dataType: "json",
                beforeSend:function(data){
                    //console.log('beforeSend');
                },
                error:function(data){
                    //console.log('error');
                },
                complete:function(data){
                    //console.log('complete');
                },
                success:function(data) {
                    $formEle.parsley().reset();
                    //console.log('success');
                    //console.log('data',data);
                    if (data) {
                        if (data.success) {
                            //console.log('data.success');i
                            if (data.formType && data.formType != '' && data.formType == 'add') {
                                $formEle[0].reset();
                            }
                            if ($image && $image.attr('src') !== '') {
                                //console.log('after image');
                                $('#'+$image.attr('data-field')).attr('src', formDataObj[$image.attr('data-field')]);
                            }
                        } else {
                            //console.log('length',data.field_errors.length);
                            //if (data.field_errors && data.field_errors.length > 0) {
                            if (data.field_errors) {
                                //console.log('11');
                                $.each( data.field_errors, function( key, value ) {
                                    var specificField = $formEle.find('#'+key).parsley();
                                    //window.ParsleyUI.removeError(specificField, "myCustomError");
                                    window.ParsleyUI.addError(specificField, "myCustomError", value);
                                    $('#'+key).focus();
                                });
                            }
                        }
                        if (data.form_msg && data.form_msg != '') {
                            //console.log('$formEle',$formEle);
                            $formEle.parent().find('#resp-msg .msg').html('').html(data.form_msg);
                            $formEle.parent().find('#resp-msg').removeClass('alert-danger').removeClass('alert-success').removeClass('hidden').addClass(data.form_msg_class).show().fadeTo(3000, 500).slideUp(1000);
                            //$('#resp-msg .msg').html('');
                            if (data.success && redirectUrl && redirectUrl != '') {
                                //console.log('data.success');
                                setTimeout(function() {
                                    window.location = redirectUrl;
                                }, 1000);
                            }
                        }
                        var top = $formEle.position().top;
                        $(window).scrollTop( top );
                    }
                }
            });
        }
    });
}