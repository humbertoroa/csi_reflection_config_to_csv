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