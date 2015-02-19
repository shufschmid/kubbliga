<?php
get_header();
$prefix = 'tk_';
$curauth = (isset($_GET['author_name'])) ? get_user_by('slug', $author_name) : get_userdata(intval($author));
?>

<!-- CONTENT -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
<div class="content left category-page">
    <div class="wrapper">
        <div class="content-full left">

            <div class="content-left left">

                <div class="single-autor left">
                    <?php echo get_avatar(get_the_author_meta('ID', $curauth->ID), '81')?>
                    <span><?php echo get_the_author_meta('nickname', $curauth->ID)?></span>
                    <?php
                    chdir('wp-admin/liga');
                    include('kubbmaister.php'); //aber es macht nix

                    // sort function
                    function sortByLostPoints($kubbLigaItem1, $kubbLigaItem2){
                        if($kubbLigaItem1->punkteX == $kubbLigaItem2->punkteX){
                            return 0;
                        }
                        return ($kubbLigaItem1->punkteX < $kubbLigaItem2->punkteX) ? 1 : -1;
                    }

                    $einzelspieler = getRanking($spiele, $curauth->ID);

                    usort($einzelspieler, "sortByLostPoints");

                    ?>
                    <style>
                        table, th, td {
                            border: 1px solid black;
                        }
                        line {
                            stroke: black;
                        }

                        path {
                            fill: none;
                            stroke: green;
                        }
                    </style>
                    <table><?php
                        $i = 1;
                        foreach($einzelspieler as $ligaItem){
                            if(count($ligaItem->eloentwicklungX)>0){
                                $eloentwicklung = $ligaItem->eloentwicklungX;
                            }
                            if($ligaItem->anzSpieleX > 0){
                                ?>
                                <tr>
                                    <td>
                                        <?php echo $i; $i++;?>.
                                    </td>
                                    <td>
                                        <?php
                                        echo "<a href=\"http://baselcitykubb.ch/?author=".$ligaItem->id."\">".$ligaItem->name."</a>";
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo $ligaItem->anzSpieleX;?>
                                    </td>

                                    <td>
                                        <?php echo intval($ligaItem->ProzSiegeX);?> %
                                    </td>
                                    <td>
                                        <?php echo $ligaItem->saetzeGX.":".$ligaItem->saetzeVX;?>
                                    </td>
                                    <td>
                                        <?php echo intval($ligaItem->elo);?>
                                    </td>
                                    <td>
                                        <?php echo intval($ligaItem->punkteX);?>
                                    </td>

                                </tr>

                            <?php } };?>
                    </table>
                    <script type="text/javascript" src="http://mbostock.github.com/d3/d3.js?2.4.6"></script><script type="text/javascript" src="http://mbostock.github.com/d3/d3.time.js?2.4.6"></script>
                    <script type="text/javascript">
                    <?php echo "var data = ".json_encode($eloentwicklung).";"?>
                    // helper function
                    function getDate(d) {
                        return new Date(d.jsonDate);
                    }

                    // get max and min dates - this assumes data is sorted
                    var minDate = getDate(data[0]),
                        maxDate = getDate(data[data.length-1]);

                    var w = 450,
                        h = 275,
                        p = 30,
                        y = d3.scale.linear().domain([1000, 2500]).range([h, 0]),
                        x = d3.time.scale().domain([minDate, maxDate]).range([0, w]);

                    var vis = d3.select("body")
                        .data([data])
                        .append("svg:svg")
                        .attr("width", w + p * 2)
                        .attr("height", h + p * 2)
                        .append("svg:g")
                        .attr("transform", "translate(" + p + "," + p + ")");

                    var rules = vis.selectAll("g.rule")
                        .data(x.ticks(5))
                        .enter().append("svg:g")
                        .attr("class", "rule");

                    rules.append("svg:line")
                        .attr("x1", x)
                        .attr("x2", x)
                        .attr("y1", 0)
                        .attr("y2", h - 1);

                    rules.append("svg:line")
                        .attr("class", function(d) { return d ? null : "axis"; })
                        .attr("y1", y)
                        .attr("y2", y)
                        .attr("x1", 0)
                        .attr("x2", w + 1);

                    rules.append("svg:text")
                        .attr("x", x)
                        .attr("y", h + 3)
                        .attr("dy", ".71em")
                        .attr("text-anchor", "middle")
                        .text(x.tickFormat(10));

                    rules.append("svg:text")
                        .attr("y", y)
                        .attr("x", -3)
                        .attr("dy", ".35em")
                        .attr("text-anchor", "end")
                        .text(y.tickFormat(10));

                    vis.append("svg:path")
                        .attr("class", "line")
                        .attr("d", d3.svg.line()
                            .x(function(d) { return x(getDate(d)) })
                            .y(function(d) { return y(d.jsonHitCount) })
                    );

                    vis.selectAll("circle.line")
                        .data(data)
                        .enter().append("svg:circle")
                        .attr("class", "line")
                        .attr("cx", function(d) { return x(getDate(d)) })
                        .attr("cy", function(d) { return y(d.jsonHitCount); })
                        .attr("r", 3.5);
                    </script>
                </div><!--/single-autor-->



            </div><!--/content-left-->

            <?php
            /* include sidebar */
            tk_get_sidebar('Right', 'Archive/Search');
            ?>

        </div><!--/content-full-->
    </div><!--/wrapper-->
</div><!--/content-->

<?php get_footer(); ?>