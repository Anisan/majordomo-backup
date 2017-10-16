<?php
interface IProvider
{
    public function getList();
    public function addBackup($file, $backup);
    public function deleteBackup($backup);
}
?>