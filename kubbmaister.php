<?php

function berechnen($elo1, $elo2, $saetze1, $saetze2){
    #formel P = K G (W - W_e) vgl. http://en.wikipedia.org/wiki/World_Football_Elo_Ratings#Basic_calculation_principles

    #k berechnen
    if($saetze1 > 5 || $saetze2 > 5){       $k = 60;    }
    elseif($saetze1 > 3 || $saetze2 > 3){   $k = 40;    }
    else{                                   $k = 20;    }

    #G berechnen
    if(abs($saetze1-$saetze2) == 2){        $g = 3/2;    }
    elseif(abs($saetze1-$saetze2) > 2){     $g = (11 + abs($saetze1-$saetze2))/8;    }
    else{                                   $g = 1;    }

    #w berechnen
    if($saetze1>$saetze2){                  $w = 1;    }
    elseif($saetze1<$saetze2){              $w = 0;    }
    else{                                   $w = 0.5;  }

    #W_e berechnen
    $we = 1 / (pow(10, -(($elo1-$elo2)/400)) +1);

    $punkte = $k * $g * ($w - $we);

    return $punkte;
}
#Name:	DJ
#Gegner	Elo	    Anz. Spiele	% Siege	Sätze S:N	Punkte
#Mu	    1922	22	        69	    44:12	    53
class KubbLigaArrayItem {
    public $id;
    public $name;
    public $anzSpiele;
    public $anzSiege;
    public $ProzSiege;
    public $saetzeG;
    public $saetzeV;
    public $Gegner;
    public $EloGegner;
    public $AnzGegner;
    public $elo;
    public $anzSpieleX;
    public $anzSiegeX;
    public $ProzSiegeX;
    public $saetzeGX;
    public $saetzeVX;
    public $punkteX;
    public $eloentwicklungX;

}

// sort function
function sortByElo($kubbLigaItem1, $kubbLigaItem2){

    if($kubbLigaItem1->elo == $kubbLigaItem2->elo){
        return 0;
    }

    return ($kubbLigaItem1->elo < $kubbLigaItem2->elo) ? 1 : -1;

}

//holt spiele
$spiele = $wpdb->get_results("SELECT * FROM `liga_spiele` ORDER BY `date` ASC ");
$spiele365 = $wpdb->get_results("SELECT * FROM `liga_spiele` WHERE date >= DATE(NOW() - INTERVAL 12 MONTH) ORDER BY `date` ASC ");
$spielesaison = $wpdb->get_results("SELECT * FROM `liga_spiele` WHERE (date BETWEEN '2013-01-01' AND '2013-05-01') ORDER BY `date` ASC ");


function getEinzelspieler($spiele, $curauth){
    return $curauth;
}
function getRanking($spiele, $X)
{
    //holt benutzer
    $allewordpressnutzer = get_users();


    //definitives array, das zurückgegeben wird
    $ranking = Array();

    foreach ($allewordpressnutzer as $spieler) {
        $entry = new KubbLigaArrayItem(); // eine neue, leere Instanz von einem Tabelleneitrag

        // befüllt das neue objekt mit Name und Standard-Elo
        $entry->id = $spieler->id;
        $entry->name = $spieler->user_nicename;
        $entry->anzSpiele = 0;
        $entry->anzSiege = 0;
        $entry->ProzSiege = 0;
        $entry->saetzeG = 0;
        $entry->saetzeV = 0;
        $entry->EloGegner = 1500;
        $entry->Gegner = Array();
        $entry->elo = 1500;
        $entry->anzSpieleX = 0;
        $entry->anzSiegeX = 0;
        $entry->ProzSiegeX = 0;
        $entry->saetzeGX = 0;
        $entry->saetzeVX = 0;
        $entry->punkteX = 0;
        $entry->eloentwicklungX = Array();

        // array_push($ranking, $entry); // das neu erstellte entry Item wird dem array hinzugefügt
        $ranking[$spieler->id] = $entry;
    }

    foreach ($spiele as $spiel) {

        $punkte = berechnen($ranking[$spiel->spieler1]->elo, $ranking[$spiel->spieler2]->elo, $spiel->resultat1, $spiel->resultat2);
        #elo hinzufügen
        $ranking[$spiel->spieler1]->elo += $punkte;
        $ranking[$spiel->spieler2]->elo -= $punkte;
        #Anzahl Spiele erhöhen
        $ranking[$spiel->spieler1]->anzSpiele += 1;
        $ranking[$spiel->spieler2]->anzSpiele += 1;
        #Anzahl Siege (des Siegers) erhöhen
        if($spiel->resultat1 > $spiel->resultat2){
            $ranking[$spiel->spieler1]->anzSiege += 1;
        }
        elseif($spiel->resultat1 < $spiel->resultat2){
            $ranking[$spiel->spieler2]->anzSiege += 1;
        }
        #Prozent gewonnene Spiele berechnen
        $ranking[$spiel->spieler1]->ProzSiege = ($ranking[$spiel->spieler1]->anzSiege / $ranking[$spiel->spieler1]->anzSpiele) * 100;
        $ranking[$spiel->spieler2]->ProzSiege = ($ranking[$spiel->spieler2]->anzSiege / $ranking[$spiel->spieler2]->anzSpiele) * 100;


        #gewonnene / verlorene Sätze hinzufügen
        $ranking[$spiel->spieler1]->saetzeG += $spiel->resultat1;
        $ranking[$spiel->spieler1]->saetzeV += $spiel->resultat2;
        $ranking[$spiel->spieler2]->saetzeG += $spiel->resultat2;
        $ranking[$spiel->spieler2]->saetzeV += $spiel->resultat1;

        #Durchschnittlicher Elo Gegner
        $ranking[$spiel->spieler1]->EloGegner = ($ranking[$spiel->spieler1]->EloGegner * ($ranking[$spiel->spieler1]->anzSpiele - 1) + $ranking[$spiel->spieler2]->elo) / $ranking[$spiel->spieler1]->anzSpiele;
        $ranking[$spiel->spieler2]->EloGegner = ($ranking[$spiel->spieler2]->EloGegner * ($ranking[$spiel->spieler2]->anzSpiele - 1) + $ranking[$spiel->spieler1]->elo) / $ranking[$spiel->spieler2]->anzSpiele;

        #Array Gegner füllen (für Anzahl Gegner)
        if(!in_array($ranking[$spiel->spieler2]->id, $ranking[$spiel->spieler1]->Gegner, true)){
            array_push($ranking[$spiel->spieler1]->Gegner, $ranking[$spiel->spieler2]->id);
        }
        if(!in_array($ranking[$spiel->spieler1]->id, $ranking[$spiel->spieler2]->Gegner, true)){
            array_push($ranking[$spiel->spieler2]->Gegner, $ranking[$spiel->spieler1]->id);
        }
        #Daten für Einzelspieler-Ansicht sammeln
        if($spiel->spieler1 == $X){
            #Anz Spiele gegen Gegner X erhöhen
            $ranking[$spiel->spieler2]->anzSpieleX += 1;



            #Bei Sieg Gegenspieler: Anzahl Siege gegen Spieler X erhöhen
            if($spiel->resultat1 < $spiel->resultat2){
                $ranking[$spiel->spieler2]->anzSiegeX += 1;
            }

            #Gewinnquote in Prozent gegen Spieler X
            $ranking[$spiel->spieler2]->ProzSiegeX = ($ranking[$spiel->spieler2]->anzSiegeX / $ranking[$spiel->spieler2]->anzSpieleX) * 100;

            #gewonnene / verlorene Sätze gegen Spieler X hinzufügen
            $ranking[$spiel->spieler2]->saetzeGX += $spiel->resultat2;
            $ranking[$spiel->spieler2]->saetzeVX += $spiel->resultat1;

            #gewonnene / verlorene Elo-Punkte gegen Spieler X hinzufügen
            $ranking[$spiel->spieler2]->punkteX -= $punkte;

            #Eloentwicklung
            $array = [
                "jsonDate" => $spiel->date,
                "jsonHitCount" => intval($ranking[$spiel->spieler1]->elo),
                "seriesKey" => "Website Usage"
            ];
            array_push($ranking[$spiel->spieler1]->eloentwicklungX, $array);

        }
        elseif($spiel->spieler2 == $X){
            #Anz Spiele gegen Gegner X erhöhen
            $ranking[$spiel->spieler1]->anzSpieleX += 1;

            #Bei Sieg Gegenspieler: Anzahl Siege gegen Spieler X erhöhen
            if($spiel->resultat1 > $spiel->resultat2){
                $ranking[$spiel->spieler1]->anzSiegeX += 1;
            }

            #Gewinnquote in Prozent gegen Spieler X
            $ranking[$spiel->spieler1]->ProzSiegeX = ($ranking[$spiel->spieler1]->anzSiegeX / $ranking[$spiel->spieler1]->anzSpieleX) * 100;

            #gewonnene / verlorene Sätze gegen Spieler X hinzufügen
            $ranking[$spiel->spieler1]->saetzeGX += $spiel->resultat1;
            $ranking[$spiel->spieler1]->saetzeVX += $spiel->resultat2;

            #gewonnene / verlorene Elo-Punkte gegen Spieler X hinzufügen
            $ranking[$spiel->spieler1]->punkteX += $punkte;
        }
    }

    // do the actual sorting
    usort($ranking, "sortByElo");

    // return the array
    return $ranking;
}
?>