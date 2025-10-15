<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Product Master Controller
 */
class Product_master extends AdminController 
{
    public function __construct() 
    {
        parent::__construct();
        $this->load->model('product_master_model');
        $this->load->helper('product_master/product_master');
        hooks()->do_action('product_master_init');
    }

    /**
     * Groups management
     */
    public function groups()
    {
        if (!has_permission('product_master', '', 'view')) {
            access_denied('product_master');
        }

        if ($this->input->post()) {
            if (!has_permission('product_master', '', 'create') && !has_permission('product_master', '', 'edit')) {
                access_denied('product_master');
            }

            $data = $this->input->post();
            $id = $this->input->post('id');

            if ($id) {
                $success = $this->product_master_model->update_group($data, $id);
                if ($success === 'duplicate_code') {
                    set_alert('danger', 'Group code already exists. Please use a different code.');
                } elseif ($success) {
                    set_alert('success', _l('updated_successfully'));
                } else {
                    set_alert('danger', 'An error occurred while updating the group.');
                }
            } else {
                $result = $this->product_master_model->add_group($data);
                if ($result === 'duplicate_code') {
                    set_alert('danger', 'Group code already exists. Please use a different code.');
                } elseif ($result) {
                    set_alert('success', _l('added_successfully'));
                } else {
                    set_alert('danger', 'An error occurred while adding the group.');
                }
            }
            redirect(admin_url('product_master/groups'));
        }

        $data['title'] = _l('pm_groups');
        $data['groups'] = $this->product_master_model->get_groups();
        $this->load->view('groups', $data);
    }

    /**
     * Subgroups management
     */
    public function subgroups()
    {
        if (!has_permission('product_master', '', 'view')) {
            access_denied('product_master');
        }

        if ($this->input->post()) {
            if (!has_permission('product_master', '', 'create') && !has_permission('product_master', '', 'edit')) {
                access_denied('product_master');
            }

            $data = $this->input->post();
            $id = $this->input->post('id');

            if ($id) {
                $success = $this->product_master_model->update_subgroup($data, $id);
                if ($success === 'duplicate_code') {
                    set_alert('danger', 'Subgroup code already exists. Please use a different code.');
                } elseif ($success) {
                    set_alert('success', _l('updated_successfully'));
                } else {
                    set_alert('danger', 'An error occurred while updating the subgroup.');
                }
            } else {
                $result = $this->product_master_model->add_subgroup($data);
                if ($result === 'duplicate_code') {
                    set_alert('danger', 'Subgroup code already exists. Please use a different code.');
                } elseif ($result) {
                    set_alert('success', _l('added_successfully'));
                } else {
                    set_alert('danger', 'An error occurred while adding the subgroup.');
                }
            }
            redirect(admin_url('product_master/subgroups'));
        }

        $data['title'] = _l('pm_subgroups');
        $data['subgroups'] = $this->product_master_model->get_subgroups();
        $data['groups'] = $this->product_master_model->get_groups(['status' => 1]);
        $this->load->view('subgroups', $data);
    }

    /**
     * Categories management
     */
    public function categories()
    {
        if (!has_permission('product_master', '', 'view')) {
            access_denied('product_master');
        }

        if ($this->input->post()) {
            if (!has_permission('product_master', '', 'create') && !has_permission('product_master', '', 'edit')) {
                access_denied('product_master');
            }

            $data = $this->input->post();
            $id = $this->input->post('id');

            if ($id) {
                $success = $this->product_master_model->update_category($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully'));
                }
            } else {
                $id = $this->product_master_model->add_category($data);
                if ($id) {
                    set_alert('success', _l('added_successfully'));
                }
            }
            redirect(admin_url('product_master/categories'));
        }

        $data['title'] = _l('pm_categories');
        $data['categories'] = $this->product_master_model->get_categories();
        $data['subgroups'] = $this->product_master_model->get_subgroups(['status' => 1]);
        $this->load->view('categories', $data);
    }

    /**
     * Subcategories management
     */
    public function subcategories()
    {
        if (!has_permission('product_master', '', 'view')) {
            access_denied('product_master');
        }

        if ($this->input->post()) {
            if (!has_permission('product_master', '', 'create') && !has_permission('product_master', '', 'edit')) {
                access_denied('product_master');
            }

            $data = $this->input->post();
            $id = $this->input->post('id');

            if ($id) {
                $success = $this->product_master_model->update_subcategory($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully'));
                }
            } else {
                $id = $this->product_master_model->add_subcategory($data);
                if ($id) {
                    set_alert('success', _l('added_successfully'));
                }
            }
            redirect(admin_url('product_master/subcategories'));
        }

        $data['title'] = _l('pm_subcategories');
        $data['subcategories'] = $this->product_master_model->get_subcategories();
        $data['categories'] = $this->product_master_model->get_categories(['status' => 1]);
        $this->load->view('subcategories', $data);
    }

    /**
     * Units management
     */
    public function units()
    {
        if (!has_permission('product_master', '', 'view')) {
            access_denied('product_master');
        }

        if ($this->input->post()) {
            if (!has_permission('product_master', '', 'create') && !has_permission('product_master', '', 'edit')) {
                access_denied('product_master');
            }

            $data = $this->input->post();
            $id = $this->input->post('id');

            if ($id) {
                $success = $this->product_master_model->update_unit($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully'));
                }
            } else {
                $id = $this->product_master_model->add_unit($data);
                if ($id) {
                    set_alert('success', _l('added_successfully'));
                }
            }
            redirect(admin_url('product_master/units'));
        }

        $data['title'] = _l('pm_units');
        $data['units'] = $this->product_master_model->get_units();
        $this->load->view('units', $data);
    }

    /**
     * Multi Units management
     */
    public function multi_units()
    {
        if (!has_permission('product_master', '', 'view')) {
            access_denied('product_master');
        }

        if ($this->input->post()) {
            if (!has_permission('product_master', '', 'create') && !has_permission('product_master', '', 'edit')) {
                access_denied('product_master');
            }

            $data = $this->input->post();
            $id = $this->input->post('id');

            if ($id) {
                $success = $this->product_master_model->update_multi_unit($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully'));
                }
            } else {
                $id = $this->product_master_model->add_multi_unit($data);
                if ($id) {
                    set_alert('success', _l('added_successfully'));
                }
            }
            redirect(admin_url('product_master/multi_units'));
        }

        $data['title'] = _l('pm_multi_units');
        $data['multi_units'] = $this->product_master_model->get_multi_units();
        $data['units'] = $this->product_master_model->get_units(['status' => 1]);
        $this->load->view('multi_units', $data);
    }

    /**
     * Settings
     */
    public function settings()
    {
        if (!has_permission('product_master', '', 'edit') && !is_admin()) {
            access_denied('product_master');
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            foreach ($data as $key => $value) {
                update_option($key, $value);
            }
            set_alert('success', _l('settings_updated'));
            redirect(admin_url('product_master/settings'));
        }

        $data['title'] = _l('settings');
        $this->load->view('settings', $data);
    }

    /**
     * Get subgroups by group AJAX
     */
    public function get_subgroups_by_group($group_id)
    {
        $subgroups = $this->product_master_model->get_subgroups(['group_id' => $group_id, 'status' => 1]);
        echo json_encode($subgroups);
    }

    /**
     * Get categories by subgroup AJAX
     */
    public function get_categories_by_subgroup($subgroup_id)
    {
        $categories = $this->product_master_model->get_categories(['subgroup_id' => $subgroup_id, 'status' => 1]);
        echo json_encode($categories);
    }

    /**
     * Get subcategories by category AJAX
     */
    public function get_subcategories_by_category($category_id)
    {
        $subcategories = $this->product_master_model->get_subcategories(['category_id' => $category_id, 'status' => 1]);
        echo json_encode($subcategories);
    }

    /**
     * Get single group
     */
    public function get_group($id)
    {
        $group = $this->product_master_model->get_group($id);
        echo json_encode($group);
    }

    /**
     * Get single subgroup
     */
    public function get_subgroup($id)
    {
        $subgroup = $this->product_master_model->get_subgroup($id);
        echo json_encode($subgroup);
    }

    /**
     * Get single category
     */
    public function get_category($id)
    {
        $category = $this->product_master_model->get_category($id);
        echo json_encode($category);
    }

    /**
     * Get single subcategory
     */
    public function get_subcategory($id)
    {
        $subcategory = $this->product_master_model->get_subcategory($id);
        echo json_encode($subcategory);
    }

    /**
     * Get single unit
     */
    public function get_unit($id)
    {
        $unit = $this->product_master_model->get_unit($id);
        echo json_encode($unit);
    }

    /**
     * Get single multi unit
     */
    public function get_multi_unit($id)
    {
        $multi_unit = $this->product_master_model->get_multi_unit($id);
        echo json_encode($multi_unit);
    }

    /**
     * Delete item
     */
    public function delete($type, $id)
    {
        if (!has_permission('product_master', '', 'delete')) {
            access_denied('product_master');
        }

        $success = false;
        switch ($type) {
            case 'group':
                $success = $this->product_master_model->delete_group($id);
                break;
            case 'subgroup':
                $success = $this->product_master_model->delete_subgroup($id);
                break;
            case 'category':
                $success = $this->product_master_model->delete_category($id);
                break;
            case 'subcategory':
                $success = $this->product_master_model->delete_subcategory($id);
                break;
            case 'unit':
                $success = $this->product_master_model->delete_unit($id);
                break;
            case 'multi_unit':
                $success = $this->product_master_model->delete_multi_unit($id);
                break;
        }

        if ($success) {
            set_alert('success', _l('deleted'));
        } else {
            set_alert('warning', _l('problem_deleting'));
        }

        redirect($_SERVER['HTTP_REFERER']);
    }
}