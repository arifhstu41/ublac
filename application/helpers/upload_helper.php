<?php

defined('BASEPATH') or exit('No direct script access allowed');


/**
 * Handle lead attachments if any
 * @param  mixed $leadid
 * @return boolean
 */
function handle_estimate_request_attachments($estimateRequestId, $index_name = 'file')
{
    $totalUploaded = 0;
    if (
        (isset($_FILES[$index_name]['name']) && !empty($_FILES[$index_name]['name'])) ||
        (isset($_FILES[$index_name]) && is_array($_FILES[$index_name]['name']) && count($_FILES[$index_name]['name']) > 0)
    ) {
        if (!is_array($_FILES[$index_name]['name'])) {
            $_FILES[$index_name]['name']     = [$_FILES[$index_name]['name']];
            $_FILES[$index_name]['type']     = [$_FILES[$index_name]['type']];
            $_FILES[$index_name]['tmp_name'] = [$_FILES[$index_name]['tmp_name']];
            $_FILES[$index_name]['error']    = [$_FILES[$index_name]['error']];
            $_FILES[$index_name]['size']     = [$_FILES[$index_name]['size']];
        }

        for ($i = 0; $i < count($_FILES[$index_name]['name']); $i++) {
            if (isset($_FILES[$index_name]) && empty($_FILES[$index_name]['name'][$i])) {
                continue;
            }

            if (isset($_FILES[$index_name][$i]) && _perfex_upload_error($_FILES[$index_name]['error'][$i])) {
                header('HTTP/1.0 400 Bad error');
                echo _perfex_upload_error($_FILES[$index_name]['error'][$i]);
                die;
            }

            $CI = & get_instance();
            if (isset($_FILES[$index_name]['name'][$i]) && $_FILES[$index_name]['name'][$i] != '') {
                hooks()->do_action('before_upload_estimate_request_attachment', $estimateRequestId);
                $path = get_upload_path_by_type('estimate_request') . $estimateRequestId . '/';
                // Get the temp file path
                $tmpFilePath = $_FILES[$index_name]['tmp_name'][$i];
                // Make sure we have a filepath

                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    if (!_upload_extension_allowed($_FILES[$index_name]['name'][$i])) {
                        continue;
                    }

                    _maybe_create_upload_path($path);

                    $filename    = unique_filename($path, $_FILES[$index_name]['name'][$i]);
                    $newFilePath = $path . $filename;
                    // Upload the file into the company uploads dir
                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        $CI = & get_instance();
                        $CI->load->model('estimate_request_model');
                        $data   = [];
                        $data[] = [
                            'file_name' => $filename,
                            'filetype'  => $_FILES[$index_name]['type'][$i],
                            ];
                        $CI->estimate_request_model->add_attachment_to_database($estimateRequestId, $data, false);
                        $totalUploaded++;
                    }
                }
            }
        }
    }

    if ($totalUploaded > 0) {
        return true;
    }

    return false;
}

/**
 * Handles uploads error with translation texts
 * @param  mixed $error type of error
 * @return mixed
 */
function _perfex_upload_error($error)
{
    $uploadErrors = [
        0 => _l('file_uploaded_success'),
        1 => _l('file_exceeds_max_filesize'),
        2 => _l('file_exceeds_maxfile_size_in_form'),
        3 => _l('file_uploaded_partially'),
        4 => _l('file_not_uploaded'),
        6 => _l('file_missing_temporary_folder'),
        7 => _l('file_failed_to_write_to_disk'),
        8 => _l('file_php_extension_blocked'),
    ];

    if (isset($uploadErrors[$error]) && $error != 0) {
        return $uploadErrors[$error];
    }

    return false;
}
/**
 * Newsfeed post attachments
 * @param  mixed $postid Post ID to add attachments
 * @return array  - Result values
 */
function handle_newsfeed_post_attachments($postid)
{
    if (isset($_FILES['file']) && _perfex_upload_error($_FILES['file']['error'])) {
        header('HTTP/1.0 400 Bad error');
        echo _perfex_upload_error($_FILES['file']['error']);
        die;
    }
    $path = get_upload_path_by_type('newsfeed') . $postid . '/';
    $CI   = & get_instance();
    if (isset($_FILES['file']['name'])) {
        hooks()->do_action('before_upload_newsfeed_attachment', $postid);
        $uploaded_files = false;
        // Get the temp file path
        $tmpFilePath = $_FILES['file']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);
            $filename = unique_filename($path, $_FILES['file']['name']);
            // In case client side validation is bypassed
            if (_upload_extension_allowed($filename)) {
                $newFilePath = $path . $filename;
                // Upload the file into the temp dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    $file_uploaded = true;
                    $attachment    = [];
                    $attachment[]  = [
                    'file_name' => $filename,
                    'filetype'  => $_FILES['file']['type'],
                    ];
                    $CI->misc_model->add_attachment_to_database($postid, 'newsfeed_post', $attachment);
                }
            }
        }
        if ($file_uploaded == true) {
            echo json_encode([
                'success' => true,
                'postid'  => $postid,
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'postid'  => $postid,
            ]);
        }
    }
}
/**
 * Handles upload for project files
 * @param  mixed $project_id project id
 * @return boolean
 */
function handle_project_file_uploads($project_id)
{
    $filesIDS = [];
    $errors   = [];

    if (isset($_FILES['file']['name'])
        && ($_FILES['file']['name'] != '' || is_array($_FILES['file']['name']) && count($_FILES['file']['name']) > 0)) {
        hooks()->do_action('before_upload_project_attachment', $project_id);

        if (!is_array($_FILES['file']['name'])) {
            $_FILES['file']['name']     = [$_FILES['file']['name']];
            $_FILES['file']['type']     = [$_FILES['file']['type']];
            $_FILES['file']['tmp_name'] = [$_FILES['file']['tmp_name']];
            $_FILES['file']['error']    = [$_FILES['file']['error']];
            $_FILES['file']['size']     = [$_FILES['file']['size']];
        }

        $path = get_upload_path_by_type('project') . $project_id . '/';

        for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
            if (_perfex_upload_error($_FILES['file']['error'][$i])) {
                $errors[$_FILES['file']['name'][$i]] = _perfex_upload_error($_FILES['file']['error'][$i]);

                continue;
            }

            // Get the temp file path
            $tmpFilePath = $_FILES['file']['tmp_name'][$i];
            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                _maybe_create_upload_path($path);
                $originalFilename = unique_filename($path, $_FILES['file']['name'][$i]);
                $filename = app_generate_hash() . '.' . get_file_extension($originalFilename);

                // In case client side validation is bypassed
                if (!_upload_extension_allowed($filename)) {
                    continue;
                }

                $newFilePath = $path . $filename;
                // Upload the file into the company uploads dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    $CI = & get_instance();
                    if (is_client_logged_in()) {
                        $contact_id = get_contact_user_id();
                        $staffid    = 0;
                    } else {
                        $staffid    = get_staff_user_id();
                        $contact_id = 0;
                    }
                    $data = [
                            'project_id' => $project_id,
                            'file_name'  => $filename,
                            'original_file_name'  => $originalFilename,
                            'filetype'   => $_FILES['file']['type'][$i],
                            'dateadded'  => date('Y-m-d H:i:s'),
                            'staffid'    => $staffid,
                            'contact_id' => $contact_id,
                            'subject'    => $originalFilename,
                        ];
                    if (is_client_logged_in()) {
                        $data['visible_to_customer'] = 1;
                    } else {
                        $data['visible_to_customer'] = ($CI->input->post('visible_to_customer') == 'true' ? 1 : 0);
                    }
                    $CI->db->insert(db_prefix() . 'project_files', $data);

                    $insert_id = $CI->db->insert_id();
                    if ($insert_id) {
                        if (is_image($newFilePath)) {
                            create_img_thumb($path, $filename);
                        }
                        array_push($filesIDS, $insert_id);
                    } else {
                        unlink($newFilePath);

                        return false;
                    }
                }
            }
        }
    }

    if (count($filesIDS) > 0) {
        $CI->load->model('projects_model');
        end($filesIDS);
        $lastFileID = key($filesIDS);
        $CI->projects_model->new_project_file_notification($filesIDS[$lastFileID], $project_id);
    }

    if (count($errors) > 0) {
        $message = '';
        foreach ($errors as $filename => $error_message) {
            $message .= $filename . ' - ' . $error_message . '<br />';
        }
        header('HTTP/1.0 400 Bad error');
        echo $message;
        die;
    }

    if (count($filesIDS) > 0) {
        return true;
    }

    return false;
}
/**
 * Handle contract attachments if any
 * @param  mixed $contractid
 * @return boolean
 */
function handle_contract_attachment($id)
{
    if (isset($_FILES['file']) && _perfex_upload_error($_FILES['file']['error'])) {
        header('HTTP/1.0 400 Bad error');
        echo _perfex_upload_error($_FILES['file']['error']);
        die;
    }
    if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
        hooks()->do_action('before_upload_contract_attachment', $id);
        $path = get_upload_path_by_type('contract') . $id . '/';
        // Get the temp file path
        $tmpFilePath = $_FILES['file']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);
            $filename    = unique_filename($path, $_FILES['file']['name']);
            $newFilePath = $path . $filename;
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $CI           = & get_instance();
                $attachment   = [];
                $attachment[] = [
                    'file_name' => $filename,
                    'filetype'  => $_FILES['file']['type'],
                    ];
                $CI->misc_model->add_attachment_to_database($id, 'contract', $attachment);

                return true;
            }
        }
    }

    return false;
}
/**
 * Handle lead attachments if any
 * @param  mixed $leadid
 * @return boolean
 */
function handle_lead_attachments($leadid, $index_name = 'file', $form_activity = false)
{
    $uploaded_files = [];
    $path           = get_upload_path_by_type('lead') . $leadid . '/';
    $CI             = &get_instance();
    $CI->load->model('leads_model');

    if (isset($_FILES[$index_name]['name'])
        && ($_FILES[$index_name]['name'] != ''
                || is_array($_FILES[$index_name]['name']) && count($_FILES[$index_name]['name']) > 0)) {
        if (!is_array($_FILES[$index_name]['name'])) {
            $_FILES[$index_name]['name']     = [$_FILES[$index_name]['name']];
            $_FILES[$index_name]['type']     = [$_FILES[$index_name]['type']];
            $_FILES[$index_name]['tmp_name'] = [$_FILES[$index_name]['tmp_name']];
            $_FILES[$index_name]['error']    = [$_FILES[$index_name]['error']];
            $_FILES[$index_name]['size']     = [$_FILES[$index_name]['size']];
        }

        _file_attachments_index_fix($index_name);

        for ($i = 0; $i < count($_FILES[$index_name]['name']); $i++) {
            // Get the temp file path
            $tmpFilePath = $_FILES[$index_name]['tmp_name'][$i];

            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                if (_perfex_upload_error($_FILES[$index_name]['error'][$i])
                    || !_upload_extension_allowed($_FILES[$index_name]['name'][$i])) {
                    continue;
                }

                _maybe_create_upload_path($path);
                $filename = unique_filename($path, $_FILES[$index_name]['name'][$i]);

                $newFilePath = $path . $filename;

                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    $CI->leads_model->add_attachment_to_database($leadid, [[
                        'file_name' => $filename,
                        'filetype'  => $_FILES[$index_name]['type'][$i],
                    ]], false, $form_activity);
                }
            }
        }
    }

    return true;
}

/**
 * Task attachments upload array
 * Multiple task attachments can be upload if input type is array or dropzone plugin is used
 * @param  mixed $taskid     task id
 * @param  string $index_name attachments index, in different forms different index name is used
 * @return mixed
 */
function handle_task_attachments_array($taskid, $index_name = 'attachments')
{
    $uploaded_files = [];
    $path           = get_upload_path_by_type('task') . $taskid . '/';
    $CI             = &get_instance();

    if (isset($_FILES[$index_name]['name'])
        && ($_FILES[$index_name]['name'] != '' || is_array($_FILES[$index_name]['name']) && count($_FILES[$index_name]['name']) > 0)) {
        if (!is_array($_FILES[$index_name]['name'])) {
            $_FILES[$index_name]['name']     = [$_FILES[$index_name]['name']];
            $_FILES[$index_name]['type']     = [$_FILES[$index_name]['type']];
            $_FILES[$index_name]['tmp_name'] = [$_FILES[$index_name]['tmp_name']];
            $_FILES[$index_name]['error']    = [$_FILES[$index_name]['error']];
            $_FILES[$index_name]['size']     = [$_FILES[$index_name]['size']];
        }

        _file_attachments_index_fix($index_name);
        for ($i = 0; $i < count($_FILES[$index_name]['name']); $i++) {
            // Get the temp file path
            $tmpFilePath = $_FILES[$index_name]['tmp_name'][$i];

            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                if (_perfex_upload_error($_FILES[$index_name]['error'][$i])
                    || !_upload_extension_allowed($_FILES[$index_name]['name'][$i])) {
                    continue;
                }

                _maybe_create_upload_path($path);
                $filename    = unique_filename($path, $_FILES[$index_name]['name'][$i]);
                $newFilePath = $path . $filename;

                // Upload the file into the temp dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    array_push($uploaded_files, [
                        'file_name' => $filename,
                        'filetype'  => $_FILES[$index_name]['type'][$i],
                    ]);

                    if (is_image($newFilePath)) {
                        create_img_thumb($path, $filename);
                    }
                }
            }
        }
    }

    if (count($uploaded_files) > 0) {
        return $uploaded_files;
    }

    return false;
}

/**
 * Invoice attachments
 * @param  mixed $invoiceid invoice ID to add attachments
 * @return array  - Result values
 */
function handle_sales_attachments($rel_id, $rel_type)
{
    if (isset($_FILES['file']) && _perfex_upload_error($_FILES['file']['error'])) {
        header('HTTP/1.0 400 Bad error');
        echo _perfex_upload_error($_FILES['file']['error']);
        die;
    }

    $path = get_upload_path_by_type($rel_type) . $rel_id . '/';

    $CI = & get_instance();
    if (isset($_FILES['file']['name'])) {
        $uploaded_files = false;
        $file_uploaded  = false;
        // Get the temp file path
        $tmpFilePath = $_FILES['file']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            // Getting file extension
            $type = $_FILES['file']['type'];
            _maybe_create_upload_path($path);
            $filename    = unique_filename($path, $_FILES['file']['name']);
            $newFilePath = $path . $filename;
            // Upload the file into the temp dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $file_uploaded = true;
                $attachment    = [];
                $attachment[]  = [
                    'file_name' => $filename,
                    'filetype'  => $type,
                    ];
                $insert_id = $CI->misc_model->add_attachment_to_database($rel_id, $rel_type, $attachment);
                // Get the key so we can return to ajax request and show download link
                $CI->db->where('id', $insert_id);
                $_attachment = $CI->db->get(db_prefix() . 'files')->row();
                $key         = $_attachment->attachment_key;

                if ($rel_type == 'invoice') {
                    $CI->load->model('invoices_model');
                    $CI->invoices_model->log_invoice_activity($rel_id, 'invoice_activity_added_attachment');
                } elseif ($rel_type == 'estimate') {
                    $CI->load->model('estimates_model');
                    $CI->estimates_model->log_estimate_activity($rel_id, 'estimate_activity_added_attachment');
                }
            }
        }

        if ($file_uploaded == true) {
            echo json_encode([
                'success'       => true,
                'attachment_id' => $insert_id,
                'filetype'      => $type,
                'rel_id'        => $rel_id,
                'file_name'     => $filename,
                'key'           => $key,
            ]);
        } else {
            echo json_encode([
                'success'   => false,
                'rel_id'    => $rel_id,
                'file_name' => $filename,
            ]);
        }
    }
}
/**
 * Client attachments
 * @param  mixed $clientid Client ID to add attachments
 * @return array  - Result values
 */
function handle_client_attachments_upload($id, $customer_upload = false)
{
    set_time_limit(-1);
    $path          = get_upload_path_by_type('customer') . $id . '/';
    $CI            = & get_instance();
    $totalUploaded = 0;

    if (isset($_FILES['file']['name'])
        && ($_FILES['file']['name'] != '' || is_array($_FILES['file']['name']) && count($_FILES['file']['name']) > 0)) {
        if (!is_array($_FILES['file']['name'])) {
            $_FILES['file']['name']     = [$_FILES['file']['name']];
            $_FILES['file']['type']     = [$_FILES['file']['type']];
            $_FILES['file']['tmp_name'] = [$_FILES['file']['tmp_name']];
            $_FILES['file']['error']    = [$_FILES['file']['error']];
            $_FILES['file']['size']     = [$_FILES['file']['size']];
        }

        _file_attachments_index_fix('file');
        for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
            hooks()->do_action('before_upload_client_attachment', $id);
            // Get the temp file path
            $tmpFilePath = $_FILES['file']['tmp_name'][$i];
            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                if (_perfex_upload_error($_FILES['file']['error'][$i])
                    || !_upload_extension_allowed($_FILES['file']['name'][$i])) {
                    continue;
                }

                _maybe_create_upload_path($path);
                $filename    = unique_filename($path, $_FILES['file']['name'][$i]);
                $newFilePath = $path . $filename;
                // Upload the file into the temp dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    $attachment   = [];
                    $attachment[] = [
                    'file_name' => $filename,
                    'filetype'  => $_FILES['file']['type'][$i],
                    ];

                    $contactUserId = get_contact_user_id();
                    $CI->load->model('Clients_model');
                    $contactdata = $CI->Clients_model->get_contact($contactUserId);
                    $contactfullname = trim($contactdata->firstname . ' ' . $contactdata->lastname);
                    $attachment[0]['cloudstorage_url'] = upload_to_nextcloud($tmpFilePath, $filename, $contactfullname);
                    if (is_image($newFilePath)) {
                        create_img_thumb($newFilePath, $filename);

                        $fileinfo = pathinfo($newFilePath);
                        $thumbfilepath = $fileinfo['dirname'] . '/'. $fileinfo['filename'] . "_thumb" . '.' .  $fileinfo['extension'];
                        if (file_exists($thumbfilepath) && !is_null($attachment[0]['cloudstorage_url'])) {

                            $thumbfilename = $fileinfo['filename'] . "_thumb" . '.' .  $fileinfo['extension'];
                            $attachment[0]['cloudstorage_thumb_url'] = upload_to_nextcloud($thumbfilepath, $thumbfilename, $contactfullname);
                        }
                    }

                    if ($customer_upload == true) {
                        $attachment[0]['staffid']          = 0;
                        $attachment[0]['contact_id']       = get_contact_user_id();
                        $attachment['visible_to_customer'] = 1;
                    }

                    $CI->misc_model->add_attachment_to_database($id, 'customer', $attachment);
                    $totalUploaded++;
                }
            }
        }
    }

    return (bool) $totalUploaded;
}
/**
 * Handles upload for expenses receipt
 * @param  mixed $id expense id
 * @return void
 */
function handle_expense_attachments($id)
{
    if (isset($_FILES['file']) && _perfex_upload_error($_FILES['file']['error'])) {
        header('HTTP/1.0 400 Bad error');
        echo _perfex_upload_error($_FILES['file']['error']);
        die;
    }
    $path = get_upload_path_by_type('expense') . $id . '/';
    $CI   = & get_instance();

    if (isset($_FILES['file']['name'])) {
        hooks()->do_action('before_upload_expense_attachment', $id);
        // Get the temp file path
        $tmpFilePath = $_FILES['file']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);
            $filename    = $_FILES['file']['name'];
            $newFilePath = $path . $filename;
            // Upload the file into the temp dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $attachment   = [];
                $attachment[] = [
                    'file_name' => $filename,
                    'filetype'  => $_FILES['file']['type'],
                    ];

                $CI->misc_model->add_attachment_to_database($id, 'expense', $attachment);
            }
        }
    }
}
/**
 * Check for ticket attachment after inserting ticket to database
 * @param  mixed $ticketid
 * @return mixed           false if no attachment || array uploaded attachments
 */
function handle_ticket_attachments($ticketid, $index_name = 'attachments')
{
    $path           = get_upload_path_by_type('ticket') . $ticketid . '/';
    $uploaded_files = [];

    if (isset($_FILES[$index_name])) {
        _file_attachments_index_fix($index_name);

        for ($i = 0; $i < count($_FILES[$index_name]['name']); $i++) {
            hooks()->do_action('before_upload_ticket_attachment', $ticketid);
            if ($i <= get_option('maximum_allowed_ticket_attachments')) {
                // Get the temp file path
                $tmpFilePath = $_FILES[$index_name]['tmp_name'][$i];
                // Make sure we have a filepath
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    // Getting file extension
                    $extension = strtolower(pathinfo($_FILES[$index_name]['name'][$i], PATHINFO_EXTENSION));

                    $allowed_extensions = explode(',', get_option('ticket_attachments_file_extensions'));
                    $allowed_extensions = array_map('trim', $allowed_extensions);
                    // Check for all cases if this extension is allowed
                    if (!in_array('.' . $extension, $allowed_extensions)) {
                        continue;
                    }
                    _maybe_create_upload_path($path);
                    $filename    = unique_filename($path, $_FILES[$index_name]['name'][$i]);
                    $newFilePath = $path . $filename;
                    // Upload the file into the temp dir
                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        array_push($uploaded_files, [
                                'file_name' => $filename,
                                'filetype'  => $_FILES[$index_name]['type'][$i],
                                ]);
                    }
                }
            }
        }
    }
    if (count($uploaded_files) > 0) {
        return $uploaded_files;
    }

    return false;
}
/**
 * Check for company logo upload
 * @return boolean
 */
function handle_company_logo_upload()
{
    $logoIndex = ['logo', 'logo_dark'];
    $success   = false;

    foreach ($logoIndex as $logo) {
        $index = 'company_' . $logo;

        if (isset($_FILES[$index]) && !empty($_FILES[$index]['name']) && _perfex_upload_error($_FILES[$index]['error'])) {
            set_alert('warning', _perfex_upload_error($_FILES[$index]['error']));

            return false;
        }
        if (isset($_FILES[$index]['name']) && $_FILES[$index]['name'] != '') {
            hooks()->do_action('before_upload_company_logo_attachment');
            $path = get_upload_path_by_type('company');
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

                    continue;
                }

                // Setup our new file path
                $filename    = md5($logo . time()) . '.' . $extension;
                $newFilePath = $path . $filename;
                _maybe_create_upload_path($path);
                // Upload the file into the company uploads dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    update_option($index, $filename);
                    $success = true;
                }
            }
        }
    }


    return $success;
}
/**
 * Check for company logo upload
 * @return boolean
 */
function handle_company_signature_upload()
{
    if (isset($_FILES['signature_image']) && _perfex_upload_error($_FILES['signature_image']['error'])) {
        set_alert('warning', _perfex_upload_error($_FILES['signature_image']['error']));

        return false;
    }
    if (isset($_FILES['signature_image']['name']) && $_FILES['signature_image']['name'] != '') {
        hooks()->do_action('before_upload_signature_image_attachment');
        $path = get_upload_path_by_type('company');
        // Get the temp file path
        $tmpFilePath = $_FILES['signature_image']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            // Getting file extension
            $path_parts = pathinfo($_FILES['signature_image']['name']);
            $extension  = $path_parts['extension'];
            $extension  = strtolower($extension);

            $allowed_extensions = [
                'jpg',
                'jpeg',
                'png',
            ];
            if (!in_array($extension, $allowed_extensions)) {
                set_alert('warning', 'Image extension not allowed.');

                return false;
            }
            // Setup our new file path
            $filename    = 'signature' . '.' . $extension;
            $newFilePath = $path . $filename;
            _maybe_create_upload_path($path);
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                update_option('signature_image', $filename);

                return true;
            }
        }
    }

    return false;
}
/**
 * Handle company favicon upload
 * @return boolean
 */
function handle_favicon_upload()
{
    if (isset($_FILES['favicon']['name']) && $_FILES['favicon']['name'] != '') {
        hooks()->do_action('before_upload_favicon_attachment');
        $path = get_upload_path_by_type('company');
        // Get the temp file path
        $tmpFilePath = $_FILES['favicon']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            // Getting file extension
            $path_parts = pathinfo($_FILES['favicon']['name']);
            $extension  = $path_parts['extension'];
            $extension  = strtolower($extension);
            // Setup our new file path
            $filename    = 'favicon' . '.' . $extension;
            $newFilePath = $path . $filename;
            _maybe_create_upload_path($path);
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                update_option('favicon', $filename);

                return true;
            }
        }
    }

    return false;
}

/**
 * Maybe upload staff profile image
 * @param  string $staff_id staff_id or current logged in staff id will be used if not passed
 * @return boolean
 */
function handle_staff_profile_image_upload($staff_id = '')
{
    if (!is_numeric($staff_id)) {
        $staff_id = get_staff_user_id();
    }
    if (isset($_FILES['profile_image']['name']) && $_FILES['profile_image']['name'] != '') {
        hooks()->do_action('before_upload_staff_profile_image');
        $path = get_upload_path_by_type('staff') . $staff_id . '/';
        // Get the temp file path
        $tmpFilePath = $_FILES['profile_image']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            // Getting file extension
            $extension          = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = [
                'jpg',
                'jpeg',
                'png',
            ];

            $allowed_extensions = hooks()->apply_filters('staff_profile_image_upload_allowed_extensions', $allowed_extensions);

            if (!in_array($extension, $allowed_extensions)) {
                set_alert('warning', _l('file_php_extension_blocked'));

                return false;
            }
            _maybe_create_upload_path($path);
            $filename    = unique_filename($path, $_FILES['profile_image']['name']);
            $newFilePath = $path . '/' . $filename;
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $CI                       = & get_instance();
                $config                   = [];
                $config['image_library']  = 'gd2';
                $config['source_image']   = $newFilePath;
                $config['new_image']      = 'thumb_' . $filename;
                $config['maintain_ratio'] = true;
                $config['width']          = hooks()->apply_filters('staff_profile_image_thumb_width', 320);
                $config['height']         = hooks()->apply_filters('staff_profile_image_thumb_height', 320);
                $CI->image_lib->initialize($config);
                $CI->image_lib->resize();
                $CI->image_lib->clear();
                $config['image_library']  = 'gd2';
                $config['source_image']   = $newFilePath;
                $config['new_image']      = 'small_' . $filename;
                $config['maintain_ratio'] = true;
                $config['width']          = hooks()->apply_filters('staff_profile_image_small_width', 96);
                $config['height']         = hooks()->apply_filters('staff_profile_image_small_height', 96);
                $CI->image_lib->initialize($config);
                $CI->image_lib->resize();
                $CI->db->where('staffid', $staff_id);
                $CI->db->update(db_prefix() . 'staff', [
                    'profile_image' => $filename,
                ]);
                // Remove original image
                unlink($newFilePath);

                return true;
            }
        }
    }

    return false;
}

/**
 * Maybe upload contact profile image
 * @param  string $contact_id contact_id or current logged in contact id will be used if not passed
 * @return boolean
 */
function handle_contact_profile_image_upload($contact_id = '')
{
    if (isset($_FILES['profile_image']['name']) && $_FILES['profile_image']['name'] != '') {
        hooks()->do_action('before_upload_contact_profile_image');
        if ($contact_id == '') {
            $contact_id = get_contact_user_id();
        }
        $path = get_upload_path_by_type('contact_profile_images') . $contact_id . '/';
        // Get the temp file path
        $tmpFilePath = $_FILES['profile_image']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            // Getting file extension
            $extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));

            $allowed_extensions = [
                'jpg',
                'jpeg',
                'png',
            ];

            $allowed_extensions = hooks()->apply_filters('contact_profile_image_upload_allowed_extensions', $allowed_extensions);

            if (!in_array($extension, $allowed_extensions)) {
                set_alert('warning', _l('file_php_extension_blocked'));

                return false;
            }
            _maybe_create_upload_path($path);
            $filename    = unique_filename($path, $_FILES['profile_image']['name']);
            $newFilePath = $path . $filename;
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $CI                       = & get_instance();
                $config                   = [];
                $config['image_library']  = 'gd2';
                $config['source_image']   = $newFilePath;
                $config['new_image']      = 'thumb_' . $filename;
                $config['maintain_ratio'] = true;
                $config['width']          = hooks()->apply_filters('contact_profile_image_thumb_width', 320);
                $config['height']         = hooks()->apply_filters('contact_profile_image_thumb_height', 320);
                $CI->image_lib->initialize($config);
                $CI->image_lib->resize();
                $CI->image_lib->clear();
                $config['image_library']  = 'gd2';
                $config['source_image']   = $newFilePath;
                $config['new_image']      = 'small_' . $filename;
                $config['maintain_ratio'] = true;
                $config['width']          = hooks()->apply_filters('contact_profile_image_small_width', 32);
                $config['height']         = hooks()->apply_filters('contact_profile_image_small_height', 32);
                $CI->image_lib->initialize($config);
                $CI->image_lib->resize();

                $CI->db->where('id', $contact_id);
                $CI->db->update(db_prefix() . 'contacts', [
                    'profile_image' => $filename,
                ]);
                // Remove original image
                unlink($newFilePath);

                return true;
            }
        }
    }

    return false;
}
/**
 * Handle upload for project discussions comment
 * Function for jquery-comment plugin
 * @param  mixed $discussion_id discussion id
 * @param  mixed $post_data     additional post data from the comment
 * @param  array $insert_data   insert data to be parsed if needed
 * @return arrray
 */
function handle_project_discussion_comment_attachments($discussion_id, $post_data, $insert_data)
{
    if (isset($_FILES['file']['name']) && _perfex_upload_error($_FILES['file']['error'])) {
        header('HTTP/1.0 400 Bad error');
        echo json_encode(['message' => _perfex_upload_error($_FILES['file']['error'])]);
        die;
    }

    if (isset($_FILES['file']['name'])) {
        hooks()->do_action('before_upload_project_discussion_comment_attachment');
        $path = PROJECT_DISCUSSION_ATTACHMENT_FOLDER . $discussion_id . '/';

        // Check for all cases if this extension is allowed
        if (!_upload_extension_allowed($_FILES['file']['name'])) {
            header('HTTP/1.0 400 Bad error');
            echo json_encode(['message' => _l('file_php_extension_blocked')]);
            die;
        }

        // Get the temp file path
        $tmpFilePath = $_FILES['file']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);
            $filename    = unique_filename($path, $_FILES['file']['name']);
            $newFilePath = $path . $filename;
            // Upload the file into the temp dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $insert_data['file_name'] = $filename;

                if (isset($_FILES['file']['type'])) {
                    $insert_data['file_mime_type'] = $_FILES['file']['type'];
                } else {
                    $insert_data['file_mime_type'] = get_mime_by_extension($filename);
                }
            }
        }
    }

    return $insert_data;
}

/**
 * Create thumbnail from image
 * @param  string  $path     imat path
 * @param  string  $filename filename to store
 * @param  integer $width    width of thumb
 * @param  integer $height   height of thumb
 * @return null
 */
function create_img_thumb($path, $filename, $width = 300, $height = 300)
{
    $CI = &get_instance();

    $source_path  = rtrim($path, '/') . '/' . $filename;
    $target_path  = $path;
    $config_manip = [
        'image_library'  => 'gd2',
        'source_image'   => $source_path,
        'new_image'      => $target_path,
        'maintain_ratio' => true,
        'create_thumb'   => true,
        'thumb_marker'   => '_thumb',
        'width'          => $width,
        'height'         => $height,
    ];

    $CI->image_lib->initialize($config_manip);
    $CI->image_lib->resize();
    $CI->image_lib->clear();
}

/**
 * Check if extension is allowed for upload
 * @param  string $filename filename
 * @return boolean
 */
function _upload_extension_allowed($filename)
{
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $browser = get_instance()->agent->browser();

    $allowed_extensions = explode(',', get_option('allowed_files'));
    $allowed_extensions = array_map('trim', $allowed_extensions);

    //  https://discussions.apple.com/thread/7229860
    //  Used in main.js too for Dropzone
    if (strtolower($browser) === 'safari'
        && in_array('.jpg', $allowed_extensions)
        && !in_array('.jpeg', $allowed_extensions)
    ) {
        $allowed_extensions[] = '.jpeg';
    }
    // Check for all cases if this extension is allowed
    if (!in_array('.' . $extension, $allowed_extensions)) {
        return false;
    }

    return true;
}

/**
 * Performs fixes when $_FILES is array and the index is messed up
 * Eq user click on + then remove the file and then added new file
 * In this case the indexes will be 0,2 - 1 is missing because it's removed but they should be 0,1
 * @param  string $index_name $_FILES index name
 * @return null
 */
function _file_attachments_index_fix($index_name)
{
    if (isset($_FILES[$index_name]['name']) && is_array($_FILES[$index_name]['name'])) {
        $_FILES[$index_name]['name'] = array_values($_FILES[$index_name]['name']);
    }

    if (isset($_FILES[$index_name]['type']) && is_array($_FILES[$index_name]['type'])) {
        $_FILES[$index_name]['type'] = array_values($_FILES[$index_name]['type']);
    }

    if (isset($_FILES[$index_name]['tmp_name']) && is_array($_FILES[$index_name]['tmp_name'])) {
        $_FILES[$index_name]['tmp_name'] = array_values($_FILES[$index_name]['tmp_name']);
    }

    if (isset($_FILES[$index_name]['error']) && is_array($_FILES[$index_name]['error'])) {
        $_FILES[$index_name]['error'] = array_values($_FILES[$index_name]['error']);
    }

    if (isset($_FILES[$index_name]['size']) && is_array($_FILES[$index_name]['size'])) {
        $_FILES[$index_name]['size'] = array_values($_FILES[$index_name]['size']);
    }
}

/**
 * Check if path exists if not exists will create one
 * This is used when uploading files
 * @param  string $path path to check
 * @return null
 */
function _maybe_create_upload_path($path)
{
    if (!file_exists($path)) {
        mkdir($path, 0755);
        fopen(rtrim($path, '/') . '/' . 'index.html', 'w');
    }
}

/**
 * Function that return full path for upload based on passed type
 * @param  string $type
 * @return string
 */
function get_upload_path_by_type($type)
{
    $path = '';
    switch ($type) {
        case 'lead':
            $path = LEAD_ATTACHMENTS_FOLDER;

        break;
        case 'expense':
            $path = EXPENSE_ATTACHMENTS_FOLDER;

        break;
        case 'project':
            $path = PROJECT_ATTACHMENTS_FOLDER;

        break;
        case 'proposal':
            $path = PROPOSAL_ATTACHMENTS_FOLDER;

        break;
        case 'estimate':
            $path = ESTIMATE_ATTACHMENTS_FOLDER;

        break;
        case 'invoice':
            $path = INVOICE_ATTACHMENTS_FOLDER;

        break;
        case 'credit_note':
            $path = CREDIT_NOTES_ATTACHMENTS_FOLDER;

        break;
        case 'task':
            $path = TASKS_ATTACHMENTS_FOLDER;

        break;
        case 'contract':
            $path = CONTRACTS_UPLOADS_FOLDER;

        break;
        case 'customer':
            $path = CLIENT_ATTACHMENTS_FOLDER;

        break;
        case 'staff':
        $path = STAFF_PROFILE_IMAGES_FOLDER;

        break;
        case 'company':
        $path = COMPANY_FILES_FOLDER;

        break;
        case 'ticket':
        $path = TICKET_ATTACHMENTS_FOLDER;

        break;
        case 'contact_profile_images':
        $path = CONTACT_PROFILE_IMAGES_FOLDER;

        break;
        case 'newsfeed':
        $path = NEWSFEED_FOLDER;

        break;
        case 'estimate_request':
        $path = NEWSFEED_FOLDER;

        break;
    }

    return hooks()->apply_filters('get_upload_path_by_type', $path, $type);
}

/**
 * Function that uploads file to nextcloud
 * @param  string $tmpFilePath from formdata
 * @param  string $filename unique filename
 * @param  mixed $clientid client id
 * @return string
 */
function upload_to_nextcloud($tmpFilePath, $filename, $contactfullname)
{
    $username = 'ariful.fb';
    $password = 'arifHsut41@!';
    $baseurl = 'https://drive.ublac.com/remote.php/dav/files/'.$username;
    $file = $tmpFilePath;
    
    $file_url = NULL;
    $path = $contactfullname;
    $url = $baseurl . "/" . $path;
    $responseFolderExistData = check_folder_exists_in_nextcloud($url, $username, $password);
    if (@$responseFolderExistData['does_folder_exist']) {

        $response = upload_file_in_nextcloud($url, $filename, $username, $password, $file);

        if ($response['status']) {
            $file_url = $response['file_url'];
        }

    } else if (@$responseFolderExistData['does_folder_exist'] === false) {

        $response = create_folder_in_nextcloud($url, $username, $password);

        if ($response['status']) {

            $response = upload_file_in_nextcloud($url, $filename, $username, $password, $file);
            if ($response['status']) {

                $file_url = $response['file_url'];
            }
        }
    }

    return $file_url;
}

function check_folder_exists_in_nextcloud($url, $username, $password)
{
    $client = new \GuzzleHttp\Client();
    $headers = [
        'Authorization' => 'Basic ' . base64_encode($username.':'.$password),
        'Content-Type' => 'application/xml; charset=utf-8',
    ];
    try {

        $request = new \GuzzleHttp\Psr7\Request('PROPFIND', $url, $headers);
        $res = $client->sendAsync($request)->wait();
        
    } catch (GuzzleHttp\Exception\ClientException $e) {

        if ($e->hasResponse() && $e->getResponse()->getStatusCode() == 404) {
            // Handle the 404 error here
            $response['status'] = 0;
            $response['statusCode'] = 404;
            $response['message'] = $e->getMessage();
            $response['does_folder_exist'] = false;
        } else {
            // Re-throw the exception if it's not a 404 error
            $response['status'] = 0;
            $response['statusCode'] = $e->getResponse()->getStatusCode();
            $response['message'] = $e->getMessage();
            $response['does_folder_exist'] = false;
        }

        return $response;
    }

    $statusCode = $res->getStatusCode();
    if($statusCode >= 400) {
        
        $response['status'] = 0;
        $response['statusCode'] = $statusCode;
        $response['message'] = $res->getBody();
    } else if ($statusCode === 207) {

        $response['does_folder_exist'] = true;
        $response['message'] = "Folder exists";
    } else {

        $response['does_folder_exist'] = false;
        $response['message'] = "Folder does not exist";
    }

    return $response;
}

function create_folder_in_nextcloud($url, $username, $password)
{
    $response = array(
        'status' => 0,
    );

    $client = new \GuzzleHttp\Client();
    $res = $client->request('MKCOL', $url, [
        'auth' => [$username, $password]
    ]);

    $statusCode = $res->getStatusCode();
    if ($statusCode >= 200 && $statusCode < 300) {

        $response['status'] = 1;
    } else {
        
        $response['message'] = $res->getBody();
    }

    return $response;
}

function upload_file_in_nextcloud($url, $filename, $username, $password, $file)
{
    $client = new \GuzzleHttp\Client();
    $contents = file_get_contents($file); // Replace with the path to your local file

    $res = $client->request('PUT', $url . '/' . $filename, [
        'auth' => [$username, $password],
        'body' => $contents
    ]);

    $statusCode = $res->getStatusCode();

    $response["status"] = 1;
    if($statusCode >= 400) {
        
        $response["status"] = 0;
        $response["message"] = $res->getBody();
    } else {

        $file_url = $url . '/' . $filename;
        $response["file_url"] = $file_url;
    }

    return $response;
}
