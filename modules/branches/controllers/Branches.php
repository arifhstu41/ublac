<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Branches extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (!is_admin()) {
            access_denied('Menu Setup');
        }

        $this->load->model("branches_model");
        $this->load->model("staff_model");
    }

    public function index()
    {
        if (!is_admin()) {
            access_denied('Estimate Request Access');
        }

        $staff       = $this->branches_model->tableData('staff');




        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('branches', 'table'));
        }

        $data['title'] = _l('branches');
        $this->load->view('manage', $data);
    }

    public function branch($id = '')
    {
        $data['members']       = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);

        if (!has_permission('branches', '', 'view')) {
            access_denied('branches');
        }
        if ($this->input->post()) {
            if ($id == '') {
                if (!has_permission('branches', '', 'create')) {
                    access_denied('branches');
                }
                $id = $this->branches_model->add($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('branch')));
                    redirect(admin_url('branches/branch/' . $id));
                }
            } else {
                if (!has_permission('branches', '', 'edit')) {
                    access_denied('branches');
                }
                $success = $this->branches_model->update($this->input->post(), $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('branch')));
                }
                redirect(admin_url('branches/branch/' . $id));
            }
        }
        if ($id == '') {
            $data['branch'] = [];
            $title = _l('add_new', _l('branch_lowercase'));
        } else {
            $data['branch']        = $this->branches_model->get($id);

            $title = _l('edit', _l('branch_lowercase'));
        }
        // prd($data['branch']);
        $data['title']                 = $title;
        $this->load->view('branch', $data);
    }

    public function remove_logo($id, $type)
    {

        if (!has_permission('branches', '', 'delete')) {
            access_denied('branches');
        }

        $branch = $this->branches_model->get($id);

        $logo_path = $branch->$type;

        $update[$type] = '';
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'branches', $update);

        if (file_exists($logo_path)) {
            unlink($logo_path);
        }

        redirect($_SERVER['HTTP_REFERER']);
    }

    public function delete($id)
    {

        $this->branches_model->delete($id);

        redirect(admin_url('branches/all_branches'));
    }
    public function makeDefault($id)
    {

        $this->branches_model->makeDefault($id);

        redirect(admin_url('branches/all_branches'));
    }

    public function get_branch_details()
    {

        $this->branches_model->get_branch_details();
    }


    
}
