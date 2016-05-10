<?php

$files = [
    'Arr.php',
    'Dict.php',
    'Set.php',
    'Tree.php',
];

function get_changes($content) {
    $changes = [];
    $key = '// API-CHANGE:';
    // "// API-CHANGE: new function"
    $lines = explode("\n", $content);
    foreach ($lines as $idx => $line) {
        if (strpos($line, $key) !== false) {
            $changes[] = [
                'line' => $idx,
                'change' => str_replace($key, '', $line)
            ];
        }
    }
    return $changes;
}

function print_changes($file, $changes) {
    echo "<h1>$file</h1>";
    echo '<ol>';
    foreach ($changes as $idx => $change_data) {
        echo '<li>'.$change_data['change'].' (line '.$change_data['line'].')</li>';
    }
    echo '</ol>';
}

foreach ($files as $idx => $file) {
    $content = file_get_contents($file);
    $changes = get_changes($content);
    print_changes($file, $changes);
}
