<?php

class Csi_csv {

    static function outputCsv($columns, $data, $fileName = "file.csv"){

        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=$fileName");
        header("Pragma: no-cache");
        header("Expires: 0");

        /* Output csv file
        --------------------------------*/
        $fp = fopen("php://output", "w");

        fputcsv($fp, $columns);
        foreach ($data as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);

    }

    static function saveCsv($columns, $data, $fileName = "file.csv"){

        $fp = fopen('', 'w');

        fputcsv($fp, $columns);
        foreach ($data as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);

    }

}