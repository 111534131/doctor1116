<?php
header('Content-Type: text/plain; charset=utf-8');

function list_directory_recursively($dir, $prefix = '') {
    // Prevent access to parent directories
    if (strpos($dir, '..') !== false) {
        return;
    }

    $files = scandir($dir);
    if ($files === false) {
        echo $prefix . "[Error reading directory: " . htmlspecialchars($dir) . "]\n";
        return;
    }
    
    // Use array_diff to remove '.' and '..'
    $files = array_diff($files, array('.', '..'));

    $file_count = count($files);
    $i = 0;

    foreach ($files as $file) {
        $is_last = (++$i == $file_count);
        $connector = $is_last ? '└── ' : '├── ';

        echo $prefix . $connector . htmlspecialchars($file) . "\n";

        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            $new_prefix = $prefix . ($is_last ? '    ' : '│   ');
            list_directory_recursively($path, $new_prefix);
        }
    }
}

$start_dir = '.'; // Start from the current directory
echo "Directory listing from: " . realpath($start_dir) . "\n\n";
list_directory_recursively($start_dir);

?>