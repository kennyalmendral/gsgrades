(function($) {
    const loader = $('#loader');

    $(window).on('load', function() {/*{{{*/
        loader.fadeOut('slow', function() {
            $(this).hide();
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
            const profilePictureContainer = $('#profile-picture-container');
            const saveChangesBtn = accountInfoForm.find('#save-changes-button');
            const uploadUpdateBtn = profilePictureContainer.find('#upload-update-button');
            const removeBtn = profilePictureContainer.find('#remove-button');
            const profilePictureInput = $('#profile-picture');
            const urlParams = new URLSearchParams(window.location.search);

            if ($('#account-info-container .alert-success').length > 0) {
                setTimeout(function() {
                    $('#account-info-container .alert-success').fadeOut();
                }, 2000);
            }

            if ($('#profile-picture-container .alert-success').length > 0) {
                setTimeout(function() {
                    $('#profile-picture-container .alert-success').fadeOut();
                }, 2000);
            }

            if (urlParams.has('profile_picture_removed') || urlParams.has('profile_picture_uploaded') || urlParams.has('profile_picture_updated')) {
                window.scrollTo({
                    top: profilePictureContainer.offset().top,
                    behavior: 'smooth'
                });
            } else if (urlParams.has('info_updated')) {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }

            if (profilePictureInput.get(0).files.length === 0) {
                uploadUpdateBtn.attr('disabled', true);
            }

            if (!gsg.currentUserHasProfilePicture) {
                removeBtn.attr('disabled', true);
            }

            profilePictureInput.change(function(e) {
                const me = $(this);
                const allowedFileTypes = ['jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'PNG'];

                if (profilePictureContainer.find('#profile-picture-error-alert').length > 0) {
                    me.next().remove();
                }

                if ((me.get(0).files.length > 0) && me.get(0).files[0]) {
                    const extension = me.get(0).files[0].name.split('.').pop().toLowerCase();
                    const isExtensionAllowed = allowedFileTypes.indexOf(extension) > -1;

                    if (isExtensionAllowed) {
                        if ((parseFloat(me.get(0).files[0].size) / 1024) > 3000) {
                            profilePictureContainer.find('#image-wrap').html(`<h4 class="m-0 text-muted">${gsg.currentUserNameInitials}</h4>`);

                            $(`<div id="profile-picture-error-alert" class="invalid-feedback text-start fs-8 p-0 mt-1 d-block">The file size must be less than 3MB.</div>`).insertAfter(me);

                            !me.hasClass('is-invalid') && me.addClass('is-invalid');

                            uploadUpdateBtn.attr('disabled', true);
                        } else {
                            const reader = new FileReader();

                            reader.onload = function(e) {
                                profilePictureContainer.find('#image-wrap').html(`<img src="${e.target.result}">`);
                            };

                            reader.readAsDataURL(me.get(0).files[0]);

                            me.hasClass('is-invalid') && me.removeClass('is-invalid');

                            uploadUpdateBtn.removeAttr('disabled');
                        }
                    } else {
                        profilePictureContainer.find('#image-wrap').html(`<h4 class="m-0 text-muted">${gsg.currentUserNameInitials}</h4>`);
                    
                        $(`<div id="profile-picture-error-alert" class="invalid-feedback text-start fs-8 p-0 mt-1 d-block">The file type must be an image and must be in JPG or PNG format only.</div>`).insertAfter(me);

                        !me.hasClass('is-invalid') && me.addClass('is-invalid');

                        uploadUpdateBtn.attr('disabled', true);
                    }
                } else {
                    profilePictureContainer.find('#image-wrap').html(`<h4 class="m-0 text-muted">${gsg.currentUserNameInitials}</h4>`);

                    $(`<div id="profile-picture-error-alert" class="invalid-feedback text-start fs-8 p-0 mt-1 d-block">Please select a file.</div>`).insertAfter(me);

                    !me.hasClass('is-invalid') && me.addClass('is-invalid');

                    uploadUpdateBtn.attr('disabled', true);
                }
            });

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
                                            accountInfoForm.prepend(`<div id="save-account-info-error" class="alert alert-danger fs-8 px-3 py-2">${responseData[key]}</div>`);
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

            uploadUpdateBtn.click(function() {
                const me = $(this);
                const formData = new FormData();

                if (profilePictureInput.get(0).files.length > 0) {
                    formData.append('profile_picture', profilePictureInput.get(0).files[0]);
                }

                formData.append('action', 'gsg_upload_update_profile_picture');
                formData.append('upload_update_profile_picture_nonce', profilePictureContainer.find('#gsg_upload_update_profile_picture_nonce_field').val());

                $.ajax({
                    url: gsg.ajaxUrl,
                    method: 'POST',
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    data: formData,
                    beforeSend: function() {
                        me.attr('disabled', true);
                        me.find('i').removeClass('d-none');

                        let btnText = gsg.currentUserHasProfilePicture ? 'Updating' : 'Uploading';

                        me.find('span').text(btnText);

                        profilePictureInput.get(0).files.length > 0 && profilePictureInput.removeClass('is-invalid');

                        profilePictureContainer.find('#upload-update-profile-picture-error').length > 0 && profilePictureContainer.find('#upload-update-profile-picture-error').remove();
                        profilePictureContainer.find('.alert-success').length > 0 && profilePictureContainer.find('.alert-success').remove();
                        profilePictureContainer.find('.invalid-feedback').length > 0 && profilePictureContainer.find('.invalid-feedback').remove();
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

                                        $(`<div id="${keyId}-error-alert" class="invalid-feedback text-start fs-8 p-0 mt-1 d-block">${responseData[key]}</div>`).insertAfter(`#profile-picture-container #${keyId}`);
                                    }

                                    if (key === 'upload_update_profile_picture_error') {
                                        if ($('#upload-update-profile-picture-error').length === 0) {
                                            profilePictureContainer.find('#control-group').prepend(`<div id="upload-update-profile-picture-error" class="alert alert-danger fs-8 px-3 py-2 mb-3">${responseData[key]}</div>`);
                                        }
                                    }
                                }
                            }

                            window.scrollTo({
                                top: profilePictureContainer.offset().top,
                                behavior: 'smooth'
                            });
                        }
                    },
                    success: function(response) {
                        if (response.success) {
                            let queryString = gsg.currentUserHasProfilePicture ? 'profile_picture_updated=true' : 'profile_picture_uploaded=true';

                            location.href = `${gsg.accountUrl}?${queryString}`;
                        }
                    },
                    complete: function() {
                        me.removeAttr('disabled');
                        me.find('i').addClass('d-none');

                        let btnText = gsg.currentUserHasProfilePicture ? 'Update' : 'Upload';

                        me.find('span').text(btnText);
                    }
                });
            });

            removeBtn.click(function(e) {
                const me = $(this);
                const filename = me.data('filename');

                $.ajax({
                    url: gsg.ajaxUrl,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'gsg_remove_profile_picture',
                        remove_profile_picture_nonce: profilePictureContainer.find('#gsg_remove_profile_picture_nonce_field').val(),
                        filename: filename
                    },
                    beforeSend: function() {
                        me.attr('disabled', true);
                        me.find('i').removeClass('d-none');
                        me.find('span').text('Removing');

                        profilePictureContainer.find('#remove-profile-picture-error').length > 0 && profilePictureContainer.find('#remove-profile-picture-error').remove();
                        profilePictureContainer.find('.alert-success').length > 0 && profilePictureContainer.find('.alert-success').remove();
                        profilePictureContainer.find('.invalid-feedback').length > 0 && profilePictureContainer.find('.invalid-feedback').remove();
                    },
                    error: function(xhr) {
                        let response = xhr.responseJSON;

                        if (!response.success) {
                            let responseData = response.data;

                            for (let key in responseData) {
                                if (responseData.hasOwnProperty(key)) {
                                    if (key === 'remove_profile_picture_error') {
                                        if ($('#remove-profile-picture-error').length === 0) {
                                            profilePictureContainer.find('#control-group').prepend(`<div id="remove-profile-picture-error" class="alert alert-danger fs-8 px-3 py-2 mb-3">${responseData[key]}</div>`);
                                        }
                                    }
                                }
                            }

                            window.scrollTo({
                                top: profilePictureContainer.offset().top,
                                behavior: 'smooth'
                            });
                        }
                    },
                    success: function(response) {
                        if (response.success) {
                            location.href = `${gsg.accountUrl}?profile_picture_removed=true`;
                        }
                    },
                    complete: function() {
                        me.removeAttr('disabled');
                        me.find('i').addClass('d-none');
                        me.find('span').text('Remove');
                    }
                });
            });
        }/*}}}*/

        if (gsg.isStudentsPage) {/*{{{*/
            const studentsTable = $('#students-table');

            const studentsDataTable = studentsTable.DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: gsg.ajaxUrl,
                    data: {
                        action: 'gsg_get_students',
                        get_students_nonce: gsg.getStudentsNonce
                    },
                    columns: [
                        { data: 'ID' },
                        { data: 'display_name' },
                        { data: 'user_email' },
                        { data: 'contact_number' },
                        { data: 'user_registered' },
                    ],
                },
                columnDefs: [
                    {
                        targets: -1,
                        data: null,
                        defaultContent: '<button class="view-details-button btn btn-secondary btn-sm" title="View details"><i class="bi bi-eye d-none"></i><i class="bi bi-eye-fill"></i></button>',
                        className: 'dt-center',
                        orderable: false
                    }
                ],
                language: {
                    search: '',
                    searchPlaceholder: 'Search...'
                },
                initComplete: function(settings, json) {
                    $('#students-table_length select').removeClass('form-control form-control-sm').addClass('form-select form-select-sm');

                    $('#main-content').fadeIn();
                }
            });

            studentsTable.on('click', 'button', function() {
                const me = $(this);
                const data = studentsDataTable.row($(this).parents('tr')).data();
                const studentId = data[0];

                $.ajax({
                    url: gsg.ajaxUrl,
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        action: 'gsg_get_student',
                        get_student_nonce: gsg.getStudentNonce,
                        student_id: studentId
                    },
                    beforeSend: function() {
                        me.attr('disabled', true);
                        me.find('.bi-eye-fill').addClass('d-none');
                        me.find('.bi-eye').removeClass('d-none');
                    },
                    error: function(xhr) {
                        let response = xhr.responseJSON;

                        alert(response.data);
                    },
                    success: function(response) {
                        if (response.success) {
                            const user = response.data;
                            const name = $('#student-details-modal').find('#name');
                            const emailAddress = $('#student-details-modal').find('#email-address a');
                            const contactNumber = $('#student-details-modal').find('#contact-number a');

                            const studentDetailsModal = new bootstrap.Modal(document.getElementById('student-details-modal'), {
                                backdrop: 'static',
                                keyboard: false
                            });

                            if (user.profile_picture == null) {
                                $('#student-details-modal').find('#image-wrap').html(`<h4 class="m-0 text-muted">${user.user_initials}</h4>`);
                            } else {
                                $('#student-details-modal').find('#image-wrap').html(`<img src="${user.profile_picture}" alt="${user.display_name}">`);
                            }

                            name.text(user.display_name);

                            emailAddress.attr('href', `mailto:${user.user_email}`);
                            emailAddress.attr('title', `Send mail to ${user.user_email}`);
                            emailAddress.text(user.user_email);

                            contactNumber.attr('href', `tel:${user.contact_number}`);
                            contactNumber.attr('title', `Call ${user.contact_number}`);
                            contactNumber.text(user.contact_number);

                            studentDetailsModal.show();
                        }
                    },
                    complete: function() {
                        me.removeAttr('disabled');
                        me.find('.bi-eye-fill').removeClass('d-none');
                        me.find('.bi-eye').addClass('d-none');
                    }
                });
            });
        }/*}}}*/

        if (gsg.isClassesPage) {/*{{{*/
            const statusFilter = $('body').find('#status-filter');
            const statusFilterSelect = statusFilter.find('select');

            const classesTable = $('#classes-table');
        
            const classesDataTable = classesTable.DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: gsg.ajaxUrl,
                    data: function(d) {
                        d.action = 'gsg_get_classes';
                        d.get_classes_nonce = gsg.getClassesNonce;
                        d.status = statusFilterSelect.val();
                    },
                    columns: [
                        { data: 'id' },
                        { data: 'post_title' },
                        { data: 'completion_hours' },
                        { data: 'completed_hours' },
                        { data: 'remaining_hours' },
                        { data: 'status' },
                        { data: 'date_created' }
                    ],
                },
                columnDefs: [
                    {
                        targets: -1,
                        data: null,
                        defaultContent: '<button class="edit-class-button btn btn-primary btn-sm me-2" title="Edit class"><i class="bi bi-pencil d-none"></i><i class="bi bi-pencil-fill"></i></button><button class="delete-class-button btn btn-danger btn-sm" title="Delete class"><i class="bi bi-trash d-none"></i><i class="bi bi-trash-fill"></i></button>',
                        className: 'dt-center',
                        orderable: false
                    }
                ],
                order: [
                    [6, 'DESC']
                ],
                language: {
                    search: '',
                    searchPlaceholder: 'Search...'
                },
                initComplete: function(settings, json) {
                    $('#classes-table_length select').removeClass('form-control form-control-sm').addClass('form-select form-select-sm');

                    $('#classes-table_wrapper .row:first-of-type').addClass('justify-content-between');
                    $('#classes-table_wrapper .row:first-of-type .col-sm-12').removeClass('col-md-6').addClass('col-md-4');
                    $('#classes-table_wrapper .row:first-of-type .col-sm-12').removeClass('col-md-6').addClass('col-md-4');

                    $('#classes-table_wrapper .row:first-of-type .col-sm-12:last-of-type').addClass('d-flex align-items-center justify-content-between').prepend(statusFilter);

                    $('#main-content').fadeIn();
                }
            });

            statusFilterSelect.change(function() {
                classesDataTable.ajax.reload();
            });

            $('body').on('click', '.delete-class-button', function() {
                let confirmation = confirm('This action cannot be undone. Are you sure you want to delete this class?');

                if (confirmation) {
                    const me = $(this);
                    const data = classesDataTable.row($(this).parents('tr')).data();
                    const classId = data[0];

                    $.ajax({
                        url: gsg.ajaxUrl,
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'gsg_delete_class',
                            delete_class_nonce: gsg.deleteClassNonce,
                            class_id: classId
                        },
                        beforeSend: function() {
                            me.attr('disabled', true);
                            me.find('.bi-trash-fill').addClass('d-none');
                            me.find('.bi-trash').removeClass('d-none');
                        },
                        error: function(xhr) {
                            let response = xhr.responseJSON;

                            alert(response.data.error_message);
                        },
                        success: function(response) {
                            if (response.success) {
                                location.href = gsg.classesUrl;
                            }
                        },
                        complete: function() {
                            me.removeAttr('disabled');
                            me.find('.bi-trash-fill').removeClass('d-none');
                            me.find('.bi-trash').addClass('d-none');
                        }
                    });
                }
            });
        
            const createClassBtn = $('#create-class');
            const createClassModalForm = $('#create-class-modal').find('form');
            const createClassModalFormSubmitBtn = createClassModalForm.find('.modal-footer button');

            const createClassModal = new bootstrap.Modal(document.getElementById('create-class-modal'), {
                backdrop: 'static',
                keyboard: false
            });

            createClassBtn.click(function() {
                const me = $(this);

                createClassModal.show();
            });

            createClassModalForm.submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: gsg.ajaxUrl,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'gsg_create_class',
                        create_class_nonce: createClassModalForm.find('#gsg_create_class_nonce_field').val(),
                        completion_hours: createClassModalForm.find('#completion-hours').val()
                    },
                    beforeSend: function() {
                        createClassModalFormSubmitBtn.attr('disabled', true);
                        createClassModalFormSubmitBtn.find('span').text('Creating class');
                        createClassModalFormSubmitBtn.find('i').removeClass('d-none');

                        createClassModalForm.find('#completion-hours').length > 0 && createClassModalForm.find('#completion-hours').removeClass('is-invalid');
                        createClassModalForm.find('#create-class-error').length > 0 && createClassModalForm.find('#create-class-error').remove();
                        createClassModalForm.find('.alert-success').length > 0 && createClassModalForm.find('.alert-success').remove();
                        createClassModalForm.find('.invalid-feedback').length > 0 && createClassModalForm.find('.invalid-feedback').remove();
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

                                        $(`<div id="${keyId}-error-alert" class="invalid-feedback text-start fs-8 p-0 mt-1 d-block">${responseData[key]}</div>`).insertAfter(createClassModalForm.find(`#${keyId}`));
                                    }

                                    if (key === 'create_class_error') {
                                        if ($('#create_class_error').length === 0) {
                                            createClassModalForm.find('.modal-body').prepend(`<div id="create-class-error" class="alert alert-danger fs-8 px-3 py-2">${responseData[key]}</div>`);
                                        }
                                    }
                                }
                            }
                        }
                    },
                    success: function(response) {
                        if (response.success) {
                            createClassModalForm.find('.modal-body').prepend(`<div id="create-class-success" class="alert alert-success fs-8 px-3 py-2">${response.data.message}</div>`);

                            setTimeout(function() {
                                createClassModal.hide();

                                createClassModalForm.find('#create-class-success').remove();
                                createClassModalForm.find('#completion-hours').val('');

                                classesDataTable.ajax.reload();
                            }, 1000);
                        }
                    },
                    complete: function() {
                        createClassModalFormSubmitBtn.removeAttr('disabled');
                        createClassModalFormSubmitBtn.find('span').text('Login');
                        createClassModalFormSubmitBtn.find('i').addClass('d-none');
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
