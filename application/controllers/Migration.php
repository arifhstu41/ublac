<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration extends App_Controller
{

    public function updatedata(){
        
        error_reporting(-1);
		ini_set('display_errors', 1);
		
        $this->db->where('version', '294');
        echo $this->db->update(db_prefix() . 'migrations', ['version' => 306]);
        
        echo $this->db->last_query();
        
        $data = $this->db->get(db_prefix() . 'migrations')->result_array();
    }
    public function migrate()
    {
        if (!$this->db->field_exists('quantity', db_prefix() . 'items')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . "items`
            ADD COLUMN `quantity` int(11) NULL DEFAULT '0';");
        }
        if (!$this->db->field_exists('quantity', db_prefix() . 'itemable')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . "itemable`
            ADD COLUMN `quantity` int(11) NULL DEFAULT '0';");
        }
        if (!$this->db->field_exists('quantity_unit', db_prefix() . 'items')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . "items`
            ADD COLUMN `quantity_unit` varchar(100) NULL DEFAULT '0';");
        }
        if (!$this->db->field_exists('quantity_unit', db_prefix() . 'itemable')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . "itemable`
            ADD COLUMN `quantity_unit` varchar(100) NULL DEFAULT '0';");
        }

        if (!$this->db->field_exists('cloudstorage_url', db_prefix() . 'files')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . "files`
            ADD COLUMN `cloudstorage_url` MEDIUMTEXT NULL;");
        }

        if (!$this->db->field_exists('cloudstorage_thumb_url', db_prefix() . 'files')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . "files`
            ADD COLUMN `cloudstorage_thumb_url` MEDIUMTEXT NULL;");
        }

        echo $this->db->last_query();
    }

    public function make()
    {
        $this->load->config('migration');

        if ($this->config->item('migration_enabled') !== true) {
            echo '<h1>Set config item <b>migration_enabled</b> to TRUE in <b>application/config/migration.php</b></h1>';
            die;
        }

        if (!$this->input->get('old_base_url')) {
            echo '<h1>
                You need to pass old base url in the url like: ' . site_url('migration/make?old_base_url=http://myoldbaseurl.com/')
                . '</h1>';
            die;
        }

        $old_url = $this->input->get('old_base_url');
        $new_url = $this->config->item('base_url');
        if (!endsWith($old_url, '/')) {
            $old_url = $old_url . '/';
        }

        $tables = [
                [
                    'table' => db_prefix() . 'notifications',
                    'field' => 'description',
                ],
                [
                    'table' => db_prefix() . 'notifications',
                    'field' => 'additional_data',
                ],
                [
                    'table' => db_prefix() . 'notes',
                    'field' => 'description',
                ],
                [
                    'table' => db_prefix() . 'emailtemplates',
                    'field' => 'message',
                ],
                [
                    'table' => db_prefix() . 'newsfeed_posts',
                    'field' => 'content',
                ],
                [
                    'table' => db_prefix() . 'newsfeed_post_comments',
                    'field' => 'content',
                ],
                [
                    'table' => db_prefix() . 'options',
                    'field' => 'value',
                ],
                [
                    'table' => db_prefix() . 'staff',
                    'field' => 'email_signature',
                ],
                [
                    'table' => db_prefix() . 'tickets_predefined_replies',
                    'field' => 'message',
                ],
                [
                    'table' => db_prefix() . 'projectdiscussioncomments',
                    'field' => 'content',
                ],
                [
                    'table' => db_prefix() . 'projectdiscussions',
                    'field' => 'description',
                ],
                [
                    'table' => db_prefix() . 'project_notes',
                    'field' => 'content',
                ],
                [
                    'table' => db_prefix() . 'projects',
                    'field' => 'description',
                ],
                [
                    'table' => db_prefix() . 'reminders',
                    'field' => 'description',
                ],
                [
                    'table' => db_prefix() . 'tasks',
                    'field' => 'description',
                ],
                [
                    'table' => db_prefix() . 'task_comments',
                    'field' => 'content',
                ],
                [
                    'table' => db_prefix() . 'ticket_replies',
                    'field' => 'message',
                ],
                [
                    'table' => db_prefix() . 'tickets',
                    'field' => 'message',
                ],
                [
                    'table' => db_prefix() . 'todos',
                    'field' => 'description',
                ],
                [
                    'table' => db_prefix() . 'proposal_comments',
                    'field' => 'content',
                ],
                [
                    'table' => db_prefix() . 'proposals',
                    'field' => 'content',
                ],
                [
                    'table' => db_prefix() . 'lead_activity_log',
                    'field' => 'description',
                ],
                [
                    'table' => db_prefix() . 'knowledge_base_groups',
                    'field' => 'description',
                ],
                [
                    'table' => db_prefix() . 'knowledge_base',
                    'field' => 'description',
                ],
                [
                    'table' => db_prefix() . 'invoices',
                    'field' => 'terms',
                ],
                [
                    'table' => db_prefix() . 'invoices',
                    'field' => 'clientnote',
                ],
                [
                    'table' => db_prefix() . 'invoices',
                    'field' => 'adminnote',
                ],
                [
                    'table' => db_prefix() . 'creditnotes',
                    'field' => 'terms',
                ],
                [
                    'table' => db_prefix() . 'creditnotes',
                    'field' => 'clientnote',
                ],
                [
                    'table' => db_prefix() . 'creditnotes',
                    'field' => 'adminnote',
                ],
                [
                    'table' => db_prefix() . 'milestones',
                    'field' => 'description',
                ],
                [
                    'table' => db_prefix() . 'sales_activity',
                    'field' => 'description',
                ],
                [
                    'table' => db_prefix() . 'sales_activity',
                    'field' => 'additional_data',
                ],
                [
                    'table' => db_prefix() . 'estimates',
                    'field' => 'terms',
                ],
                [
                    'table' => db_prefix() . 'estimates',
                    'field' => 'clientnote',
                ],
                [
                    'table' => db_prefix() . 'estimates',
                    'field' => 'adminnote',
                ],
                [
                    'table' => db_prefix() . 'contracts',
                    'field' => 'description',
                ],
                [
                    'table' => db_prefix() . 'contract_comments',
                    'field' => 'content',
                ],
                [
                    'table' => db_prefix() . 'contracts',
                    'field' => 'content',
                ],
                [
                    'table' => db_prefix() . 'activity_log',
                    'field' => 'description',
                ],
                [
                    'table' => db_prefix() . 'announcements',
                    'field' => 'message',
                ],
                [
                    'table' => db_prefix() . 'consent_purposes',
                    'field' => 'description',
                ],
                [
                    'table' => db_prefix() . 'consents',
                    'field' => 'description',
                ],
                [
                    'table' => db_prefix() . 'consents',
                    'field' => 'opt_in_purpose_description',
                ],
                [
                    'table' => db_prefix() . 'vault',
                    'field' => 'description',
                ],
            ];

        $tables = hooks()->apply_filters('migration_tables_to_replace_old_links', $tables);

        $affectedRows = 0;

        foreach ($tables as $t) {
            $this->db->query('UPDATE `' . $t['table'] . '` SET `' . $t['field'] . '` = replace(' . $t['field'] . ', "' . $old_url . '", "' . $new_url . '")');

            $affectedRows += $this->db->affected_rows();
        }

        echo '<h1>Total links replaced: ' . $affectedRows . '</h1>';
    }
}
