<?php
    header('Content-Type: text/html; charset=utf-8');
    require_once("mysql.php");

    ?>

    <script type="text/javascript" src="Lib/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="Lib/jquery-ui-1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" type="text/css" href="Lib/jquery-ui-1.12.1/jquery-ui.min.css">
    <link rel="stylesheet" type="text/css" href="Lib/jquery-ui-1.12.1/jquery-ui.structure.min.css">
    <link rel="stylesheet" type="text/css" href="Lib/jquery-ui-1.12.1/jquery-ui.theme.min.css">

    <?php

    function init() {
        $query = "  SELECT 
                    POST.id as id, POST.post_title as title, META.meta_value as stock,
                    PROGNOZ.need as need, PROGNOZ.diff as diff, PROGNOZ.date as `date`
                    from wp_posts POST
                    inner join
                    (
                        select 
                        post_id, meta_value
                        from wp_postmeta
                        where meta_key = '_stock'
                    ) META on META.post_id = POST.ID 
                    LEFT OUTER JOIN
                    (
                        select *
                        from my_prognoz
                    ) PROGNOZ on PROGNOZ.id = POST.ID
                    where POST.post_type = 'product'
                    order by POST.post_date desc
        ";
        $res = mysqlQuery($query);
        //print_r($res);
        $data = [];
        while ($row = $res->fetch_assoc())
        {
            list($name, $type) = explode(' (', $row['title']);
            $type = str_replace(")","", $type);
            $data[$name]['name'] = $name;
            $data[$name]['stock'] += $row['stock'];
            $data[$name]['type'][$type]['name'] = $type;
            $data[$name]['type'][$type]['stock'] = $row['stock'];
            $data[$name]['type'][$type]['need'] = $row['need'];
            $data[$name]['type'][$type]['diff'] = $row['diff'];
            $data[$name]['type'][$type]['id'] = $row['id'];
            $data[$name]['type'][$type]['date'] = $row['date'];
        }
        //print_r($data);
        ?>
        <script>
            $( function() {
                $( "#accordion" ).accordion();
                $( ".button" ).button();
            } );
        </script>
          <script>
  $( function() {
    
    var to = $( ".to" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        numberOfMonths: 1,
        dateFormat: "yy-mm-dd",
        minDate: "0"
      })
      .on( "change", function() {
        
      });
 
  } );
  </script>
        <button class="button">Вычислить</button>
        <hr>
        <div id="accordion">
        <?php
        foreach ($data as $key => $value) 
        {e
            ?>
            <h3><b><?php echo $key; ?></b> Всего: <?php echo $value['stock']; ?> </h3>
            <div>
                <?php
                foreach ($value['type'] as $typevalue) 
                {
                    echo "<div name='{$key}' class='_name'>{$typevalue['name']}</div>";
                    echo "<div name='{$key}' class='_stock'>Stock: {$typevalue['stock']}</div>";
                    echo "<div name='{$key}' class='_need'>Need: {$typevalue['need']}</div>";
                    echo "<div name='{$key}' class='_diff'>Diff: {$typevalue['diff']}</div>";
                    echo "<div name='{$key}' class='_date'>Date: {$typevalue['date']}</div>";
                    ?>
                    <p>
                    <br><input type="number" placeholder="Сколько нужно" style="width: 200px;">
                    <label for='<?php echo $key; ?>'>До</label>
                    <input type="text" class="to _to_date" name='<?php echo $key; ?>'>
                    </p>
                    <?php
                }
                ?>
            </div>
            <?php
        }
        ?>
        </div>
        <hr>
        <button class="button">Вычислить</button>
        <?php
    }
    init();

?>