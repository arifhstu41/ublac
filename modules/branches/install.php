<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();


// $CI->db->query('DROP TABLE `' . db_prefix() . 'branches`;');

if (!$CI->db->table_exists(db_prefix() . 'branches')) {
  $CI->db->query('CREATE TABLE `' . db_prefix() . 'branches` (
  `id` int(11) NOT NULL,
  `logo` varchar(245) NOT NULL,
  `branch_street` varchar(245) NOT NULL,
  `branch_city` varchar(245) NOT NULL,
  `branch_state` varchar(245) NOT NULL,
  `branch_zip` varchar(245) NOT NULL,
  `branch_country` varchar(245) NOT NULL,
  `user` varchar(245) NOT NULL,
  `branch` varchar(245) NOT NULL,
  `invoice_prefix` varchar(45) NOT NULL,
  `invoice_postfix` int(11) NOT NULL,
  `estimate_prefix` varchar(45) NOT NULL,
  `estimate_postfix` int(11) NOT NULL,
  `creation_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

  $CI->db->query('ALTER TABLE `' . db_prefix() . 'branches`
  ADD PRIMARY KEY (`id`);');

  $CI->db->query('ALTER TABLE `' . db_prefix() . 'branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');
}

if (!$CI->db->field_exists('branches_id', db_prefix() . 'invoices')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . "invoices`
    ADD COLUMN `branches_id` int(11) NULL DEFAULT '0';");
}
if (!$CI->db->field_exists('branches_id', db_prefix() . 'estimates')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . "estimates`
    ADD COLUMN `branches_id` int(11) NULL DEFAULT '0';");
}
if (!$CI->db->field_exists('branches_id', db_prefix() . 'proposals')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . "proposals`
    ADD COLUMN `branches_id` int(11) NULL DEFAULT '0';");
}

if (!$CI->db->field_exists('branches_id', db_prefix() . 'leads')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . "leads`
    ADD COLUMN `branches_id` int(11) NULL DEFAULT '0';");
}
if (!$CI->db->field_exists('branches_id', db_prefix() . 'clients')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . "clients`
    ADD COLUMN `branches_id` int(11) NULL DEFAULT '0';");
}
