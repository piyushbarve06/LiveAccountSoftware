<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="mtop40">
    <div class="col-md-4 col-md-offset-4 text-center">
        <h1 class="tw-font-semibold mbot20 login-heading">
            <?php
         echo _l(get_option('allow_registration') == 1 ? 'clients_login_heading_register' : 'clients_login_heading_no_register');
         ?>
        </h1>
    </div>
    <div class="col-md-4 col-md-offset-4 col-sm-8 col-sm-offset-2">
        <?php echo form_open($this->uri->uri_string(), ['class' => 'login-form']); ?>
        <?php hooks()->do_action('clients_login_form_start'); ?>
        <div class="panel_s">
            <div class="panel-body">

                <?php if (!is_language_disabled()) { ?>
                <div class="form-group">
                    <label for="language" class="control-label"><?php echo _l('language'); ?>
                    </label>
                    <select name="language" id="language" class="form-control selectpicker"
                        onchange="change_contact_language(this)"
                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                        data-live-search="true">
                        <?php $selected = (get_contact_language() != '') ? get_contact_language() : get_option('active_language'); ?>
                        <?php foreach ($this->app->get_available_languages() as $availableLanguage) { ?>
                        <option value="<?php echo $availableLanguage; ?>"
                            <?php echo ($availableLanguage == $selected) ? 'selected' : '' ?>>
                            <?php echo ucfirst($availableLanguage); ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>
                <?php } ?>

                <div class="form-group">
                    <label for="email"><?php echo _l('clients_login_email'); ?></label>
                    <input type="text" autofocus="true" class="form-control" name="email" id="email">
                    <?php echo form_error('email'); ?>
                </div>

                <div class="form-group">
                    <label for="password"><?php echo _l('clients_login_password'); ?></label>
                    <input type="password" class="form-control" name="password" id="password">
                    <?php echo form_error('password'); ?>
                </div>

                <?php if (show_recaptcha_in_customers_area()) { ?>
                <div class="g-recaptcha tw-mb-4" data-sitekey="<?php echo get_option('recaptcha_site_key'); ?>"></div>
                <?php echo form_error('g-recaptcha-response'); ?>
                <?php } ?>

                <div class="checkbox">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">
                        <?php echo _l('clients_login_remember'); ?>
                    </label>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">
                        <?php echo _l('clients_login_login_string'); ?>
                    </button>
                    <?php if (get_option('allow_registration') == 1) { ?>
                    <a href="<?php echo site_url('authentication/register'); ?>" class="btn btn-success btn-block">
                        <?php echo _l('clients_register_string'); ?>
                    </a>
                    <?php } ?>
                </div>
                <a href="<?php echo site_url('authentication/forgot_password'); ?>">
                    <?php echo _l('customer_forgot_password'); ?>
                </a>
                <?php hooks()->do_action('clients_login_form_end'); ?>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<script>
// Single session login with SweetAlert confirmation
$(document).ready(function() {
    var isForceLogin = false;
    var isChecking = false;
    
    // Intercept form submission with priority
    $('.login-form').off('submit').on('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        if (isForceLogin) {
            // Allow actual submission
            isForceLogin = false;
            $(this).unbind('submit').submit();
            return false;
        }
        
        if (isChecking) {
            return false;
        }
        
        isChecking = true;
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var btnText = submitBtn.html();
        
        // Disable button and show loading
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Checking...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize() + '&ajax_check=1',
            dataType: 'json',
            cache: false,
            success: function(response) {
                isChecking = false;
                if (response && response.already_logged_in === true) {
                    // Restore button first
                    submitBtn.prop('disabled', false).html(btnText);
                    
                    // Show SweetAlert confirmation
                    swal({
                        title: 'Already Logged In',
                        text: 'You are already logged in. If you want to login here, your other login will be logged out.',
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'OK, login here',
                        cancelButtonText: 'Cancel',
                        closeOnConfirm: true
                    }, function(isConfirm) {
                        if (isConfirm) {
                            // User confirmed, add force_login flag and submit
                            if (form.find('input[name="force_login"]').length === 0) {
                                $('<input>').attr({
                                    type: 'hidden',
                                    name: 'force_login',
                                    value: '1'
                                }).appendTo(form);
                            }
                            isForceLogin = true;
                            form.submit();
                        }
                    });
                } else {
                    // No conflict or error, submit normally
                    isForceLogin = true;
                    form.submit();
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', status, error);
                isChecking = false;
                // On error, submit normally
                isForceLogin = true;
                form.submit();
            }
        });
        
        return false;
    });
});
</script>