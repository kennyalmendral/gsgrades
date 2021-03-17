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
            let loginForm = $('.gsg-auth-form'),
                loginBtn = loginForm.find('button');

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
                                            $(`<div id="login-error" class="alert alert-danger fs-8 px-2 py-2">${responseData[key]}</div>`).insertAfter('.gsg-auth-form h1');
                                        }
                                    }
                                }
                            }
                        }
                    },
                    success: function(response) {
                        if (response.success) {
                            $(`<div id="login-success" class="alert alert-success fs-8 px-2 py-2">${response.data.message}</div>`).insertAfter('.gsg-auth-form h1');

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
