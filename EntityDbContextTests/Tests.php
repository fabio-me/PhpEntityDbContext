<?php

echo '<body text="green" bgcolor="black">';

// configuration
require_once '../autoload.php';

$phpEntityDbContext = PhpEntityDbContext\PhpEntityDbContextSetting::InitialSetting(
"./Models",
'localhost',
'phpentitydbcontext',
'root',
'123');

require_once "./Models/Cliente.php";
$entity = new EntityDbContext(new Cliente);

//require_once "./ModelsAlternative/ClienteAlternative.php";
//$entity = new EntityDbContext(null,"./ModelsAlternative");

// MIGRATIONS
//$entity->AddDataMigrations(true);
//$entity->AddDataMigrations();
//$entity->DropRemoveDatabaseMigrations();

// ADD
//$data = ["Goku", null, "sayajin", null, "5511996112233", "2022-12-25"];
//$entity->Add($data);
//$entity->Add(array("Darth Vader"));

// UPDATE
//$campos = ["Nome", "Cpf"];
//$valores = ["Gohan", "1234567890"];
//$entity->Update(1, $campos, $valores);
//$entity->Update(1, 'Nome', 'Vegeta');

// REMOVE
//$entity->Remove(2);

// LIST
//var_dump($entity->ToList());

// GET BY ID
//var_dump($entity->FirstOrDefault(1));

// GUERYS
//var_dump($entity->GetQuery("SELECT *FROM cliente;"));
//$entity->SetQuery("UPDATE cliente SET Nome = 'xxxxxxx', cpf = '000' WHERE ClienteId = 5;");

//echo "<pre>";
//echo "</pre>";