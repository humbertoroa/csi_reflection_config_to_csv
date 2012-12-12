<?php

/* Check command line vs. form upload
--------------------------------------*/
if (isset($_SERVER['HTTP_HOST']) === false) {
    define('CLI', true);
} else {
    define('CLI', false);
}

if(CLI === false && isset($_FILES['xmlfile']) ===  false):
    include('includes/header.php');
?>
<style>
    .controls, ol{
        margin-bottom: 1em;;

    }

    #xmlfile {
        display: none;
    }

    .dummyfile input[type="text"] {
        width: 120px;
        display: inline;
    }
</style>
<form class="form-horizontal" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
    <fieldset>
        <legend>Config.xml to .csv</legend>
        <p>Use this page to convert Config.xml files to .csv to examine attributes that were added or modified.</p>
        <p>Steps</p>
        <ol>
            <li>Export the configuration from your server</li>
            <li>Unzip the Config file</li>
            <li>Open the reflection folder, and verify that a file named Config.xml exists</li>
            <li>Upload this Config.xml file using the form below</li>
            <li>A .csv file will be generated for you</li>
        </ol>
        <label class="control-label">File</label>

        <div class="controls">
            <input type="file" id="xmlfile" name="xmlfile">
            <div class="dummyfile">
                <input id="filename" type="text" class="input disabled" name="filename" readonly>
                <a id="fileselectbutton" class="btn">Choose...</a>
            </div>
        </div>

        <div class="controls">
            <button type="submit" class="btn">Upload</button>
        </div>
    </fieldset>
</form>
<script>
    $(document).ready(function(){

        $('#fileselectbutton').click(function(e){
            $('#xmlfile').trigger('click');
        });

        $('#xmlfile').change(function(e){
            var val = $(this).val();

            var file = val.split(/[\\/]/);

            $('#filename').val(file[file.length-1]);
        });
    });
</script>


<?php
    include('includes/footer.php');
endif;


if(CLI === true || isset($_FILES['xmlfile']) ===  true){

    function getParameterObject($xml){

        $result = array();


        foreach($xml->Parameter as $xmlParameters){

            $p = new StdClass();
            $p->id = (string) $xmlParameters['Id'];
            $p->type =  (string) $xmlParameters['Type'];
            $p->value =  (string) $xmlParameters['Value'];
            $result[$p->id] = $p;
        }

        return $result;

    }

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
                $createAttributes[] = getParameterObject($call);
                break;

            case "RestoreCustomAttribute":
                $restoreAttributes[] = getParameterObject($call);
                break;

            case "RestoreEnum":
                $item = getParameterObject($call);

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

    if(CLI === true){

        /* Output csv file
        --------------------------------*/
        $fp = fopen('attributes.csv', 'w');

        fputcsv($fp, $columns);
        foreach ($createInfo as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);

        echo 'attributes.csv was updated';

    } else {

        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=file.csv");
        header("Pragma: no-cache");
        header("Expires: 0");

        /* Output csv file
        --------------------------------*/
        $fp = fopen("php://output", "w");

        fputcsv($fp, $columns);
        foreach ($createInfo as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);

    }

}