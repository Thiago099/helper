<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
    <?php
     include 'bin/sql.php';
     include 'bin/misc.php';
     ?>
    <link rel="stylesheet" href="bin/style.css">
  </head>
  <body>
<div class="container">

    <form class="" action="" method="get">
      <select class="" name="database">
        <?php
          $db=new sql();
          $r=$db->query('SHOW DATABASES');
          foreach ($r as $i):
          $ii=$i['Database'];
          ?>
          <option value="<?php echo  $ii?>" <?php if(isset($_GET['database']) && $_GET['database'] == $ii) echo "selected"?>><?php echo $ii ?></option>
        <?php endforeach; ?>
      </select>
      <?php
      if(isset($_GET['database'])):
        $db=new sql($_GET['database']);
        $r=$db->query("SHOW TABLES");
        ?>
        <select class="" name="table">
        <?php
        foreach ($r as $i):
          $ii=$i["Tables_in_$_GET[database]"];
          ?>
          <option value="<?php echo  $ii?>" <?php if(isset($_GET['table']) && $_GET['table'] == $ii) echo "selected"?>><?php echo $ii ?></option>
        <?php endforeach;?>
        </select>
      <?php endif; ?>
      <input type="submit" name="action" value="Submit">
      </form>
    <textarea name="name" rows="40" cols="200" spellcheck="false"><?php
      if(isset($_GET['database'])&&isset($_GET['table']))
      {
        $db=new sql($_GET['database']);
        $result=$db->query("DESC $_GET[table]");
        echo "\$dados_insert['$_GET[table]'] = [\n";
        foreach ($result as $i)
        {
          $ii=$i['Field'];
                if($ii=='id')         continue;
           else if($ii=='created_by') continue;
           else if($ii=='created_at') continue;
           else if($ii=='updated_by') continue;
           else if($ii=='updated_at') continue;
          echo ident("   '$ii' ",70)."=> \$dados->$ii,\n";
        }
        echo '];';
        echo "

\$header = (object) \$this->input->request_headers();
\$user = (object) \$this->jwt->decode(isset(\$header->authorization) ? \$header->authorization : \$header->Authorization, CONSUMER_KEY);
\$dados_insert['id_usuario']=\$user->id_usuario;

if ((int)\$dados->id == 0)
{
  \$dados_insert['$_GET[table]']['created_by'] = \$user->id_usuario;
  \$dados_insert['$_GET[table]']['created_at'] = date('Y-m-d H:i:s', time());
  \$result = \$this->$_GET[table]->salvar(\$dados_insert);
}
else
{
  \$dados_insert['$_GET[table]']['updated_by'] = \$user->id_usuario;
  \$dados_insert['$_GET[table]']['updated_at'] = date('Y-m-d H:i:s', time());
  \$result = \$this->$_GET[table]->atualizar(\$dados_insert, \$dados->id);
}

if (\$result)
{
   \$response['lista']  = \$this->$_GET[table]->get(\$result);
}
else
{
   \$response = [
      'status' => 'erro',
      'lista'  => [],
   ];
}
";
      }
      ?>
    </textarea>
    <textarea name="name" rows="40" cols="200" spellcheck="false"><?php
      if(isset($_GET['database'])&&isset($_GET['table']))
      {
        $database=$_GET['database'];
        $table=$_GET['table'];

        $db=new sql($database);
        $result=$db->query("DESC $_GET[table]");
        $ret="{\n";

          $info=new sql("information_schema");
          $db=new sql($database);
          $fks=$info->query("SELECT K.COLUMN_NAME coluna,k.REFERENCED_TABLE_NAME tabela, k.REFERENCED_COLUMN_NAME chave
          FROM information_schema.TABLE_CONSTRAINTS i
          LEFT JOIN information_schema.KEY_COLUMN_USAGE k ON i.CONSTRAINT_NAME = k.CONSTRAINT_NAME
          WHERE i.CONSTRAINT_TYPE = 'FOREIGN KEY'
          AND i.TABLE_SCHEMA = '$database'
          AND i.TABLE_NAME = '$table'
          GROUP BY coluna;");
        foreach ($result as $i)
        {
          $ii=$i['Field'];
          $ij=$i['Type'];
               if($ii=='created_by') continue;
          else if($ii=='created_at') continue;
          else if($ii=='updated_by') continue;
          else if($ii=='updated_at') continue;
          $str="SQL-$ij";
               if(!(strpos($str, 'varchar')     === false)) $str='""';
          else if(!(strpos($str, 'tinyint(1)')  === false)) $str='false';
          else if(!(strpos($str, 'text')        === false)) $str='""';
          else if(!(strpos($str, 'int')         === false)) $str='0';
          else if(!(strpos($str, 'float')       === false)) $str='0.0';
          else if(!(strpos($str, 'decimal')     === false)) $str='0.0';
          else if(!(strpos($str, 'double')      === false)) $str='0.0';
          else if(!(strpos($str, 'datetime')    === false)) $str='"2020-11-24 00:00:00.000"';
          else if(!(strpos($str, 'date')        === false)) $str='"2020-11-24"';
          foreach ($fks as $j) {
            if($j['coluna']==$ii) $str='null';
          }
          $ret.=ident("   \"$ii\" ",70).": $str,\n";
        }
        $ret=substr($ret, 0, -2);
        $ret.= "\n}";
        echo $ret;
      }
      ?>
    </textarea>
    <textarea name="name" rows="40" cols="200" spellcheck="false"><?php
    if(isset($_GET['database'])&&isset($_GET['table']))
    {
      function loop($database,$table,&$join,&$select)
      {
        $info=new sql("information_schema");
        $db=new sql($database);
        $fks=$info->query("SELECT K.COLUMN_NAME coluna,k.REFERENCED_TABLE_NAME tabela, k.REFERENCED_COLUMN_NAME chave
        FROM information_schema.TABLE_CONSTRAINTS i
        LEFT JOIN information_schema.KEY_COLUMN_USAGE k ON i.CONSTRAINT_NAME = k.CONSTRAINT_NAME
        WHERE i.CONSTRAINT_TYPE = 'FOREIGN KEY'
        AND i.TABLE_SCHEMA = '$database'
        AND i.TABLE_NAME = '$table'
        GROUP BY coluna;");
        $table=$_GET['table'];
        $fields="";
        foreach ($fks as $i) {
            $ct=$i['tabela'];
            $fields=$db->query("DESC $ct");
            $fk_name=str_replace("id_","",$i['coluna']);
            foreach ($fields as $j) {
              $jj=$j['Field'];
              if($i['chave']==$jj)continue;
              $select.=ident("$fk_name.$jj",70)." AS {$jj}_$fk_name,\n";
            }

            $join.="LEFT JOIN $ct";
            if($ct != $fk_name)$join.=" AS $fk_name";
            $join.=" ON $table.$i[coluna] = $fk_name.$i[chave]\n";
        }

        // foreach ($fks as $i) {
        //     loop($database,$i['tabela'],$join,$select);
        // }

      }
      $join='';
      $select=",\n";
      loop($_GET['database'],$_GET['table'],$join,$select);
      if($select==",\n")$select='';
      $select=substr($select, 0, -2)."\n";
      echo "SELECT\n$_GET[table].*{$select}FROM $_GET[table]\n$join";
    }
    ?></textarea>
    <textarea name="name" rows="40" cols="200" spellcheck="false"><?php
      if(isset($_GET['database'])&&isset($_GET['table']))
      {
        $database = $_GET['database'];
        $table = $_GET['table'];
        echo "ALTER TABLE `$database`.`$table`
ADD COLUMN `created_by` INT NULL,
ADD COLUMN `created_at` DATETIME NULL,
ADD COLUMN `updated_by` INT NULL,
ADD COLUMN `updated_at` DATETIME NULL,
ADD COLUMN `excluido` TINYINT(1) NULL DEFAULT NULL,
ADD CONSTRAINT `FK_{$table}_created_by` FOREIGN KEY (`created_by`) REFERENCES `usuario` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
ADD CONSTRAINT `FK_{$table}_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `usuario` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION;";
      }
      ?></textarea>
    <textarea name="name" rows="40" cols="200" spellcheck="false"><?php
      if(isset($_GET['database'])&&isset($_GET['table']))
      {
        $database=$_GET['database'];
        $table=$_GET['table'];

        $db=new sql($database);
        $result=$db->query("DESC $_GET[table]");
        $ret="{\n";

          $info=new sql("information_schema");
          $db=new sql($database);
          $fks=$info->query("SELECT K.COLUMN_NAME coluna,k.REFERENCED_TABLE_NAME tabela, k.REFERENCED_COLUMN_NAME chave
          FROM information_schema.TABLE_CONSTRAINTS i
          LEFT JOIN information_schema.KEY_COLUMN_USAGE k ON i.CONSTRAINT_NAME = k.CONSTRAINT_NAME
          WHERE i.CONSTRAINT_TYPE = 'FOREIGN KEY'
          AND i.TABLE_SCHEMA = '$database'
          AND i.TABLE_NAME = '$table'
          GROUP BY coluna;");

        $ret="INSERT INTO $_GET[table]\n(\n";
        foreach ($result as $i) {
          $ii=$i['Field'];
          $ret.="   $ii,\n";
        }
        $ret=substr($ret, 0, -2);
        $ret.="\n)\nVALUES\n(\n";
        foreach ($result as $i)
        {
          $ii=$i['Field'];
          $ij=$i['Type'];
          $str="SQL-$ij";
               if(!(strpos($str, 'varchar')     === false)) $str='""';
          else if(!(strpos($str, 'tinyint(1)')  === false)) $str='false';
          else if(!(strpos($str, 'text')        === false)) $str='""';
          else if(!(strpos($str, 'int')         === false)) $str='0';
          else if(!(strpos($str, 'float')       === false)) $str='0.0';
          else if(!(strpos($str, 'decimal')     === false)) $str='0.0';
          else if(!(strpos($str, 'double')      === false)) $str='0.0';
          else if(!(strpos($str, 'datetime')    === false)) $str='"2020-11-24 00:00:00.000"';
          else if(!(strpos($str, 'date')        === false)) $str='"2020-11-24"';
          foreach ($fks as $j) {
            if($j['coluna']==$ii) $str='null';
          }
          $ret.="   $str,\n";
        }
        $ret=substr($ret, 0, -2);
        $ret.= "\n)\n";
        echo $ret;
      }
      ?></textarea>
  </body>
  </div>
</html>
