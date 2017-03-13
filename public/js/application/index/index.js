var chatting = {
    params: {
        container : null,
        outputEle : null,
        loadingEle : null,
        formEle : null,
        debug : false
    },
    showDebug : function(str) {
        if (this.params.debug) {
            console.log(str);
        }
    },
    showLoading: function() {
        this.showDebug('showLoading');
        this.params.loadingEle.show();
        this.params.outputEle.hide();
    },
    hideLoading: function() {
        this.showDebug('hideLoading');
        this.params.loadingEle.hide();
        this.params.outputEle.show();
    },
    reset: function() {
        var that = this;
        this.showDebug('reset');
    },
    events:function() {
        this.showDebug('events');
        var that = this;
        this.customformSubmit(this.params.formEle);
    },
    init:function(json) {
        this.showDebug('init');
        this.params.container = $('div.container');
        this.params.loadingEle = $('div.container img#loading');
        this.params.outputEle = $('div.container tbody#result');
        this.params.formEle = $('div.container form#chat');

        this.events();
        this.loadChatRecords();
    },
    formReset: function() {
        this.params.formEle.get(0).reset();
    },
    loadChatRecords: function() {
        var that = this;
        this.showDebug('showResult');
        $.ajax({
            method: "POST",
            data: {},
            dataType: "json",
            beforeSend:function(){
                that.showLoading();
            },
            error:function(){
            },
            complete:function(){
                that.hideLoading();
            },
            success:function(data) {
                that.showDebug('data',data);
                if (data && data.status == true) {
                    var totalRec = data.result.length;
                    if (totalRec && totalRec > 0) {
                        html = '';
                        for (var i=0; i < totalRec; i++) {
                            html += '<tr>';
                            html += '<td>'+data.result[i]['doc']+'</td>';
                            html += '<td>'+data.result[i]['message']+'</td>';
                            html += '</tr>';
                        }
                        that.params.outputEle.html(html);
                    }
                }
            }
        });
    },
    customformSubmit ($formEle) {
        var that = this;
        this.showDebug('customformSubmit');
        $formEle.submit(function( event ) {
            event.preventDefault();
            if ($formEle.parsley().validate()) {
                var formData = $formEle.serializeArray();
                var ajaxUrl = $formEle.attr('action');
                var formDataObj = {};
                for (var i=0; i<formData.length; i++) {
                    if (formDataObj[formData[i]['name']]) {
                        formDataObj[formData[i]['name']] = formDataObj[formData[i]['name']] + ',' +formData[i]['value'];
                    } else {
                        formDataObj[formData[i]['name']] = formData[i]['value'];
                    }
                }
                formDataObj['type'] = 'add';
                $.ajax({
                    method: "POST",
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
                        if (data) {
                            if (data.success) {
                                if (data.formType && data.formType != '' && data.formType == 'add') {
                                    $formEle[0].reset();
                                }
                            } else {
                                if (data.field_errors) {
                                    $.each( data.field_errors, function( key, value ) {
                                        var specificField = $formEle.find('#'+key).parsley();
                                        //window.ParsleyUI.removeError(specificField, "myCustomError");
                                        window.ParsleyUI.addError(specificField, "myCustomError", value);
                                        $('#'+key).focus();
                                    });
                                }
                            }
                            if (data.form_msg && data.form_msg != '') {
                                $formEle.parent().find('#resp-msg .msg').html('').html(data.form_msg);
                                $formEle.parent().find('#resp-msg').removeClass('alert-danger').removeClass('alert-success').removeClass('hidden').addClass(data.form_msg_class).show().fadeTo(3000, 500).slideUp(1000);
                                that.formReset();
                                that.loadChatRecords();
                            }
                            var top = $formEle.position().top;
                            $(window).scrollTop( top );
                        }
                    }
                });
            }
        });
    }
};
chatting.init();