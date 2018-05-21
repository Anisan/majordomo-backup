<?php
interface IProvider
{
    public function getFreeSpace();
    public function getList();
    public function addBackup($file, $backup);
    public function deleteBackup($backup);
    public function uploadBackup($backup, $file);
}
?>