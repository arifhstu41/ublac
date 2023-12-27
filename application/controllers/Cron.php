<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cron extends App_Controller
{
    public function index($key = '')
    {
        update_option('cron_has_run_from_cli', 1);

        if (defined('APP_CRON_KEY') && (APP_CRON_KEY != $key)) {
            header('HTTP/1.0 401 Unauthorized');
            die('Passed cron job key is not correct. The cron job key should be the same like the one defined in APP_CRON_KEY constant.');
        }

        $last_cron_run                  = get_option('last_cron_run');
        $seconds = hooks()->apply_filters('cron_functions_execute_seconds', 300);

        if ($last_cron_run == '' || (time() > ($last_cron_run + $seconds))) {
            $this->load->model('cron_model');
            $this->cron_model->run();
        }
    }

    public function updateTable($branchId){

        // $this->db->where("branches_id", '0');
        // $this->db->update("tblinvoices", ["branches_id" => $branchId]);

        // $this->db->where("branches_id", '0');
        // $this->db->update("tblestimates", ["branches_id" => $branchId]);

        // $this->db->where("branches_id", '0');
        // $this->db->update("tblproposals", ["branches_id" => $branchId]);

        $this->db->where("branches_id", '0');
        $this->db->update("tblclients", ["branches_id" => $branchId]);

        $this->db->where("branches_id", '0');
        $this->db->update("tblleads", ["branches_id" => $branchId]);

        echo $this->db->last_query();
    }

    public function checkTime(){
        $this->load->helper('date_helper');
        echo date_default_timezone_get();
        echo date("y-m-d H:i:s", now());die;
    }
}
