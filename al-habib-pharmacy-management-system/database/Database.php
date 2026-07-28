<?php

interface Database
{
    public function connectToDb();
    public function insert($sql, $params = []);
    public function update($sql, $params = []);
    public function delete($sql, $params = []);
    public function select($sql, $params = []);
    public function disconnect();
}

?>