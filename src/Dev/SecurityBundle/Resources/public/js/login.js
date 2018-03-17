SecurityLogin = (function() {
    "use strict";

    var displayLoginButton = function() {
        var that = this;
        $("#btnPanelUser").hide();
        $("#btnLogin").show();
        $("#btnLogin").click(function (event) {
            event.stopPropagation();
            that.displayLoginForm();
        });
    };

    var displayLoginForm = function() {
        var that = this;
        this.addLoginFormModal().then(function() {
            that.addEventLoginSubmit();

            $('#securityLoginModal').modal('show');

        });
    };

    var addLoginFormModal = function() {
        if (0 !== document.querySelectorAll('#securityLoginModal').length) {
            return new Promise(
                function(resolve, reject) {
                    resolve();
                }
            );
        }

        var url     = "user/loginForm";
        var promise = Promise.resolve($.ajax({
            type: "POST",
            url: url
        })).then(function(data) {
            $("body").append(data.view);
            $('#loginModalBody .error').hide();
        });
        return promise;
    };

    var addEventLoginSubmit = function() {
        var thisBis = this;
        $('#SecurityBtnLogin').click(function () {
            var form = document.querySelector("#loginModalBody form");
            var url = form.getAttribute("action");
            var formData = new FormData(form);
            $('#loginModalBody .error').hide();

            $.ajax({
                type: "POST",
                url: url,
                data: formData,
                processData: false,
                contentType: false
            }).done(function (data) {
                console.log("login ok");
                thisBis.changeAuthenticateMode(true);
                $('#securityLoginModal').modal("hide");
            }).fail(function (xhr) {
                var data = xhr.responseJSON;
                $('#loginModalBody .error').html(data.messageError);
                $('#loginModalBody .error').show("slow");

                console.log("login fail");
            });
        });
    };

    var changeAuthenticateMode = function(connected) {
        var thiz = this;
        $("#securityUserPanelContent").remove();
        if(connected) {
            userConnected = true;
            this.displayUserPanelButton();
        } else {
            userConnected = false;
            $("#securityLoginModal").remove();
            this.displayLoginButton();
        }
    };

    var displayUserPanelButton = function() {
        var that = this;
        $("#btnLogin").hide();
        $("#btnPanelUser").show();
        $("#btnPanelUser").click(function (event) {
            event.stopPropagation();
            that.displayUserPanelView();
        });
    };

    var displayUserPanel = function() {
        var thisBis = this;
        this.addUserPanelView().then(function() {
            thisBis.addLogoutEvent();
            $("#securityUserPanelContent").modal("show");
        });
    };

    var addUserPanelView = function() {
        if (0 !== document.querySelectorAll("#securityUserPanelContent").length) {
            return new Promise(
                function(resolve, reject) {
                    resolve();
                }
            );
        }

        var url = Dev.config.url.user.panel;
        var promise = $.ajax({
            type: "POST",
            url: url
        });
        promise = Promise.resolve(promise);
        promise.then(function(data) {
            $("body").append(data.view);
        });

        return promise;
    };

    var addLogoutEvent = function() {
        var thisBis = this;
        $("#SecurityBtnLogout").click(function () {
            var url = "/logout";
            $.ajax({
                type: "POST",
                url: url,
                success: function(data) {
                    $('#securityUserPanelContent').modal("hide");
                    $("body").removeClass("modal-open");
                    $(".modal-backdrop").remove();
                    thisBis.changeAuthenticateMode(false);
                }
            });

        });
        return true;
    };

    var init = function() {
        if (true === userConnected) {
            this.displayUserPanelButton();
        } else {
            this.displayLoginButton();
        }

    };

    return {
        displayLoginForm:       displayLoginForm,
        addLoginFormModal:       addLoginFormModal,
        addEventLoginSubmit:    addEventLoginSubmit,
        changeAuthenticateMode: changeAuthenticateMode,
        displayUserPanelView:   displayUserPanel,
        addUserPanelView:       addUserPanelView,
        addLogoutEvent:         addLogoutEvent,
        displayLoginButton:     displayLoginButton,
        displayUserPanelButton: displayUserPanelButton,
        init:                   init
    };
})();
