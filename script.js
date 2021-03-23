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
                    email: loginForm.find('#email').val(),
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

                        loginForm.find('#email').length > 0 && loginForm.find('#email').removeClass('is-invalid');
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
                                    if ($(`#${key}-error-alert`).length === 0) {
                                        $(`#${key}`).addClass('is-invalid');

                                        $(`<div id="${key}-error-alert" class="invalid-feedback text-start fs-8 p-0 mt-1 d-block">${responseData[key]}</div>`).insertAfter(`.gsg-auth-form #${key}`);
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
                beforeSend: function() {
                    console.log('beforeSend: logout');
                },
                error: function(xhr) {
                    let response = xhr.responseJSON;

                    console.log(response);
                },
                success: function(response) {
                    if (response.success) {
                        location.href = `${gsg.loginUrl}?logged_out=true`;
                    }
                },
                complete: function() {
                    console.log('complete: logout');
                }
            });
        });/*}}}*/
    });
})(jQuery);
