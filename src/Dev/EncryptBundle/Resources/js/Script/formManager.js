
var FormManager = (function() {
    "use strict";

    var txtAreaContent = document.createTextNode("");
    var labelProcessInProgress = 'Processus en cours.';
    var labelDownloadInProgress = 'Telechargement en cours.';
    var errorMsg = 'Erreur.';


    var addHtmlForm = function() {
        var that = this;

        $("#typeTaskList").change(function () {
            var value = $(this).val();
            var url   = Dev.config.url.formFactory.replace('===task===', value);

            $.ajax({
                type:"POST",
                url: url,
                success: function(html) {
                    document.getElementById('templateForm').innerHTML = html;
                    that.setEventSubmit();
                },
                error: function() {
                    console.log(that.errorMsg);
                }
            });
        });

        $("#typeTaskList").trigger("change");
    };


    var setEventSubmit = function() {
        var thisBis = this;
        var forms = document.querySelectorAll(".formContent form");
        for (var i = 0; i < forms.length; ++i) {
            var form = forms[i];
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                var form      = event.target;
                var idProcess = form.getAttribute('name');
                var formData  = new FormData(form);
                var urlUpl    = Dev.config.url.upload
                    .replace('===mode===', Dev.config.mode)
                    .replace('===task===', $('#typeTaskList').val())
                    .replace('===idProcess===', idProcess);

                document.querySelector('#previewResult').value = "";
                thisBis.initProcessAnimation();
                thisBis.statusTracking(idProcess, urlUpl, formData);
            });
        }
        return false;
    };

    var updateProgressBar = function(value) {
        var progress = document.querySelector('#progressBarContent div#defaultProgress');
        if (null !== progress) {
            progress.style.width = value + "%";
        }
    };

    var scrollPreview = function() {
        var preview = document.querySelector('#previewResult');
        preview.scrollTop = preview.scrollHeight;
    };

    var replaceClass = function(oldClassName, newClassName) {
        $("." + oldClassName).addClass(function() {
            $(this).removeClass(oldClassName);
            return newClassName;
        });
    };

    var initProcessAnimation = function() {
        this.replaceClass("border-primary-color", "border-encode-color");
        this.replaceClass("bg-primary-color", "bg-encode-color");
    };

    var afterServerProcessEnd = function() {
        this.replaceClass("border-encode-color", "border-intermediate-color");
        this.replaceClass("bg-encode-color", "bg-intermediate-color");
        $("#progressBarContent").show('speed');
    };

    var afterClientProcessEnd = function() {
        this.replaceClass("border-intermediate-color", "border-primary-color");
        this.replaceClass("bg-intermediate-color", "bg-primary-color");
    };

    var addTextTopreviewResult = function(str, forceReflow) {
        forceReflow = (undefined === forceReflow || null === forceReflow) ? false : forceReflow;
        this.txtAreaContent.appendData(str);

        if (forceReflow) {
            var previewResult = document.querySelector('#previewResult');
            //previewResult.appendChild(txtAreaContent);  //@todo
            previewResult.value += txtAreaContent.textContent;
            $("#previewSize").html(previewResult.value.length);
            this.txtAreaContent.deleteData(0, this.txtAreaContent.textContent.length);
        }
    };

    var statusTracking = function (idProcess, urlUpl, formData) {
        var thisBis = this;
        var submitBtn = document.querySelector('.divSubmmit input[type=submit]');
        var submitBtnInitalValue = submitBtn.value;
        submitBtn.value = thisBis.labelProcessInProgress;

        var params = {
            isProgressBarUpdated : false,
            lastLoaded : 0,
            cmpt : 0,
            submitBtn : submitBtn,
            submitBtnInitalValue : submitBtnInitalValue
        };

        $.ajax({
            type: "POST",
            mimeType: "multipart/form-data",
            data: formData,
            url: urlUpl,
            processData: false,
            contentType: false,
            async : true,
            cache : false,
            xhr: function () {
                var xhr = $.ajaxSettings.xhr();
                thisBis.handleProgress(params, xhr);
                return xhr;
            }
        }).done(function () {
            console.log('done');
            thisBis.addTextTopreviewResult("", true);
            submitBtn.value = submitBtnInitalValue;
            thisBis.updateProgressBar(100);
            thisBis.afterClientProcessEnd();
        }).fail(function (e) {
            console.log('fail');
            console.log(e);
        });
    };

    var handleProgress = function (params, xhr) {
        var thisBis = this;
        xhr.onprogress = function() {
            var response = xhr.responseText;
            response = response.toString();
            response = response.substr(params.lastLoaded);
            params.lastLoaded += response.length;

            document.querySelector('.progressionContent div.uploadStatus').innerHTML = response.substr(0, 100);
            if ("" === response || "#" !== response.substr(3, 1) || "#" !== response.substr(7, 1)) {
                thisBis.addTextTopreviewResult(response, true);
                return;
            }

            var status   = response.substr(0, 3);
            var progress = response.substr(4, 3);
            var data     = response.substr(8);
            response = {
                status   : ("ko" === status) ? status : parseInt(status),
                progress : parseInt(progress),
                data     : data
            };

            console.log("status=" + status + "  prog="+progress + "  length=" + response['data'].length);
            thisBis.addTextTopreviewResult(response.data, 0 === params.cmpt % 10);
            params.cmpt++;
            if ([100, 102, 200].indexOf(response.status)) {
                thisBis.updateProgressBar(response.progress);
                thisBis.scrollPreview();
            } else if ([401, 404, "ko"].indexOf(response.status)) {
                console.log(response.data);
                return;
            } else {
                console.log(thisBis.errorMsg);
                return;
            }
            if (200 === response.status && ! params.isProgressBarUpdated) {
                thisBis.afterServerProcessEnd();
                params.isProgressBarUpdated = true;
                params.submitBtn.value = thisBis.labelDownloadInProgress;
            }
            console.log("------------------------------------------------------------------------");
        };
        return xhr;
    };

    return {
        setEventSubmit : setEventSubmit,
        addHtmlForm : addHtmlForm,
        updateProgressBar : updateProgressBar,
        scrollPreview : scrollPreview,
        replaceClass : replaceClass,
        initProcessAnimation : initProcessAnimation,
        afterServerProcessEnd : afterServerProcessEnd,
        afterClientProcessEnd : afterClientProcessEnd,
        addTextTopreviewResult : addTextTopreviewResult,
        statusTracking : statusTracking,
        handleProgress : handleProgress,
        txtAreaContent : txtAreaContent,
        labelProcessInProgress : labelProcessInProgress,
        labelDownloadInProgress : labelDownloadInProgress,
        errorMsg : errorMsg,
    };
})();
