<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?= _l('pm_settings'); ?></h4>
                        <hr class="hr-panel-heading" />
                        
                        <?= form_open(admin_url('product_master/settings')); ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pm_auto_generate_codes"><?= _l('pm_auto_generate_codes'); ?></label>
                                    <select name="pm_auto_generate_codes" id="pm_auto_generate_codes" class="form-control selectpicker">
                                        <option value="1" <?= get_option('pm_auto_generate_codes') == '1' ? 'selected' : ''; ?>><?= _l('yes'); ?></option>
                                        <option value="0" <?= get_option('pm_auto_generate_codes') == '0' ? 'selected' : ''; ?>><?= _l('no'); ?></option>
                                    </select>
                                    <small class="help-block"><?= _l('pm_auto_generate_codes_help'); ?></small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pm_require_approval"><?= _l('pm_require_approval'); ?></label>
                                    <select name="pm_require_approval" id="pm_require_approval" class="form-control selectpicker">
                                        <option value="1" <?= get_option('pm_require_approval') == '1' ? 'selected' : ''; ?>><?= _l('yes'); ?></option>
                                        <option value="0" <?= get_option('pm_require_approval') == '0' ? 'selected' : ''; ?>><?= _l('no'); ?></option>
                                    </select>
                                    <small class="help-block"><?= _l('pm_require_approval_help'); ?></small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <?= render_input('pm_code_prefix', 'pm_code_prefix', get_option('pm_code_prefix'), 'text', ['maxlength' => 10]); ?>
                                <small class="help-block"><?= _l('pm_code_prefix_help'); ?></small>
                            </div>
                            
                            <div class="col-md-6">
                                <?= render_input('pm_code_length', 'pm_code_length', get_option('pm_code_length') ?: '6', 'number', ['min' => 3, 'max' => 20]); ?>
                                <small class="help-block"><?= _l('pm_code_length_help'); ?></small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pm_enable_categories"><?= _l('pm_enable_categories'); ?></label>
                                    <select name="pm_enable_categories" id="pm_enable_categories" class="form-control selectpicker">
                                        <option value="1" <?= get_option('pm_enable_categories') != '0' ? 'selected' : ''; ?>><?= _l('yes'); ?></option>
                                        <option value="0" <?= get_option('pm_enable_categories') == '0' ? 'selected' : ''; ?>><?= _l('no'); ?></option>
                                    </select>
                                    <small class="help-block"><?= _l('pm_enable_categories_help'); ?></small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pm_enable_subcategories"><?= _l('pm_enable_subcategories'); ?></label>
                                    <select name="pm_enable_subcategories" id="pm_enable_subcategories" class="form-control selectpicker">
                                        <option value="1" <?= get_option('pm_enable_subcategories') != '0' ? 'selected' : ''; ?>><?= _l('yes'); ?></option>
                                        <option value="0" <?= get_option('pm_enable_subcategories') == '0' ? 'selected' : ''; ?>><?= _l('no'); ?></option>
                                    </select>
                                    <small class="help-block"><?= _l('pm_enable_subcategories_help'); ?></small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pm_enable_multi_units"><?= _l('pm_enable_multi_units'); ?></label>
                                    <select name="pm_enable_multi_units" id="pm_enable_multi_units" class="form-control selectpicker">
                                        <option value="1" <?= get_option('pm_enable_multi_units') != '0' ? 'selected' : ''; ?>><?= _l('yes'); ?></option>
                                        <option value="0" <?= get_option('pm_enable_multi_units') == '0' ? 'selected' : ''; ?>><?= _l('no'); ?></option>
                                    </select>
                                    <small class="help-block"><?= _l('pm_enable_multi_units_help'); ?></small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pm_default_status"><?= _l('pm_default_status'); ?></label>
                                    <select name="pm_default_status" id="pm_default_status" class="form-control selectpicker">
                                        <option value="1" <?= get_option('pm_default_status') != '0' ? 'selected' : ''; ?>><?= _l('active'); ?></option>
                                        <option value="0" <?= get_option('pm_default_status') == '0' ? 'selected' : ''; ?>><?= _l('inactive'); ?></option>
                                    </select>
                                    <small class="help-block"><?= _l('pm_default_status_help'); ?></small>
                                </div>
                            </div>
                        </div>
                        
                        <hr />
                        
                        <h5><?= _l('pm_data_management'); ?></h5>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i>
                                    <?= _l('pm_data_management_info'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pm_allow_duplicate_codes"><?= _l('pm_allow_duplicate_codes'); ?></label>
                                    <select name="pm_allow_duplicate_codes" id="pm_allow_duplicate_codes" class="form-control selectpicker">
                                        <option value="0" <?= get_option('pm_allow_duplicate_codes') == '0' ? 'selected' : ''; ?>><?= _l('no'); ?></option>
                                        <option value="1" <?= get_option('pm_allow_duplicate_codes') == '1' ? 'selected' : ''; ?>><?= _l('yes'); ?></option>
                                    </select>
                                    <small class="help-block"><?= _l('pm_allow_duplicate_codes_help'); ?></small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pm_cascade_delete"><?= _l('pm_cascade_delete'); ?></label>
                                    <select name="pm_cascade_delete" id="pm_cascade_delete" class="form-control selectpicker">
                                        <option value="1" <?= get_option('pm_cascade_delete') != '0' ? 'selected' : ''; ?>><?= _l('yes'); ?></option>
                                        <option value="0" <?= get_option('pm_cascade_delete') == '0' ? 'selected' : ''; ?>><?= _l('no'); ?></option>
                                    </select>
                                    <small class="help-block"><?= _l('pm_cascade_delete_help'); ?></small>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-info pull-right"><?= _l('settings_save'); ?></button>
                        <div class="clearfix"></div>
                        
                        <?= form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>