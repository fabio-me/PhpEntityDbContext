<?php

/**
* @Example of use / Exemplo de uso
*
* require './PhpEntityDbContext/autoload.php';
* $phpEntityDbContext = PhpEntityDbContext\PhpEntityDbContextSetting::InitialSetting("./Models", $_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PASSWORD"]);
*
* $entity = new EntityDbContext(new Model);
* $entity->ToList();
*/

namespace PhpEntityDbContext;

class PhpEntityDbContextSetting
{
    /**
     * @EntityDbContextInitialSetting
    */
    public static function InitialSetting(string $MODELS_DEFAULT_DIRECTORY, string $DB_HOST, string $DB_NAME, string $DB_USER, string $DB_PASSWORD)
    {
        $GLOBALS["MODELS_DEFAULT_DIRECTORY"] = $MODELS_DEFAULT_DIRECTORY;

        $GLOBALS["DB_HOST"] = $DB_HOST;
        $GLOBALS["DB_NAME"] = $DB_NAME;
        $GLOBALS["DB_USER"] = $DB_USER;
        $GLOBALS["DB_PASSWORD"] = $DB_PASSWORD;
    
        require_once __DIR__ . '/EntityDbContext.php';
    }
}