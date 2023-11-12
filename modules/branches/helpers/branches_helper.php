<?php

defined('BASEPATH') or exit('No direct script access allowed');


function get_staff_map()
{

    $CI = &get_instance();

    $staff = $CI->db->get(db_prefix() . 'staff')->result_array();

    $staffMap = [];
    foreach ($staff as $each) {
        $staffMap[$each['staffid']] = $each['firstname'] . ' ' . $each['lastname'];
    }

    return $staffMap;
}

function user_branches($user_id)
{

    $CI = &get_instance();

    $branches = $CI->db->get(db_prefix() . 'branches')->result_array();

    $user_branches = [];
    foreach ($branches as $each) {

        if(strlen($each['user'])){
            $users = json_decode($each['user']);

            if(is_array($users) && in_array($user_id, $users)){
                $user_branch['id'] = $each['id'];
                $user_branch['branch'] = $each['branch'];

                $user_branches[] =  $user_branch;
            }

            
        }
    }

    return $user_branches;
}

function getPrefix($branches_id, $table_name = 'invoices' ){

    $CI           = & get_instance();

    $branch = $CI->db->get_where(db_prefix() . 'branches', ['id' => $branches_id])->row_array();

    $CI->db->select_max('number');
    $max = $CI->db->get_where(db_prefix() . $table_name , ['branches_id' => $branches_id])->row_array();

    if(!empty($max['number'])){
        $number = $max['number'] + 1;
    }else{
        $number = (int)$branch['postfix'] + 1;
    }

    $branch['number'] = $number;    
    
    if($table_name == 'invoices'){
        return $branch['invoice_prefix'];
    }else{
        return $branch['estimate_prefix'];
    }

    
}

function tableMap($table_name,$column_key, $index_key = "id", $where = []){

    $CI           = & get_instance();
    $data =  $CI->db->get_where(db_prefix().$table_name, $where)->result_array();

    return array_column($data, $column_key, $index_key);
   
}

function getResultArray($table_name, $where = [])
{

    $CI = &get_instance();
    if (!empty($where)) {
        $CI->db->where($where);
    }
    $result = $CI->db->get($table_name)->result_array();

    return $result;
}
function getResult($table_name, $where = [])
{

    $CI = &get_instance();
    if (!empty($where)) {
        $CI->db->where($where);
    }
    $result = $CI->db->get($table_name)->result();

    return $result;
}
function getRow($table_name, $where = [])
{

    $CI = &get_instance();
    if (!empty($where)) {
        $CI->db->where($where);
    }
    $result = $CI->db->get($table_name)->row();

    return $result;
}
function getRowArray($table_name, $where = [])
{

    $CI = &get_instance();
    if (!empty($where)) {
        $CI->db->where($where);
    }
    $result = $CI->db->get($table_name)->row_array();

    return $result;
}


// function get_module_info($module_name){

//     $CI          = &get_instance();
//     return $CI->db->get_where("tblmodules", ["module_name" => $module_name])->row();
// }

function prd($d)
{
    echo "<pre>";
    print_r($d);
    die;
}
function pre($d)
{
    echo "<pre>";
    print_r($d);
}
