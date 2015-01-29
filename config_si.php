<?php

$host = 'localhost';
$flav = '<FLAV>';
$vers = '<VERS>';
$demoData = <DEMO>;

$sugar_config_si = array (
    'setup_db_host_name' => 'localhost',
    'setup_db_database_name' => "sugar_$vers"."_$flav",
    'setup_db_drop_tables' => 1,
    'setup_db_create_database' => 1,
    'setup_site_admin_user_name' => 'admin',
    'setup_site_admin_password' => 'asdf',
    'setup_db_create_sugarsales_user' => 0,
    'setup_db_admin_user_name' => 'root',
    'setup_db_admin_password' => 'asdf',
    'setup_db_type' => 'mysql',
    'setup_license_key' => 'internal0653b21c5bd06e81c3d9dba9',
    'setup_site_url' => "http://localhost/$vers/$flav/sugarcrm/",
    'default_currency_iso4217' => 'EUR',
    'default_currency_name' => 'Euro',
    'default_currency_significant_digits' => '2',
    'default_currency_symbol' => '€',
    'default_date_format' => 'Y/m/d',
    'default_time_format' => 'H:i',
    'default_decimal_seperator' => ',',
    'default_export_charset' => 'ISO-8859-1',
    'default_language' => 'en_us',
    'default_locale_name_format' => 's f, l',
    'default_number_grouping_seperator' => '.',
    'export_delimiter' => ';',
    'demoData' => ($demoData ? 'multi' : 'no'),
    'developerMode' => true,
    'setup_fts_type' => 'Elastic',
    'setup_fts_host' => 'localhost',
    'setup_fts_port' => '9200',
);
