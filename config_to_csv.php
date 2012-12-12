<?php

/* Init auto-loader
--------------------------------------*/
define('CLASSPATH', __DIR__ . DIRECTORY_SEPARATOR . 'classes');

function __autoload($class){

    if (strpos($class, 'CI_') !== 0){
        $file = CLASSPATH . DIRECTORY_SEPARATOR . strtolower($class) . ".php";
        include(str_replace( "\\", DIRECTORY_SEPARATOR, $file));
    }

}

/* Check command line vs. form upload
--------------------------------------*/
if (isset($_SERVER['HTTP_HOST']) === false) {
    define('CLI', true);
} else {
    define('CLI', false);
}

if(CLI === false && isset($_FILES['xmlfile']) ===  false):
    include('includes/header.php');
    include('includes/upload-xml-config.php');
    include('includes/footer.php');
endif;


if(CLI === true || isset($_FILES['xmlfile']) ===  true){

    /* Parse XML
    --------------------------------*/
    $createAttributes = array();
    $restoreAttributes = array();

    if(CLI === true){
        $inputFile = isset($argv[1]) ? $argv[1] : 'Config.xml';
        $xml = simplexml_load_file($inputFile);

    } else {
        $upload = (object) $_FILES['xmlfile'];
        $xml = $upload->error ? NULL : simplexml_load_file($upload->tmp_name);

        if(is_null($xml)){
            echo "There was an issue with the file you uploaded. Please try again.";
            exit;
        }

    }



    $valueType = 'Value Type';
    foreach($xml->Call as $call){
        $operation = (String) $call->attributes()->Operation;

        switch($operation){

            case "CreateCustomAttribute":
                $createAttributes[] = Csi_xml::getParameterObject($call);
                break;

            case "RestoreCustomAttribute":
                $restoreAttributes[] = Csi_xml::getParameterObject($call);
                break;

            case "RestoreEnum":
                $item = Csi_xml::getParameterObject($call);

                $resultItem = array();

                $p = new StdClass();
                $p->value = $item['Name']->value;
                $resultItem['Id'] = $p;

                $p = new StdClass();
                $p->value = 'enum';
                $resultItem[$valueType] = $p;

                $restoreAttributes[] = $resultItem;
                break;

            default:
                break;

        }

    }

    /* Extract attribute information for csv output
    --------------------------------*/
    $createInfo = array();
    $columns = array('Enabled', 'URL', 'Id', 'Value Type', 'Format', 'Initial Value',  'Expression');

    foreach($restoreAttributes as $attribute){
        $result = array();

        foreach($columns as $column){

            if(isset($attribute[$column])){
                $result[] = $attribute[$column]->value;

            } else {
                $result[] = "";

            }

        }

        $createInfo[] = $result;

    }

    /* Output csv file
    --------------------------------*/
    if(CLI === true){
        Csi_csv::saveCsv($columns, $createInfo, "attributes.csv");
        echo 'attributes.csv was updated';

    } else {
        Csi_csv::outputCsv($columns, $createInfo, "attributes.csv");

    }

}