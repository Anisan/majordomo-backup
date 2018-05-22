<?php
interface IProvider
{
    public function getFreeSpace();
    public function getList();
    public function uploadBackup($file, $backup);
    public function deleteBackup($backup);
    public function downloadBackup($backup, $file);
}
?>