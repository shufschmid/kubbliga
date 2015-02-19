<?php



function ImportCSV2Array($filename)
{
    $row = 0;
    $col = 0;

    $handle = @fopen($filename, "r");
    if ($handle)
    {
        while (($row = fgetcsv($handle, 4096)) !== false)
        {
            if (empty($fields))
            {
                $fields = $row;
                continue;
            }

            foreach ($row as $k=>$value)
            {
                $results[$col][$fields[$k]] = $value;
            }
            $col++;
            unset($row);
        }
        if (!feof($handle))
        {
            echo "Error: unexpected fgets() failn";
        }
        fclose($handle);
    }

    return $results;
}

function File_Put_Array($FileName, $ar) {
    return file_put_contents($FileName , '<?php $ar=' . var_export($ar, true) . ';');
}

#aktivespieler.json generieren (gibt Fehlermeldungen)

#Array definieren
$aktivespieler = Array();
#Spieler lesen
$filename = "Spieler.csv";
$SpielerArray = ImportCSV2Array($filename);

#Resultate lesen
$filename = "Partien.csv";
$ResultateArray = ImportCSV2Array($filename);

foreach ($SpielerArray as $Spielerrow)
{
    $anzahl = 0;

    foreach($ResultateArray as $ResultateRow) {
        if ($Spielerrow['nick'] == $ResultateRow['spieler1'] || $Spielerrow['nick'] == $ResultateRow['spieler2']) {
            $anzahl++;
        }
    }
    if($anzahl > 20){
        array_push($aktivespieler, $Spielerrow['nick']);

    }
}

?>