@ WRITTEN IN PORTUGUESE BRAZIL

## PhpEntityDbContext

## Install
- Recomendado o uso do banco de dados MariaDB
- É necessário apenas incluir o arquivo autoload.php
> O exemplo a baixo mostra como inicializar o PhpEntityDbContext

```php
<?php
require_once './PhpEntityDbContext/autoload.php';
$phpEntityDbContext = PhpEntityDbContext\PhpEntityDbContextSetting::InitialSetting("./Models", $_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PASSWORD"]);
```

## Models
> Aqui é um exemplo básico de um modelo que podemos mapear.
Uma forma de descriminar o tamanho de um varchar é com o comentário ao lado da propriedade, dessa forma `public string $Email;//[64]` interpretado como Email `varchar( 64 )`, mas, se uma string não tiver um tamanho definido ele sera interpretada como`varchar( 255 )`

```php
<?php

class Modelo
{
    public int $ModeloId;
    public string $Email;//[64]
    public bool $Status;
    public float $Price;

}
```
## construct EntityDbContext
Por padrão é passado o diretório padrão dos modelos no construtor de inicialização no
`InitialSetting();`.Além disso, é possível passar outro diretório para mapear outros Modelos, como no exemplo à baixo:
`function __construct(object $EntityContext = null, string $ModelDirectory = null)`
```php
$entity = new EntityDbContext(null,"./ModelsAlternative");
```

## Migrations
- `$entity->AddDataMigrations();` se apenas for chamado o método AddDataMigrations sem o parâmetro `true`, ele irá criar  seus scripts SQLno diretório Migrations na raiz do seu projeto.
- `$entity->AddDataMigrations(true);` Mas, se for passado um `true` como parâmetro além de gerar os scripts SQL e salvar no diretório, ele também irá auto executar usando à conexão passada como padrão e assim gerar todas as tabelas no banco de dados.
- Gerar Migrations
```php
$entity = new EntityDbContext();
$entity->AddDataMigrations(true);
//$entity->AddDataMigrations();
```
- Deletar todas a tabelas do banco
```php
$entity = new EntityDbContext();
$entity->DropRemoveDatabaseMigrations();
```

## Methods

>`$entity->Add();`
- É Necessário sempre incluir o arquivo do modelo;Exemplo: `require_once 'Models/Home.php';`
- Por parâmetro é esperado um array, e interpretado por posições, assim a primeira posição do array sendo direcionada para o primeiro campo e assim seguindo fluxo.
- Porém, é possível não adicionar um campo, apenas passando como null, assim como no exemplo a baixo
- Até mesmo inserir apenas um registro como no exemplo seguinte.
```php
require_once 'Models/Modelo.php';

$entity = new EntityDbContext(new Modelo);

$data = ["Goku", null, "sayajin", 5.000];
$entity->Add($data);

$entity->Add(array("Darth Vader"));
```

>`$entity->Update();`
- Update($ObjectId, $Property, $PropertyValue);
O primeiro parâmetro sempre é passado o`id`, o segundo é o campo que queremos modificar e o terceiro,o valor que queremos passar
- pode ser passado apenas uma `string` com o nome do campo, ou um `array` de campos e um `array` de valores , passado na ordem
```php
require_once 'Models/Modelo.php';

$entity = new EntityDbContext(new Modelo);

$campos = ["Nome", "Cpf"];
$valores = ["Gohan", "1234567890"];
$entity->Update(4, $campos, $valores);

$entity->Update(5, 'Nome', 'Vegeta');
```

>`$entity->Remove();`
- É passado o `id` do registro que queremos deletar
```php
require_once 'Models/Modelo.php';

$entity = new EntityDbContext(new Modelo);

$entity->Remove(3);
```
>`$entity->FirstOrDefault();`
- Retorna um objeto passado o `id`
```php
require_once 'Models/Modelo.php';

$entity = new EntityDbContext(new Modelo);

$entity->FirstOrDefault(1);
```

>`$entity->ToList();`
- Retorna um `array` de objetos
```php
require_once 'Models/Modelo.php';

$entity = new EntityDbContext(new Modelo);

$entity->ToList();
```

>`$entity->GetQuery();`
- É possível fazer Query personalizada, `GetQuery();` Retorna `array`de dados
```php
require_once 'Models/Modelo.php';

$entity = new EntityDbContext(new Modelo);

$entity->GetQuery("SELECT *FROM modelo;")
```

>`$entity->SetQuery();`
Método de query sem retorno
```php
require_once 'Models/Modelo.php';

$entity = new EntityDbContext(new Modelo);

$entity->SetQuery("UPDATE modelo SET Nome = 'Bills', cpf = '123' WHERE ModeloId = 2;");
```