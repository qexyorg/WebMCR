<?php
$db = array (
  'host' => '127.0.0.1',
  'user' => 'root',
  'pass' => '',
  'base' => '',
  'port' => 3306,
  'backend' => 'mysqli',
  'log' => true,
  'tables' => 
  array (
    'ugroups' => 
    array (
      'name' => 'mcr_groups',
      'fields' => 
      array (
        'id' => 'id',
        'title' => 'title',
        'text' => 'description',
        'color' => 'color',
        'perm' => 'permissions',
      ),
    ),
    'iconomy' => 
    array (
      'name' => 'mcr_iconomy',
      'fields' => 
      array (
        'id' => 'id',
        'login' => 'login',
        'money' => 'money',
        'rm' => 'realmoney',
        'bank' => 'bank',
      ),
    ),
    'logs' => 
    array (
      'name' => 'mcr_logs',
      'fields' => 
      array (
        'id' => 'id',
        'uid' => 'uid',
        'msg' => 'message',
        'date' => 'date',
      ),
    ),
    'users' => 
    array (
      'name' => 'mcr_users',
      'fields' => 
      array (
        'id' => 'id',
        'group' => 'gid',
        'login' => 'login',
        'email' => 'email',
        'pass' => 'password',
        'uuid' => 'uuid',
        'salt' => 'salt',
        'tmp' => 'tmp',
        'is_skin' => 'is_skin',
        'is_cloak' => 'is_cloak',
        'ip_create' => 'ip_create',
        'ip_last' => 'ip_last',
        'color' => 'color',
        'date_reg' => 'time_create',
        'date_last' => 'time_last',
        'fname' => 'firstname',
        'lname' => 'lastname',
        'gender' => 'gender',
        'bday' => 'birthday',
        'ban_server' => 'ban_server',
      ),
    ),
  ),
);
?>