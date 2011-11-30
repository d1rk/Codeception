<?php

require_once __DIR__.'/../autoload.php';

function clean_doc($doc, $indent = 3)
{
    $lines = explode("\n", $doc);
    $lines = array_map(function ($line) use ($indent){ return substr($line,$indent); }, $lines);
    $doc = implode("\n", $lines);
    $doc = str_replace('@',"\n> @", $doc);
    return $doc;
}

$modules = \Symfony\Component\Finder\Finder::create()->files('*.php')->in(__DIR__.'/../src/Codeception/Module');

foreach ($modules as $module) {

    $moduleName = basename(substr($module,0,-4));
    $text = '# '.$moduleName."\n";

    $className = '\Codeception\Module\\'.$moduleName;
    $class = new ReflectionClass($className);

    $doc = $class->getDocComment();
    if ($doc) $text .= clean_doc($doc, 3);

    foreach ($class->getMethods() as $method) {
        // if ($method->getDeclaringClass()->name != $className) continue;
        if ($method->isConstructor() or $method->isDestructor()) continue;
        if (strpos($method->name,'_') === 0) continue;
        if ($method->isPublic()) {
            $text .= '### '.$method->name."\n\n";
            $doc = $method->getDocComment();
            if (!$doc) {
                $doc = "__not documented__\n";
            } else {
                $doc = clean_doc($doc, 7);
            }
            $text .= $doc;
        }
    }
    file_put_contents(__DIR__.'/../docs/modules/'.$moduleName.'.md', $text);
}