<?php
#Spieler aus Spieler.csv (von Beno) einlesen und als Array $aktivespieler speichern
$SpielerArray = Array();
$handle = @fopen("Spieler.csv", "r");
while (($row = fgetcsv($handle)) !== false){
    array_push($SpielerArray, $row[1]);
}
fclose($handle);
array_shift($SpielerArray);
#Ausgabe SpielerArray:
//print_r($SpielerArray);

echo "<hr>";

#Resultate aus Partien.csv (von Beno) einlesen und als Array $ResultateArray speichern,
#wobei: [0] = spielerA, [1] = SpielerB, [2] = ResultatA, [3] = ResultatB, [4] = datum/zeit im SQL-Format
$ResultateArray = Array();
$handle = @fopen("Partien.csv", "r");
while (($row = fgetcsv($handle)) !== false){
    #Zeilen filtern, datetime-erstellen


    $date = explode('.', $row[6]);
    $time = explode(':', $row[8]);
    $datetime = mktime($time[0],$time[1],$time[2],$date[1],$date[0],$date[2]);
    $mysqldate = date( 'Y-m-d H:i:s', $datetime );


    $relevante_daten = Array($row[1], $row[2], $row[3], $row[4], $mysqldate);
    array_push($ResultateArray, $relevante_daten);
}
fclose($handle);
#Ausgabe ResultateArray
array_shift($ResultateArray);
//print_r($ResultateArray);

#Array definieren
$aktivespieler = Array();

foreach ($SpielerArray as $spieler)
{
    $anzahl = 0;

    foreach($ResultateArray as $ResultatArray) {
        if ($spieler == $ResultatArray[0] || $spieler == $ResultatArray[1]) {
            $anzahl++;
        }
    }
    #hier wird definiert, wieviele Spiele ein Spieler gespielt haben muss, um als aktiver Spieler zu gelten.

    if($anzahl > 20){
        array_push($aktivespieler, $spieler);

    }
}
#lädt $wordpress_spielerIDs - Array, das als $wordpress_spielerIDs["haemp"] die Wordpress-ID ausgibt
include 'wordpressID.php';

#Ausgabe aktive Spieler
foreach($aktivespieler as $spieler){
    echo $spieler." (wordpress-id: ".$wordpress_spielerIDs[$spieler].")<br/>";
}

#daten generieren, um in mysql-tabelle "liga_spiele" abzuspeichern
#besteht aus `date`, `id`, `spieler1`, `spieler2`, `resultat1`, `resultat2`

foreach($ResultateArray as $ResultatArray) {
    if (array_key_exists($ResultatArray[0], $wordpress_spielerIDs) && array_key_exists($ResultatArray[1], $wordpress_spielerIDs)) {
        #bedeutet sowohl 1. als aus 2. spieler sind "aktive Spieler"


        $wpdb->insert(
            'liga_spiele',
            array(
                'spieler1' => $wordpress_spielerIDs[$ResultatArray[0]],
                'spieler2' => $wordpress_spielerIDs[$ResultatArray[1]],
                'resultat1' => intval($ResultatArray[2]),
                'resultat2' => intval($ResultatArray[3]),
                'date' => $ResultatArray[4]
            )
        );



    }
}
die();

$insert = array(
    'spieler1' => $wordpress_spielerIDs[$ResultatArray[0]],
    'spieler2' => $wordpress_spielerIDs[$ResultatArray[1]],
    'resultat1' => intval($ResultatArray[2]),
    'resultat2' => intval($ResultatArray[3]),
    'date' => $ResultatArray[4]
);
echo $insert['date']."<br/>";

?>