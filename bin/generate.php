<?php

if (!isset($argv[1])) {
    echo 'No input source was defined';
    exit(1);
}

$filename = $argv[1];

if ($filename[0] !== '/') {
    $filename = __DIR__ . '/source/' . pathinfo($filename, PATHINFO_FILENAME) . '.txt';
}

$target = isset($argv[2]) ? $argv[2] : pathinfo($filename, PATHINFO_FILENAME) . '.php';

if ($target[0] !== '/') {
    $target = __DIR__ . '/index/' . $target;
}

$codes = array();

$content = file_get_contents($filename);

$lines = explode("\n", $content);

foreach ($lines as $line)
{
    $line = trim($line);
    if ($line == '' || $line[0] == '#') {
        continue;
    }
    $line = explode(chr(0x0009), $line);
    $codes[$line[0]] = '#' . $line[1] . '#';
}

$text = "<?php\n return ";
$text .= var_export($codes, true);
$text = str_replace("'#", '', str_replace("#'", '', $text));
$text .= ';';

file_put_contents($target, $text);

echo 'File creted', PHP_EOL;
