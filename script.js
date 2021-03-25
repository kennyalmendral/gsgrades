(function($) {
    const loader = $('#loader');

    $(window).on('load', function() {/*{{{*/
        loader.fadeOut('slow', function() {
            $(this).remove();
        });
    });/*}}}*/

    $(document).ready(function() {
        console.log(gsg);

        if (gsg.isLoginPage) {/*{{{*/
            const loginForm = $('.gsg-auth-form');
            const loginBtn = loginForm.find('button');

            loginForm.submit(function(e) {
                e.preventDefault();

                let formData = {
                    email_address: loginForm.find('#email-address').val(),
                    password: loginForm.find('#password').val(),
                    remember: loginForm.find('#remember').is(':checked')
                };

                $.ajax({
                    url: gsg.ajaxUrl,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'gsg_login',
                        login_nonce: loginForm.find('#gsg_login_nonce_field').val(),
                        ...formData
                    },
                    beforeSend: function() {
                        loginBtn.attr('disabled', true);
                        loginBtn.find('span').text('Logging you in');
                        loginBtn.find('i').removeClass('d-none');

                        loginForm.find('#email-address').length > 0 && loginForm.find('#email-address').removeClass('is-invalid');
                        loginForm.find('#password').length > 0 && loginForm.find('#password').removeClass('is-invalid');
                        loginForm.find('#login-error').length > 0 && loginForm.find('#login-error').remove();
                        loginForm.find('.alert-success').length > 0 && loginForm.find('.alert-success').remove();
                        loginForm.find('.invalid-feedback').length > 0 && loginForm.find('.invalid-feedback').remove();
                    },
                    error: function(xhr) {
                        let response = xhr.responseJSON;

                        if (!response.success) {
                            let responseData = response.data;

                            for (const key in responseData) {
                                if (responseData.hasOwnProperty(key)) {
                                    keyId = key.replace('_', '-');

                                    if ($(`#${keyId}-error-alert`).length === 0) {
                                        $(`#${keyId}`).addClass('is-invalid');

                                        $(`<div id="${keyId}-error-alert" class="invalid-feedback text-start fs-8 p-0 mt-1 d-block">${responseData[key]}</div>`).insertAfter(`.gsg-auth-form #${keyId}`);
                                    }

                                    if (key === 'login_error') {
                                        if ($('#login-error').length === 0) {
                                            $('.gsg-auth-form > div').prepend(`<div id="login-error" class="alert alert-danger fs-8 px-3 py-2">${responseData[key]}</div>`);
                                        }
                                    }
                                }
                            }
                        }
                    },
                    success: function(response) {
                        if (response.success) {
                            $('.gsg-auth-form > div').prepend(`<div id="login-success" class="alert alert-success fs-8 px-3 py-2">${response.data.message}</div>`);

                            setTimeout(function() {
                                location.href = gsg.homeUrl;
                            }, 1000);
                        }
                    },
                    complete: function() {
                        loginBtn.removeAttr('disabled');
                        loginBtn.find('span').text('Login');
                        loginBtn.find('i').addClass('d-none');
                    }
                });
            });
        }/*}}}*/

        if (gsg.isRegisterPage) {/*{{{*/
            const registerForm = $('.gsg-auth-form');
            const registerBtn = registerForm.find('button');

            registerForm.submit(function(e) {
                e.preventDefault();

                let formData = {
                    first_name: registerForm.find('#first-name').val(),
                    last_name: registerForm.find('#last-name').val(),
                    email_address: registerForm.find('#email-address').val(),
                    contact_number: registerForm.find('#contact-number').val(),
                    password: registerForm.find('#password').val(),
                    password_confirmation: registerForm.find('#password-confirmation').val()
                };

                $.ajax({
                    url: gsg.ajaxUrl,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'gsg_register',
                        register_nonce: registerForm.find('#gsg_register_nonce_field').val(),
                        ...formData
                    },
                    beforeSend: function() {
                        registerBtn.attr('disabled', true);
                        registerBtn.find('span').text('Creating your account');
                        registerBtn.find('i').removeClass('d-none');

                        registerForm.find('#first-name').length > 0 && registerForm.find('#first-name').removeClass('is-invalid');
                        registerForm.find('#last-name').length > 0 && registerForm.find('#last-name').removeClass('is-invalid');
                        registerForm.find('#email-address').length > 0 && registerForm.find('#email-address').removeClass('is-invalid');
                        registerForm.find('#contact-number').length > 0 && registerForm.find('#contact-number').removeClass('is-invalid');
                        registerForm.find('#password').length > 0 && registerForm.find('#password').removeClass('is-invalid');
                        registerForm.find('#password-confirmation').length > 0 && registerForm.find('#password-confirmation').removeClass('is-invalid');

                        registerForm.find('#register-error').length > 0 && registerForm.find('#register-error').remove();
                        registerForm.find('.alert-success').length > 0 && registerForm.find('.alert-success').remove();
                        registerForm.find('.invalid-feedback').length > 0 && registerForm.find('.invalid-feedback').remove();
                    },
                    error: function(xhr) {
                        let response = xhr.responseJSON;

                        if (!response.success) {
                            let responseData = response.data;

                            for (let key in responseData) {
                                if (responseData.hasOwnProperty(key)) {
                                    keyId = key.replace('_', '-');

                                    if ($(`#${keyId}-error-alert`).length === 0) {
                                        $(`#${keyId}`).addClass('is-invalid');

                                        $(`<div id="${keyId}-error-alert" class="invalid-feedback text-start fs-8 p-0 mt-1 d-block">${responseData[key]}</div>`).insertAfter(`.gsg-auth-form #${keyId}`);
                                    }

                                    if (key === 'register_error') {
                                        if ($('#register-error').length === 0) {
                                            $('.gsg-auth-form > div').prepend(`<div id="register-error" class="alert alert-danger fs-8 px-3 py-2">${responseData[key]}</div>`);
                                        }
                                    }
                                }
                            }

                            window.scrollTo({
                                top: registerForm.offset().top,
                                behavior: 'smooth'
                            });
                        }
                    },
                    success: function(response) {
                        if (response.success) {
                            location.href = `${gsg.loginUrl}?account_created=true`;
                        }
                    },
                    complete: function() {
                        registerBtn.removeAttr('disabled');
                        registerBtn.find('span').text('Submit');
                        registerBtn.find('i').addClass('d-none');
                    }
                });
            });
        }/*}}}*/

        if (gsg.isForgotPasswordPage) {/*{{{*/
            const forgotPasswordForm = $('.gsg-auth-form');
            const sendBtn = forgotPasswordForm.find('button');

            forgotPasswordForm.submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: gsg.ajaxUrl,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'gsg_forgot_password',
                        forgot_password_nonce: forgotPasswordForm.find('#gsg_forgot_password_nonce_field').val(),
                        email_address: forgotPasswordForm.find('#email-address').val()
                    },
                    beforeSend: function() {
                        sendBtn.attr('disabled', true);
                        sendBtn.find('span').text('Sending');
                        sendBtn.find('i').removeClass('d-none');

                        forgotPasswordForm.find('#email-address').length > 0 && forgotPasswordForm.find('#email-address').removeClass('is-invalid');
                        forgotPasswordForm.find('#forgot-password-error').length > 0 && forgotPasswordForm.find('#forgot-password-error').remove();
                        forgotPasswordForm.find('.alert-success').length > 0 && forgotPasswordForm.find('.alert-success').remove();
                        forgotPasswordForm.find('.invalid-feedback').length > 0 && forgotPasswordForm.find('.invalid-feedback').remove();
                    },
                    error: function(xhr) {
                        let response = xhr.responseJSON;

                        if (!response.success) {
                            let responseData = response.data;

                            for (const key in responseData) {
                                if (responseData.hasOwnProperty(key)) {
                                    keyId = key.replace('_', '-');

                                    if ($(`#${keyId}-error-alert`).length === 0) {
                                        $(`#${keyId}`).addClass('is-invalid');

                                        $(`<div id="${keyId}-error-alert" class="invalid-feedback text-start fs-8 p-0 mt-1 d-block">${responseData[key]}</div>`).insertAfter(`.gsg-auth-form #${keyId}`);
                                    }

                                    if (key === 'forgot_password_error') {
                                        if ($('#forgot-password-error').length === 0) {
                                            $('.gsg-auth-form > div').prepend(`<div id="forgot-password-error" class="alert alert-danger fs-8 px-3 py-2">${responseData[key]}</div>`);
                                        }
                                    }
                                }
                            }
                        }
                    },
                    success: function(response) {
                        if (response.success) {
                            location.href = `${gsg.forgotPasswordUrl}?sent=true`;
                        }
                    },
                    complete: function() {
                        sendBtn.removeAttr('disabled');
                        sendBtn.find('span').text('Send');
                        sendBtn.find('i').addClass('d-none');
                    }
                });
            });
        }/*}}}*/

        if (gsg.isResetPasswordPage) {/*{{{*/
            const resetPasswordForm = $('.gsg-auth-form');
            const saveBtn = resetPasswordForm.find('button');

            resetPasswordForm.submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: gsg.ajaxUrl,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'gsg_reset_password',
                        reset_password_nonce: resetPasswordForm.find('#gsg_reset_password_nonce_field').val(),
                        reset_password_key: resetPasswordForm.find('#reset-password-key').val(),
                        user_login: resetPasswordForm.find('#user-login').val(),
                        password: resetPasswordForm.find('#password').val(),
                        password_confirmation: resetPasswordForm.find('#password-confirmation').val()
                    },
                    beforeSend: function() {
                        saveBtn.attr('disabled', true);
                        saveBtn.find('span').text('Saving changes');
                        saveBtn.find('i').removeClass('d-none');

                        resetPasswordForm.find('#password').length > 0 && resetPasswordForm.find('#password').removeClass('is-invalid');
                        resetPasswordForm.find('#password-confirmation').length > 0 && resetPasswordForm.find('#password-confirmation').removeClass('is-invalid');

                        resetPasswordForm.find('#reset-password-error').length > 0 && resetPasswordForm.find('#reset-password-error').remove();
                        resetPasswordForm.find('.alert-success').length > 0 && resetPasswordForm.find('.alert-success').remove();
                        resetPasswordForm.find('.invalid-feedback').length > 0 && resetPasswordForm.find('.invalid-feedback').remove();
                    },
                    error: function(xhr) {
                        let response = xhr.responseJSON;

                        if (!response.success) {
                            let responseData = response.data;

                            for (const key in responseData) {
                                if (responseData.hasOwnProperty(key)) {
                                    keyId = key.replace('_', '-');

                                    if ($(`#${keyId}-error-alert`).length === 0) {
                                        $(`#${keyId}`).addClass('is-invalid');

                                        $(`<div id="${keyId}-error-alert" class="invalid-feedback text-start fs-8 p-0 mt-1 d-block">${responseData[key]}</div>`).insertAfter(`.gsg-auth-form #${keyId}`);
                                    }

                                    if (key === 'reset_password_error') {
                                        if ($('#reset-password-error').length === 0) {
                                            $('.gsg-auth-form > div').prepend(`<div id="reset-password-error" class="alert alert-danger fs-8 px-3 py-2">${responseData[key]}</div>`);
                                        }
                                    }
                                }
                            }
                        }
                    },
                    success: function(response) {
                        if (response.success) {
                            location.href = `${gsg.loginUrl}?password_changed=true`;
                        }
                    },
                    complete: function() {
                        saveBtn.removeAttr('disabled');
                        saveBtn.find('span').text('Save');
                        saveBtn.find('i').addClass('d-none');
                    }
                });
            });
        }/*}}}*/

        if (gsg.isAccountPage) {/*{{{*/
            const accountInfoForm = $('#account-info-container form');
            const saveChangesBtn = accountInfoForm.find('#save-changes-button');

            if ($('#account-info-container .alert-success').length > 0) {
                setTimeout(function() {
                    $('#account-info-container .alert-success').fadeOut();
                }, 2000);
            }

            accountInfoForm.submit(function(e) {
                e.preventDefault();

                let formData = {
                    first_name: accountInfoForm.find('#first-name').val(),
                    last_name: accountInfoForm.find('#last-name').val(),
                    email_address: accountInfoForm.find('#email-address').val(),
                    contact_number: accountInfoForm.find('#contact-number').val(),
                    password: accountInfoForm.find('#password').val(),
                    password_confirmation: accountInfoForm.find('#password-confirmation').val()
                };

                $.ajax({
                    url: gsg.ajaxUrl,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'gsg_save_account_info',
                        save_account_info_nonce: accountInfoForm.find('#gsg_save_account_info_nonce_field').val(),
                        ...formData
                    },
                    beforeSend: function() {
                        saveChangesBtn.attr('disabled', true);
                        saveChangesBtn.find('span').text('Saving changes');
                        saveChangesBtn.find('i').removeClass('d-none');

                        accountInfoForm.find('#first-name').length > 0 && accountInfoForm.find('#first-name').removeClass('is-invalid');
                        accountInfoForm.find('#last-name').length > 0 && accountInfoForm.find('#last-name').removeClass('is-invalid');
                        accountInfoForm.find('#email-address').length > 0 && accountInfoForm.find('#email-address').removeClass('is-invalid');
                        accountInfoForm.find('#contact-number').length > 0 && accountInfoForm.find('#contact-number').removeClass('is-invalid');
                        accountInfoForm.find('#password').length > 0 && accountInfoForm.find('#password').removeClass('is-invalid');
                        accountInfoForm.find('#password-confirmation').length > 0 && accountInfoForm.find('#password-confirmation').removeClass('is-invalid');

                        accountInfoForm.find('#save-account-info-error').length > 0 && accountInfoForm.find('#save-account-info-error').remove();
                        accountInfoForm.find('.alert-success').length > 0 && accountInfoForm.find('.alert-success').remove();
                        accountInfoForm.find('.invalid-feedback').length > 0 && accountInfoForm.find('.invalid-feedback').remove();
                    },
                    error: function(xhr) {
                        let response = xhr.responseJSON;

                        if (!response.success) {
                            let responseData = response.data;

                            for (let key in responseData) {
                                if (responseData.hasOwnProperty(key)) {
                                    keyId = key.replace('_', '-');

                                    if ($(`#${keyId}-error-alert`).length === 0) {
                                        $(`#${keyId}`).addClass('is-invalid');

                                        $(`<div id="${keyId}-error-alert" class="invalid-feedback text-start fs-8 p-0 mt-1 d-block">${responseData[key]}</div>`).insertAfter(`#account-info-container form #${keyId}`);
                                    }

                                    if (key === 'save_account_info_error') {
                                        if ($('#save-account-info-error').length === 0) {
                                            $('#account-info-container form').prepend(`<div id="save-account-info-error" class="alert alert-danger fs-8 px-3 py-2">${responseData[key]}</div>`);
                                        }
                                    }
                                }
                            }

                            window.scrollTo({
                                top: accountInfoForm.offset().top,
                                behavior: 'smooth'
                            });
                        }
                    },
                    success: function(response) {
                        if (response.success) {
                            location.href = `${gsg.accountUrl}?info_updated=true`;
                        }
                    },
                    complete: function() {
                        saveChangesBtn.removeAttr('disabled');
                        saveChangesBtn.find('span').text('Save changes');
                        saveChangesBtn.find('i').addClass('d-none');
                    }
                });
            });
        }/*}}}*/

        $('#logout').click(function(e) {/*{{{*/
            e.preventDefault();

            const me = $(this);

            $.ajax({
                url: gsg.ajaxUrl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'gsg_logout',
                    logout_nonce: gsg.logoutNonce
                },
                error: function(xhr) {
                    let response = xhr.responseJSON;

                    console.log(response);
                },
                success: function(response) {
                    if (response.success) {
                        location.href = `${gsg.loginUrl}?logged_out=true`;
                    }
                }
            });
        });/*}}}*/
    });
})(jQuery);
