<?php 
$module = 'klear';
$paths = array();
$baseDir = dirname(dirname(dirname(__DIR__)));

if (isset($argv[1]) 
        && isset($argv[2]) 
        && $argv[1] == '--module') {
    
    $module = $argv[2];
}

if (isset($argv[3])
        && isset($argv[4])
        && $argv[3] == '--scriptsPaths') {
    for ($i = 4; ;$i++) {
        if (isset($argv[$i])) {
            $paths[] = $argv[$i];
        } else {
            break;
        }
    }
}
echo "Javascript $.translate parser\n\n";
echo "Selected module: " . $module . "\n";
$moduleDir = $baseDir . DIRECTORY_SEPARATOR . $module;
if (!is_dir($moduleDir)) {
    echo "Module " . $module . " not found.\n";
    exit;
}
$strings = array();
foreach ($paths as $path) {
    $dir = getcwd() . DIRECTORY_SEPARATOR . $path;
    if (!is_dir($dir)) {
        echo "Path " . $dir . " not found.\n";
        exit;
    } 
    if ($dirHandle = opendir($dir)) {
        while (false !== ($entry = readdir($dirHandle))) {
            $entryInfo = pathinfo($entry);
            if (isset($entryInfo['extension']) && $entryInfo['extension'] == 'js') {
                //TODO: Hay que mejorar la expresión pero ya.
                $contents = file_get_contents($dir . $entry);
                
                preg_match_all('/\$\.translate\([\"|\'](.*)[\"|\'][,|\)]/i', $contents, $result);
                
                $count = count($result[1]);
                if ($count<=0) {
                    continue;
                }
                echo $entry . ": ";
                echo $count . " strings found.\n";
                $strings = array_merge($strings, $result[1]);
            }
        }
        closedir($dirHandle);
    }   
}
$strings = array_unique($strings);
   
$translationFilePath = implode(
        DIRECTORY_SEPARATOR,
        array(
                $moduleDir,
                'languages',
                'js-translations.php'
        )
);

$fileContents = "<?php\n\n";
$fileContents .= "return " . var_export($strings, true) . ";\n";
file_put_contents($translationFilePath, $fileContents);
echo count($strings) . " unique strings found.\n";
echo $translationFilePath . " ... Saved!\n";
exit;
?>