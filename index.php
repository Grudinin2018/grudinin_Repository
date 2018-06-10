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
                    POST.post_title as title, META.meta_value as stock,
                    PROGNOZ.need as need, PROGNOZ.diff as diff
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
    var dateFormat = "mm/dd/yy",
      from = $( ".from" )
        .datepicker({
          defaultDate: "+1w",
          changeMonth: true,
          numberOfMonths: 1
        })
        .on( "change", function() {
          to.datepicker( "option", "minDate", getDate( this ) );
        }),
      to = $( ".to" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        numberOfMonths: 1
      })
      .on( "change", function() {
        from.datepicker( "option", "maxDate", getDate( this ) );
      });
 
    function getDate( element ) {
      var date;
      try {
        date = $.datepicker.parseDate( dateFormat, element.value );
      } catch( error ) {
        date = null;
      }
 
      return date;
    }
  } );
  </script>
                      <label for="from">От</label>
                    <input type="text" class="from" name="from">
                    <label for="to">До</label>
                    <input type="text" class="to" name="to">
                      <br><br>
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
                    ?>
                    <p><?php echo $typevalue['name'] . ": " . $typevalue['stock']; ?>
                    <br><input type="number" placeholder="Сколько нужно" style="width: 200px;">
                    <label for="from">От</label>
                    <input type="text" class="from" name="from">
                    <label for="to">До</label>
                    <input type="text" class="to" name="to">
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
                            <label for="from">От</label>
                    <input type="text" class="from" name="from">
                    <label for="to">До</label>
                    <input type="text" class="to" name="to"><br><br>
        <button class="button">Вычислить</button>
        <?php
    }
    init();

?>