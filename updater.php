<?
$moduleId = "hellodigital.turbosite";
$modulePath = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$moduleId;


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

    // Обновление wizard решения
    CopyDirFiles(
        dirname(__FILE__).'/wizards',
        $_SERVER["DOCUMENT_ROOT"].'/bitrix/wizards', 
        true, 
        true
    );

    // Обновление базы данных
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

    return true; // вернуть true, если обновление успешно
}
?>