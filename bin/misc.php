<?php
	function ident($str,$lenght)
	{
      $count=$lenght-strlen($str);
      for ($j=0; $j < $count; $j++) 
        $str.=' ';
      return $str;
    }
?>