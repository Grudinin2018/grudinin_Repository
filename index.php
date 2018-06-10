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
          var _g_data = {};
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

      $('._calc').click(function () {
        $('._name').each(function (el, e) {
          var name = $(e).attr('name');
          _g_data[name] = {};
        });
        $('._stock').each(function (el, e) {
          var stock = $(e).attr('value');
          var name = $(e).attr('name');
          _g_data[name].stock = stock;
        });
        $('._need').each(function (el, e) {
          var need = $(e).attr('value');
          var name = $(e).attr('name');
          _g_data[name].need = need;
        });
        $('._to_need').each(function (el, e) {
          console.log(e.value);
          
          var name = $(e).attr('name');
          if (_g_data[name].need == null) {
            _g_data[name].need = e.value;
          }
        });
        $('._date').each(function (el, e) {
          var date = $(e).attr('value');
          var name = $(e).attr('name');
          _g_data[name].date = date;
        });
        $('._to_date').each(function (el, e) {
          var name = $(e).attr('name');
          if (_g_data[name].date == null) {
            date = e.value;
            $.datepicker.parseDate( "yy-mm-dd", date );
            _g_data[name].date = date;
          }
            
        });

        $.each(_g_data, function(key, value) {
          //console.log(value);
          
          if (value.need != '' && value.date != '') {
            var name = key.split('_')[0];
            var type = key.split('_')[1];
            if (type == 'цветущая')
            {
              var date = value.date;
              console.log(date);
            }
          }

        });

      });
 
  } );
  </script>
        <button class="button _calc">Вычислить</button>
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
                  $ska_name = $key.'_'.$typevalue['name'];
                    echo "<div name='{$ska_name}' class='_name'>{$typevalue['name']}</div>";
                    echo "<div name='{$ska_name}' class='_stock' value='{$typevalue['stock']}'>Stock: {$typevalue['stock']}</div>";
                    echo "<div name='{$ska_name}' class='_need' value='{$typevalue['need']}'>Need: {$typevalue['need']}</div>";
                    echo "<div name='{$ska_name}' class='_diff'>Diff: {$typevalue['diff']}</div>";
                    echo "<div name='{$ska_name}' class='_date' value='{$typevalue['date']}'>Date: {$typevalue['date']}</div>";
                    ?>
                    <p>
                    <br><input type="number" name='<?php echo $ska_name ?>' class="_to_need" placeholder="Сколько нужно" style="width: 200px;">
                    <label for='<?php echo $key; ?>'>До</label>
                    <input type="text" class="to _to_date" name='<?php echo $ska_name ?>'>
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