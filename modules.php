<?php
$modules = array();


$modules["Forums"] = array( "description"=> "Forums","link"=> "forum.class.php","enabled"=> "1","nav"=>"1","admin"=>"0","perms"=>"forums.post","class"=>"forums","stats"=>"true","href"=>"/forums","sidebar"=>"/posttopic","sidebarDesc"=>"Post Topic","acp"=>""); 


$modules["News"] = array( "description"=> "News","link"=> "news.class.php","enabled"=> "1","nav"=>"1","admin"=>"0","perms"=>"news.write","class"=>"news","stats"=>"false","href"=>"/news","sidebar"=>"/postNews","sidebarDesc"=>"Post News","acp"=>""); 
?>