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
class KubbLigaArrayItem {
    public $rang;
    public $id;
    public $name;
    public $anzSpiele;
    public $elo;
    //+ zusätzliche
}

// sort function
function sortByElo($kubbLigaItem1, $kubbLigaItem2){
    return gmp_cmp($kubbLigaItem1->elo, $kubbLigaItem2->elo) * -1; // multiply by -1 as we need the inverse order
}

function get_ranking()
{
    //holt benutzer
    $allewordpressnutzer = get_users();

    //holt spiele
    $spiele = $wpdb->get_results("SELECT * FROM `liga_spiele` ORDER BY `date` ASC ");//geht eventuell nicht mehr, ist aber richtig sortiert

    //definitives array, das zurückgegeben wird
    $ranking = Array();

    foreach ($allewordpressnutzer as $spieler) {
        $entry = new KubbLigaArrayItem(); // eine neue, leere Instanz von einem Tabelleneitrag

        // befüllt das neue objekt mit Name und Standard-Elo
        $entry->name = $spieler->user_nicename;
        $entry->id = $spieler->id;
        $entry->elo = 1500;
        $entry->anzSpiele = 0;

        // array_push($ranking, $entry); // das neu erstellte entry Item wird dem array hinzugefügt
        $ranking[$spieler->id] = $entry;
    }

    foreach ($spiele as $spiel) {

        $punkte = berechnen($ranking[$spiel->spieler1]->elo, $ranking[$spiel->spieler2]->elo, $spiel->resultat1, $spiel->resultat2);
        $ranking[$spiel->spieler1]->elo += $punkte;
        $ranking[$spiel->spieler2]->elo -= $punkte;

        $ranking[$spiel->spieler1]->anzSpiele += 1;
        $ranking[$spiel->spieler2]->anzSpiele += 1;

    }

    // do the actual sorting
    usort($ranking, "sortByElo");

    // return the array
    return $ranking;
}
?>