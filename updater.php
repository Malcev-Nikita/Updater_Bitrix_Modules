<?
\Bitrix\Main\Loader::includeModule('iblock');

$moduleId = "hellodigital.turbosite";
$modulePath = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$moduleId;


function replaceMacrosRecursive($directory) {
    $files = glob(rtrim($directory, '/') . '/*');

    foreach ($files as $file) {
        if (is_dir($file) && basename($file) === 'bitrix') {
            if ($baseName === 'bitrix' || ($baseName === 'templates' && in_array(basename(dirname($filePath)), ['hellodigital.turbosite', 'hellodigital.turbosite.en']))) {
                continue;
            }
        }

        if (is_dir($file)) {
            replaceMacrosRecursive($file);
        } 
        else {
            replaceMacrosInFile($file);
        }
    }
}

function replaceMacrosInFile($file) {
    $content = file_get_contents($file);

    $res = CIBlock::GetList(Array(), Array(), true);
    while($ar_res = $res->Fetch()) {
        if(str_contains($content, strtoupper($ar_res['CODE']))) {
            $new_content = str_replace('#'.strtoupper($ar_res['CODE']).'#', $ar_res['ID'], $content);
			file_put_contents($file, $new_content);
            $content = $new_content;
        }
    }
}


// Функция обновления
if(IsModuleInstalled($moduleId)) {
    // Копирование шаблонов решения
    CopyDirFiles(
        dirname(__FILE__).'/wizards/'.explode('.', $moduleId)[0].'/'.explode('.', $moduleId)[1].'/site/templates', 
        $_SERVER["DOCUMENT_ROOT"]."/bitrix/templates", 
        true, 
        true,
        false,
        '.menu.'
    );

    // Копирование файлов и папок
    CopyDirFiles(
        dirname(__FILE__).'/wizards/'.explode('.', $moduleId)[0].'/'.explode('.', $moduleId)[1].'/site/public', 
        $_SERVER["DOCUMENT_ROOT"], 
        true, 
        true,
        false,
        '.menu.'
    );

	replaceMacrosRecursive($_SERVER['DOCUMENT_ROOT']);

    if($_SERVER['DOCUMENT_ROOT']."/_index.php") {
        unlink($_SERVER['DOCUMENT_ROOT']."/index.php");
        rename($_SERVER['DOCUMENT_ROOT']."/_index.php", $_SERVER['DOCUMENT_ROOT']."/index.php");
    }

    // Обновление wizard решения
    CopyDirFiles(
        dirname(__FILE__).'/wizards',
        $_SERVER["DOCUMENT_ROOT"].'/bitrix/wizards', 
        true, 
        true
    );

    // Обновление базы данных
    $IBLOCKS_IDS_OLD = CIBlock::GetList(Array(), Array('TYPE'=>'turbosite%'), true);
    $IBLOCK_TEMPLATE = new CIBlock;
    
    $dir = dirname(__FILE__).'/wizards/'.explode('.', $moduleId)[0].'/'.explode('.', $moduleId)[1].'/site/services/iblock';
    $files = scandir($_SERVER['DOCUMENT_ROOT'].$dir);
    foreach ($files as $file){
        if(preg_match('/types.(php)/', $file)) {
            include $_SERVER['DOCUMENT_ROOT'].$dir.$file;
        }
    }
    foreach ($files as $file){
        if(preg_match('/\.(php)/', $file) && !str_contains($file, 'types')) {
            include $_SERVER['DOCUMENT_ROOT'].$dir.$file;
        }
    }

    $IBLOCKS_IDS_NEW = CIBlock::GetList(Array(), Array('TYPE'=>'turbosite%'), true);

    while($IBLOCK_OLD = $IBLOCKS_IDS_OLD->Fetch()) {
        while($IBLOCK_NEW = $IBLOCKS_IDS_NEW->Fetch()) {
	        if($IBLOCK_OLD['NAME'] == $IBLOCK_NEW['NAME']) {                
                $IBLOCK_TEMPLATE->Update($IBLOCK_NEW['ID'], Array("ID" => $IBLOCK_OLD['ID']));
            }
        }
    }

    return true;
}
?>
