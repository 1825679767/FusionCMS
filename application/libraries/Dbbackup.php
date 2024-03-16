<?php

use App\Config\Database;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * @package FusionCMS
 * @author  Jesper Lindström
 * @author  Xavier Geerinck
 * @author  Elliott Robbins
 * @author  Err0r
 * @author  Keramat Jokar (Nightprince) <https://github.com/Nightprince>
 * @author  Ehsan Zare (Darksider) <darksider.legend@gmail.com>
 * @link    https://github.com/FusionWowCMS/FusionCMS
 */

class Dbbackup
{
    private Controller $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper(['file', 'text', 'form', 'string']);
        $this->CI->load->model('cms_model');

        $this->CI->load->config('backups');

        if ($this->CI->config->item('auto_backups'))
        {
            $this->backup();
        }
    }

    /**
     * @param bool $trigger
     * @return void
     */
    public function backup(bool $trigger = false): void
    {
        $db_backup_path = 'writable/backups/';
        $max_files = $this->CI->config->item('backups_max_keep');
        $backups_interval = $this->CI->config->item('backups_interval');
        $backups_time = $this->CI->config->item('backups_time');

        $date_ref = date("Y-m-d H:i:s", strtotime('-' . $backups_interval . $backups_time));
        $row = $this->CI->db->table('backup')->where('created_date >', $date_ref)->orderBy('created_date', 'DESC')->limit(1)->get()->getRow();

        if (!$row || $trigger) {
            if (!is_dir($db_backup_path) && $trigger) {
                mkdir($db_backup_path);
                log_message('info', $db_backup_path . ' did not exist. Created!');
            }

            if (!is_writeable($db_backup_path) && $trigger) {
                log_message('error', $db_backup_path . ' not writeable!');
                die("Backup folder not writeable");
            }

            $date = date("Y-m-d H:i:s");
            $file_name = date("Y_m_d_H_i_s");

            $prefs = [
                'filename'           => $file_name,
                'format'             => 'zip', // gzip, zip, txt
                'add_drop'           => true,
                'add_insert'         => true,
                'newline'            => "\n",
                'foreign_key_checks' => true,
            ];

            //Backup your entire database
            $backup = Database::utils()->backup($prefs);
            $file = $db_backup_path . $file_name . '.zip';

            if (write_file($file, $backup)) {
                $data = [
                    'backup_name' => $file_name,
                    'created_date' => $date
                ];

                $this->CI->db->table('backup')->insert($data);

                $n_row = $this->CI->db->table('backup')->countAll();

                if ($n_row > $max_files) {
                    $result = $this->CI->db->table('backup')->orderBy('created_date', 'ASC')->limit($n_row - $max_files)->get()->getResult();

                    foreach ($result as $to_delete) {
                        //delete row from db table
                        $this->CI->db->table('backup')->where('id', $to_delete->id)->delete();

                        // delete file from backup directory
                        $file_del = $db_backup_path . $to_delete->backup_name . '.zip';
                        if (file_exists($file_del)) {
                            unlink($file_del);
                            log_message('info', 'Backup ' . $file_del . ' deleted');
                        }
                    }
                }
                if ($trigger) {
                    die('yes');
                }
            } else {
                log_message('error', 'Backup creation failed. Unknown error');
                if ($trigger) {
                    die('Backup failed');
                }
            }
        }
    }
}
