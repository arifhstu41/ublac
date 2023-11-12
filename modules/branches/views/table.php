<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'branch',
    'user',
    'logo',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'branches';

// $join = ['LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'branches.staff_id'];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], [
    'id',
]);

// prd($result);
$output  = $result['output'];
$rResult = $result['rResult'];
// prd($result);
$staffMap = get_staff_map();

foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];

        if ($aColumns[$i] == 'user') {

            $data = json_decode($_data);

            $userHtml = '';

            if (is_array($data)) {
                $userNames = [];
                foreach ($data as $key => $value) {
                    $url = base_url() . 'admin/profile/' . $value;
                    $userHtml .= '<a href="' . $url . '"><img src="' . base_url() . '/assets/images/user-placeholder.jpg" data-toggle="tooltip" data-title="' . $staffMap[$value] . '" class="staff-profile-image-small mright5"></a>';

                    $userNames[] = $staffMap[$value];
                }

                $userHtml .= '<span class="hide">' . implode(" ", $userNames) . '</span>';
            }


            $_data = $userHtml;
        }
        if ($aColumns[$i] == 'logo') {

            if (strlen($_data) > 0) {
                $_data = "<img width='50' height='50' src='" . $_data . "' >";
            }
        }

        $row[] = $_data;
    }

    $options = icon_btn('branches/branch/' . $aRow['id'], 'pencil-square-o', 'btn-default');

    $row[]              = $options; //.= icon_btn('branches/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');


    $output['aaData'][] = $row;
}
