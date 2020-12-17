<?php
class sql
{
  public $conn;
  function __construct($dbname="")
  {
    $this->conn = new mysqli("localhost", "root", "", $dbname);
    if ($this->conn->connect_error)
    {
        die("Connection failed: " . $this->conn->connect_error);
    }
  }
  function query($sql)
  {
    $ret=[];
    $result = $this->conn->query($sql);
    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            array_push($ret,$row);
        }
    }
    return $ret;
  }
  function close()
  {
    $conn->close();
  }
}
?>