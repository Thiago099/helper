<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
    <?php include 'bin/sql.php';?>
  </head>
  <body>

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
      <input type="submit" name="" value="Submit">
      </form>

    <textarea name="name" rows="40" cols="200"><?php
      if(isset($_GET['database'])&&isset($_GET['table']))
      {
        $db=new sql($_GET['database']);
        $result=$db->query("DESC $_GET[table]");
        echo "\$dados_insert['$_GET[table]'] = [\n";
        foreach ($result as $i)
        {
          $ii=$i['Field'];
          if($ii=='id')continue;
          $str ="   '$ii' ";
          $count=70-strlen($str);
          for ($j=0; $j < $count; $j++) {
            $str.=' ';
          }
          echo "$str=> \$dados->$ii,\n";
        }
        echo '];';
      }
      ?>
    </textarea>
    <textarea name="name" rows="40" cols="200"><?php
      if(isset($_GET['database'])&&isset($_GET['table']))
      {
        $db=new sql($_GET['database']);
        $result=$db->query("DESC $_GET[table]");
        $ret="{";
        foreach ($result as $i)
        {
          $ii=$i['Field'];
          $ij=$i['Type'];
          $str="SQL-$ij";
               if(!(strpos($str, 'varchar')     === false)) $str='""';
          else if(!(strpos($str, 'tinyint(1)')  === false)) $str='false';
          else if(!(strpos($str, 'int')         === false)) $str='0';
          else if(!(strpos($str, 'float')       === false)) $str='0.0';
          else if(!(strpos($str, 'decimal')     === false)) $str='0.0';
          else if(!(strpos($str, 'double')      === false)) $str='0.0';
          else if(!(strpos($str, 'datetime')    === false)) $str='"2020-11-24 00:00:00.000"';
          else if(!(strpos($str, 'date')        === false)) $str='"2020-11-24"';
          $ret.="\"$ii\"";
        }
        $ret=substr($ret, 0, -1);
        $ret.= "}";
        echo $ret;
      }
      ?>
    </textarea>
  </body>
</html>
