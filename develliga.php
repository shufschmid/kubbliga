<?php
/*
Template Name: develliga
*/
get_header();
$prefix = 'tk_';
$subheadline = get_post_meta($post->ID, $prefix.'subheadline', true);
$sidebar_postition = get_post_meta($post->ID, $prefix . 'sidebar_position', true);
if ($sidebar_postition == '') {
    $sidebar_postition = 'right';
}

?>

   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>

    <style>
        @media screen and (max-width: 469px){
            .ligatable {
                margin-left: -25px  !important;
                margin-right: -25px  !important;
            }
        }

        .ligatable .nav-tabs {
            background-color: white;
        }
    </style>

    <div class="content left" xmlns="http://www.w3.org/1999/html">
        <div class="wrapper">
            <div class="content-full  left">


                <!-- CONTENT -->
                <div class='content-left content-margin left'>
                    <div class="title-on-page left">
                        <span>Kubb-Liga</span>
                        <p>V. 0.1 by baselcitykubb.ch 2015</p>
                    </div>
                    <?php
                    if(isset($_POST['resultat1'])) {
                        #Spiel hinzufügen
                        $spieldatetime = date("Y-m-d H:i:s");
                        $wpdb->insert(
                            'liga_spiele',
                            array(
                                'date' => $spieldatetime,
                                'spieler1' => $_POST['spieler1'],
                                'spieler2' => $_POST['spieler2'],
                                'resultat1' => $_POST['resultat1'],
                                'resultat2' => $_POST['resultat2']
                            )
                        );
                    }
                    if ( is_user_logged_in() ) {
                    ?>
                    <form style="text-align:center" method="post" action="http://www.baselcitykubb.ch/develliga" >
                        <select name="spieler1" size="1" style="width:100px">

                            <?php
                            get_currentuserinfo();
                            $neu = get_users();
                            echo "<option value=\"".$current_user->id."\" selected=\"selected\">".$current_user->user_nicename."</option>";

                            foreach ($neu as $spieler) {
                                echo "<option value=\"".$spieler->ID."\">   ".$spieler->user_nicename."</option>";
                            }

                            ?>
                        </select> <select name="spieler2" size="1" style="width:100px">

                            <?php
                            foreach ($neu as $spieler) {
                                echo "<option value=\"".$spieler->ID."\">   ".$spieler->user_nicename."</option>";
                            }
                            ?>

                        </select><br/>

                        <input style="width:30px" type="number" name="resultat1"> : <input style="width:30px" type="number" name="resultat2">
                        <br/><br/><input type="submit" name="submit" value="Resultat melden">
                        <hr/></form>

                    <?php
                    }
                    else {
                        wp_login_form();
                    }
                    ?>

                    <div class="ligatable">
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a data-toggle="tab" href="#kubbliga" >Liga</a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#lastyear">Champions-Race</a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#special">Early-Bird</a>
                            </li>
                        </ul>

                        <div class="tab-content">

                        <?php
                        chdir('wp-admin/liga');
                        include('kubbmaister.php');


                        for ($zeitraeume = 1; $zeitraeume <= 3; $zeitraeume++){

                        if($zeitraeume == 1){
                            $ranking = getRanking($spiele, $current_user->id);
                            $tabId = "kubbliga";

                        }
                        if($zeitraeume == 2){
                            $ranking = getRanking($spiele365, $current_user->id);
                            $tabId = "lastyear";
                        }
                        if($zeitraeume == 3){
                            $ranking = getRanking($spielesaison, $current_user->id);
                            $tabId = "special";
                        }
                        ?>

                    <div  id="<?php echo $tabId; ?>" class="<?php
                         if($zeitraeume == 1){
                            echo "tab-pane active";
                            }else{
                            echo "tab-pane";
                            }?>" >
                        <div class="table-responsive">
                        <table class="table table-striped table-condensed table-hover">
                            <thead>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th>Sp.</th>
                                <th>%S</th>
                                <th class="hidden-xs">Sätze G : V</th>
                                <th>Elo G</th>
                                <th class="hidden-xs">AG</th>
                                <th>Elo</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                        $i = 1;
                        foreach($ranking as $ligaItem){
                            if($ligaItem->elo != 1500){
                            ?>
                            <tr>
                                <td>
                                    <?php echo $i; $i++;?>.
                                </td>
                                <td>
                                    <?php echo get_avatar( $ligaItem->id, 20 ); ?>
                                </td>
                                <td class="hidden-xs">
                                    <?php
                                    echo "<a href=\"http://baselcitykubb.ch/?author=".$ligaItem->id."\">".$ligaItem->name."</a>";
                                    ?>
                                </td>
                                <td class="visible-xs">
                                    <?php
                                    echo "<a href=\"http://baselcitykubb.ch/?author=".$ligaItem->id."\">".substr($ligaItem->name, 0, 7);
                                    if(strlen($ligaItem->name)>7){
                                    echo "...";
                                    }
                                    echo "</a>";
                                    ?>
                                </td>
                                <td>
                                    <?php echo $ligaItem->anzSpiele;?>
                                </td>

                                <td>
                                    <?php echo intval($ligaItem->ProzSiege);?>%
                                </td>
                                <td class="hidden-xs">
                                    <?php echo $ligaItem->saetzeG.":".$ligaItem->saetzeV;?>
                                </td>
                                <td>
                                    <?php echo intval($ligaItem->EloGegner);?>
                                </td>
                                <td class="hidden-xs">
                                    <?php echo count($ligaItem->Gegner);?>
                                </td>

                                <td>
                                    <?php echo intval($ligaItem->elo);?>
                                </td>
                            </tr>

                        <?php } };?>
                            </tbody>
                    </table>
                        </div>
                   </div>
                    <?php }?>

                    </div>
                    </div>


                    <div class="shortcodes left">



                    </div>
                </div><!-- cotent-left -->


                <?php
                $sidebar_select = get_post_meta($post->ID, $prefix.'sidebar', true);
                tk_get_sidebar('Right', $sidebar_select);
                ?>

            </div><!--/content-full-->


        </div><!--/wrapper-->
    </div><!--/content-->









<?php get_footer(); ?>