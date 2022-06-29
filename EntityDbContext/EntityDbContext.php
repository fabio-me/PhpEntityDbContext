<?php

class EntityDbContext
{
    private object $EntityContext;
    private string $NameEntityContext;
    private string $EntityContextNameId;
    private $DatabaseConnection;
    public string $ModelDirectory;

    function __construct(object $EntityContext = null, string $ModelDirectory = null)
    {
        if($ModelDirectory == null)
        {
            $this->ModelDirectory = $GLOBALS["MODELS_DEFAULT_DIRECTORY"];
        }
        else
        {
            $this->ModelDirectory = $ModelDirectory;
        }

        if($EntityContext != null)
        {
            $this->EntityContext = $EntityContext;
            $this->NameEntityContext = get_class($EntityContext);
            $this->EntityContextNameId = $this->GetEntityContextNameId();
        }
        $this->DatabaseConnection = $this->GetDatabaseConnection();
    }

    private function TestIfIsObjectId($ObjectId) : bool // if($this->TestIfIsObjectId($condition) == false)
    {
        if( $ObjectId == $this->NameEntityContext."Id" ||
            $ObjectId == $this->NameEntityContext."iD" ||
            $ObjectId == $this->NameEntityContext."id" ||
            $ObjectId == $this->NameEntityContext."ID" ||
            $ObjectId == "Id".$this->NameEntityContext ||
            $ObjectId == "iD".$this->NameEntityContext ||
            $ObjectId == "id".$this->NameEntityContext ||
            $ObjectId == "ID".$this->NameEntityContext ||
            $ObjectId == "Id" ||
            $ObjectId == "iD" ||
            $ObjectId == "id" ||
            $ObjectId == "ID"
        )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    private function GetEntityContextNameId() : string
    {
        foreach($this->GetObjectProperties() as $v)
        {
            if($this->TestIfIsObjectId($v["property"]) == true)
            {
                return $v["property"];
            }
        }
    }

    private function GetObjectProperties() : array
    {
        $obj = file($this->ModelDirectory."/".$this->NameEntityContext.".php");
        foreach ($obj as $v)
        {
            if(strpos($v, "public") || strpos($v, "private") || strpos($v, "protected"))
            {
                if(strpos($v, ";"))
                {
                    $c = explode(";", $v);
                    $c = explode("$", $c[0]);
                    if(strpos($c[0], "int"))
                    {
                        $c[0] = "int";
                    }
                    if(strpos($c[0], "string"))
                    {
                        $c[0] = "string";
                    }
                    if(strpos($c[0], "float"))
                    {
                        $c[0] = "float";
                    }
                    if(strpos($c[0], "bool"))
                    {
                        $c[0] = "bool";
                    }
                    $r[] = ["type" => $c[0], "property" => $c[1]];
                }
            }
        }
        return $r;
    }

    /**
     * @ Methods Database
    */

    private function GetDatabaseConnection()
    {
        try
        {
            $this->DatabaseConnection = new PDO('mysql:host='.$GLOBALS["DB_HOST"].';dbname='.$GLOBALS["DB_NAME"], $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWORD"]);
            return $this->DatabaseConnection;
        }
        catch(PDOException $Error)
        {
            echo 'error authenticating with the database : ' . $Error->getMessage();
            //return json_encode('error authenticating with the database : ' . $Error->getMessage());
        }
        
    }

    private function GetDatabaseQuery($query)
    {
        $sql = $this->DatabaseConnection->prepare($query);
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_CLASS);
    }

    private function GetDatabaseQueryFetchObject($query)
    {
        $sql = $this->DatabaseConnection->prepare($query);
        $sql->execute();
        return $sql->fetchObject();
    }

    private function SetDatabaseQuery($query)
    {
        $sql = $this->DatabaseConnection->prepare($query);
        $sql->execute();
    }


    /**
     * @ Methods Migrations
    */

    //SQL get properties from object defined to SQL();
    private function ListPropertiesForSQLMigrations(string $Model) : array
    {
        $obj = file($this->ModelDirectory."/".$Model);
        foreach ($obj as $v)
        {
            if(strpos($v, "public") || strpos($v, "private") || strpos($v, "protected"))
            {
                if(strpos($v, ";"))
                {
                    $c = explode(";", $v);
                    $s = null;
                    if(strpos($c[1], "["))
                    {
                        $s = explode("[", $c[1]);
                        $s = explode("]", $s[1]);
                    }
                    $c = explode("$", $c[0]);
                    if(strpos($c[0], "int"))
                    {
                        $c[0] = "int";
                    }
                    if(strpos($c[0], "string"))
                    {
                        $c[0] = "varchar";
                    }
                    if(strpos($c[0], "float"))
                    {
                        $c[0] = "decimal";
                    }
                    if(strpos($c[0], "bool"))
                    {
                        $c[0] = "bool";
                    }

                    if($c[0] == "varchar")
                    {
                        if(isset($s[0]))
                        {
                            $type = $c[0]."(".$s[0].")";
                        }
                        else
                        {
                            $type = $c[0]."(255)";
                        }
                    }
                    else
                    {
                        if($c[0] == "decimal")
                        {
                            $type = "decimal(19,4)";
                        }
                        else
                        {
                            $type = $c[0];
                        }
                    }
                    $r[] = ["property" => $c[1], "type" => $type];
                }
            }
        }
        return $r;
    }

    private function ScanModelsFieldsToSqlMigrations() : array
    {
        $Models = scandir($this->ModelDirectory);

        foreach($Models as $Model)
        {
            $NameEntity = explode(".php", $Model);
            $TableName = strtolower($NameEntity[0]);

            if($Model != "." && $Model != "..")
            {
                $Fields = null;
                $PrimaryKey = null;
                foreach($this->ListPropertiesForSQLMigrations($Model) as $value)
                {
                    $this->NameEntityContext = $NameEntity[0];
                    if($this->TestIfIsObjectId($value["property"]) == true)
                    {
                        
                        $PrimaryKey = $value["property"];
                        $Fields = $Fields.$value["property"]." ".$value["type"]." NOT NULL AUTO_INCREMENT, ";
                    }
                    else
                    {
                        $Fields = $Fields.$value["property"]." ".$value["type"].", ";
                    }
                }
                $r[] = "CREATE TABLE IF NOT EXISTS ".$TableName."(".$Fields."PRIMARY KEY (".$PrimaryKey.") );";
            }
        }
        foreach($r as $v)
        {
            $c1 = explode(", PRIMARY KEY (", $v);
            $c2 = explode(") );", $c1[1]);
            if($c2[0] ==  null)
            {
                $q[] = $c1[0].$c2[1]." );";
            }
            else
            {
                $q[] = $v;
            }
        }
        return $q;
    }

    private function SaveDataMigrationsToFile($query)
    {
        if(!is_dir("./Migrations/"))
        {
            mkdir('./Migrations/', 0777, true);
        }

        $MigrationFileName = "Migration".date("Ymdhis");
        $file = fopen("./Migrations/".$MigrationFileName.".sql", 'a');
        fwrite($file, $query."\n\n");
        fclose($file);
    }

    public function AddDataMigrations($param = null) // true or null
    {
        foreach($this->ScanModelsFieldsToSqlMigrations() as $query)
        {
            $this->SaveDataMigrationsToFile($query);
            if($param == true)
            {
                $this->SetDatabaseQuery($query);
            }
        }
        echo "Migration executed successfully";
    }

    public function DropRemoveDatabaseMigrations()
    {
        $Models = scandir($this->ModelDirectory);
        foreach($Models as $Model)
        {
            $NameEntity = explode(".php", $Model);
            $TableName = strtolower($NameEntity[0]);
            if($Model != "." && $Model != "..")
            {
                $this->SetDatabaseQuery("DROP TABLE IF EXISTS ".$TableName.";");
            }
        }
        echo "Successfully removed";
    }

    /**
     * @ Methods publics
    */

    // Insert - array or in null field
    public function Add(array $PropertyValues) : void
    {
        $TableName = strtolower($this->NameEntityContext);
        $Object = $this->GetObjectProperties();

        // Fields
        $Fields = null;
        foreach($Object as $value)
        {
            if($this->TestIfIsObjectId($value["property"]) == false)
            {
                if($Fields != null)
                {
                    $Fields = $Fields.", ".$value["property"];
                }
                else
                {
                    $Fields = $value["property"];
                }
            }
        }

        // PropertyValues
        $ValuesResults = null;
        foreach($PropertyValues as $key => $value)
        {
            if($key == 0)
            {
                if($value == null)
                {
                    $ValuesResults = "null";
                }
                else
                {
                    $ValuesResults = "'".$value."'";
                }
            }
            else
            {
                if($Object[$key + 1]["type"] == "string")
                {
                    if($value == null)
                    {
                        $ValuesResults = $ValuesResults.", null";
                    }
                    else
                    {
                        $ValuesResults = $ValuesResults.", '".$value."'";
                    }
                }
                else
                {
                    $ValuesResults = $ValuesResults.", ".$value;
                }
            }
        }

        $query = "INSERT INTO $TableName
        ($Fields)
        VALUES
        ($ValuesResults);";

        $this->SetDatabaseQuery($query);
    }

    // Edit - array or string // refatorar  AQUI ! - função que retorna se o tipo é string se repete 3 vezes
    public function Update($ObjectId, $Property, $PropertyValue) : void
    {
        $TableName = strtolower($this->NameEntityContext);
        $Object = $this->GetObjectProperties();

        if(is_array($Property))
        {
            $Fields = null;
            foreach($Property as $key => $value)
            {
                if($key == count($Property) - 1)
                {
                    foreach($Object as $v)
                    {
                        if($v["property"] == $value)
                        {
                            if($v["type"] == "string")
                            {
                                $Fields = $Fields.$value." = '".$PropertyValue[$key]."'";
                            }
                            else
                            {
                                $Fields = $Fields.$value." = ".$PropertyValue[$key];
                            }
                        }
                    }
                }
                else
                {
                    foreach($Object as $v)
                    {
                        if($v["property"] == $value)
                        {
                            if($v["type"] == "string")
                            {
                                $Fields = $Fields.$value." = '".$PropertyValue[$key]."', ";
                            }
                            else
                            {
                                $Fields = $Fields.$value." = ".$PropertyValue[$key].", ";
                            }
                        }
                    }
                }
            }
            $query = "UPDATE $TableName SET $Fields WHERE $this->EntityContextNameId  = $ObjectId;";
        }
        else
        {
            foreach($Object as $v)
            {
                if($v["property"] == $Property)
                {
                    if($v["type"] == "string")
                    {
                        $query = "UPDATE $TableName SET $Property = '$PropertyValue' WHERE $this->EntityContextNameId = $ObjectId;";
                    }
                    else
                    {
                        $query = "UPDATE $TableName SET $Property = $PropertyValue WHERE $this->EntityContextNameId = $ObjectId;";
                    }
                }
            }
        }
        
        $this->SetDatabaseQuery($query);
    }

    // Delete
    public function Remove(int $ObjectId) : void
    {
        $TableName = strtolower($this->NameEntityContext);
        $query= "DELETE FROM $TableName  WHERE $this->EntityContextNameId = $ObjectId;";
        $this->SetDatabaseQuery($query);
    }

    // Get List
    public function ToList() : array
    {
        $TableName = strtolower($this->NameEntityContext);
        $query= "SELECT *FROM $TableName;";
        return $this->GetDatabaseQuery($query);
    }

    // Get Object by id
    public function FirstOrDefault(int $ObjectId) : object
    {
        $TableName = strtolower($this->NameEntityContext);
        $query= "SELECT *FROM $TableName  WHERE $this->EntityContextNameId = $ObjectId;";
        return $this->GetDatabaseQueryFetchObject($query);
    }

    // Querys
    public function GetQuery(string $query) : array
    {
        return $this->GetDatabaseQuery($query);
    }

    public function SetQuery(string $query) : void
    {
        $this->SetDatabaseQuery($query);
    }
}