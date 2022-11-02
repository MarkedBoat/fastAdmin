<?php

$errors = [];
if (!function_exists('mkdir'))
{
    $errors[] = "需要开启 mkdir ";
}


if (!function_exists('chmod'))
{
    $errors[] = "需要开启 chmod ";
}


if (!function_exists('exec'))
{
    $errors[] = "需要开启 exec ";
}
if (!class_exists('PDO'))
{
    $errors[] = "需要开启 PDO ";
}

if (count($errors))
{
    echo join("\n", $errors);
    die("\n");
}
$root_dir  = __DIR__;
$init_dirs = [
    '/log/web',
    '/log/cmd',
    '/log/cli',
    '/log/file/image',
    '/static/upload'
];

foreach ($init_dirs as $init_dir)
{
    $dst_file = "{$root_dir}{$init_dir}";
    if (file_exists($dst_file))
    {
        chmod($dst_file, 0777);
        echo "\n {$dst_file} 文件已经存在，修改权限 \n";
    }
    else
    {
        mkdir($dst_file, 0777, true);
        chmod($dst_file, 0777);
        echo "\n {$dst_file} 创建文件 \n";
    }
}