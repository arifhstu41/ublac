<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Branches_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param  integer (optional)
     * @return object
     * Get single branch
     */
    public function get($id = '', $exclude_notified = false)
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'branches')->row();
        }

        return $this->db->get(db_prefix() . 'branches')->result_array();
    }
    public function tableData($table)
    {
        return $this->db->get(db_prefix() . $table)->result_array();
    }

    /**
     * Add new branch
     * @param mixed $data All $_POST dat
     * @return mixed
     */
    public function add($data)
    {

        $logo = $this->uploadLogo("logo")['path'];
        if (!empty($logo)) {
            $insert_data['logo'] = $logo;
        }

        $insert_data['branch']      = $data['branch'] == '' ? '' : $data['branch'];
        $insert_data['invoice_prefix']      = $data['invoice_prefix'] == '' ? '' : $data['invoice_prefix'];
        $insert_data['invoice_postfix']      = $data['invoice_postfix'] == '' ? '' : $data['invoice_postfix'];
        $insert_data['estimate_prefix']      = $data['estimate_prefix'] == '' ? '' : $data['estimate_prefix'];
        $insert_data['estimate_postfix']      = $data['estimate_postfix'] == '' ? '' : $data['estimate_postfix'];
        $insert_data['branch_street']      = $data['branch_street'] == '' ? '' : $data['branch_street'];
        $insert_data['branch_city']      = $data['branch_city'] == '' ? '' : $data['branch_city'];
        $insert_data['branch_state']      = $data['branch_state'] == '' ? '' : $data['branch_state'];
        $insert_data['branch_zip']      = $data['branch_zip'] == '' ? '' : $data['branch_zip'];
        $insert_data['branch_country']      = $data['branch_country'] == '' ? '' : $data['branch_country'];
        $insert_data['user']      = json_encode($data['user']);

        $this->db->insert(db_prefix() . 'branches', $insert_data);

        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('New branches Added [ID:' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }

    public function uploadLogo($index)
    {

        $response['path'] = '';
        $response['status'] = false;

        if (isset($_FILES[$index]['name']) && $_FILES[$index]['name'] != '') {

            $path = FCPATH . 'uploads/branches' . '/';
            $basePath = base_url() . 'uploads/branches' . '/';
            // Get the temp file path
            $tmpFilePath = $_FILES[$index]['tmp_name'];
            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                // Getting file extension
                $extension          = strtolower(pathinfo($_FILES[$index]['name'], PATHINFO_EXTENSION));
                $allowed_extensions = [
                    'jpg',
                    'jpeg',
                    'png',
                    'gif',
                    'svg',
                ];

                $allowed_extensions = array_unique(
                    hooks()->apply_filters('company_logo_upload_allowed_extensions', $allowed_extensions)
                );

                if (!in_array($extension, $allowed_extensions)) {
                    set_alert('warning', 'Image extension not allowed.');
                }

                // Setup our new file path
                $filename    = md5($index . time()) . '.' . $extension;
                $newFilePath = $path . $filename;
                _maybe_create_upload_path($path);
                // Upload the file into the branches uploads dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    $response['path'] = $basePath . $filename;
                    $response['status'] = true;
                }
            }
        }

        return $response;
    }

    /**
     * Update branch
     * @param  mixed $data All $_POST data
     * @param  mixed $id   branch id
     * @return boolean
     */
    public function update($data, $id)
    {

        $logo = $this->uploadLogo("logo")['path'];
        if (!empty($logo)) {
            $insert_data['logo'] = $logo;
        }

        $insert_data['branch']      = $data['branch'] == '' ? '' : $data['branch'];
        $insert_data['invoice_prefix']      = $data['invoice_prefix'] == '' ? '' : $data['invoice_prefix'];
        $insert_data['invoice_postfix']      = $data['invoice_postfix'] == '' ? '' : $data['invoice_postfix'];
        $insert_data['estimate_prefix']      = $data['estimate_prefix'] == '' ? '' : $data['estimate_prefix'];
        $insert_data['estimate_postfix']      = $data['estimate_postfix'] == '' ? '' : $data['estimate_postfix'];
        $insert_data['branch_street']      = $data['branch_street'] == '' ? '' : $data['branch_street'];
        $insert_data['branch_city']      = $data['branch_city'] == '' ? '' : $data['branch_city'];
        $insert_data['branch_state']      = $data['branch_state'] == '' ? '' : $data['branch_state'];
        $insert_data['branch_zip']      = $data['branch_zip'] == '' ? '' : $data['branch_zip'];
        $insert_data['branch_country']      = $data['branch_country'] == '' ? '' : $data['branch_country'];
        $insert_data['user']      = json_encode($data['user']);


        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'branches', $insert_data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Branch Updated [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete branch
     * @param  mixed $id branch id
     * @return boolean
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'branches');
        if ($this->db->affected_rows() > 0) {
            log_activity('branch Deleted [ID:' . $id . ']');

            return true;
        }

        return false;
    }


    public function updateCustomField($id)
    {

        $this->db->from(db_prefix() . 'branches');
        // $this->db->where('is_default', 1);

        $defaultbranch = $this->db->get()->row_array();

        $this->db->from(db_prefix() . 'branches');
        $branches = $this->db->get()->result_array();
        $tables = ['customers', 'invoice', 'proposal', 'estimate', 'projects'];

        $names = array_column($branches, 'company_name');
        $branchNames = implode(',', $names);

        foreach ($tables as $key => $tableName) {

            $isExists = [];
            $customRow = [];

            $customRow['fieldto'] = $tableName;
            $customRow['name'] = "branches";
            $customRow['slug'] = $tableName . "_branches";
            $customRow['type'] = 'select';
            $customRow['active'] = 1;
            $customRow['bs_column'] = 12;

            $isExists = $this->db->get_where(db_prefix() . 'customfields', $customRow)->row_array();

            $customRow['default_value'] = $defaultbranch['company_name'];
            $customRow['options'] = $branchNames;

            if (empty($isExists)) {

                $this->db->insert(db_prefix() . 'customfields', $customRow);
            } else {
                $this->db->where('id', $isExists['id']);
                $status = $this->db->update(db_prefix() . 'customfields', $customRow);
            }
        }
    }

    public function get_branch_details()
    {
        $branches_id = $this->input->post('branches_id');
        $table_name = $this->input->post('table_name');
        $branch = [];

        if($branches_id > 0){

            $branch = $this->db->get_where(db_prefix() . 'branches', ['id' => $branches_id])->row_array();

            $this->db->select_max('number');
            $max = $this->db->get_where(db_prefix() . $table_name, ['branches_id' => $branches_id])->row_array();
    
            if($table_name == 'invoices'){

                $postfix = (int)$branch['invoice_postfix'];

                if(!empty($max['number'])){
                    
                    if($max['number'] < $postfix){
                        $number = $postfix + 1;
                    }else{
                        $number = $max['number'] + 1;
                    }
                    
                }else{
                    
                    $number = $postfix + 1;
                }
            }else{
                if(!empty($max['number'])){
                    if($max['number'] < $postfix){
                        $number = $postfix + 1;
                    }else{
                        $number = $max['number'] + 1;
                    }
                }else{
                    $number = (int)$branch['estimate_postfix'] + 1;
                }
            }
            
    
            $branch['number'] = $number;    
        }

        $result['branch'] = $branch; 
        $result['max'] = $max; 
        echo json_encode($result);
    }

    public function makeDefault($id)
    {

        $this->db->where('id > ', 0);
        $this->db->update(db_prefix() . 'branches', ["is_default" => 0]);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'branches', ["is_default" => 1]);

        $this->updateCustomField($id);
    }
}
