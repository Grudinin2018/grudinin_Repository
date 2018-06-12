<?php
    header('Content-Type: text/html; charset=utf-8');
    require_once("mysql.php");

    if (isset($_GET['data'])) {
      $data = json_decode($_GET['data']);
      error_log(print_r($data, true));
      $values_sql = '';
      foreach ($data as $key => $value) {
        $id = $value->id;
        $need = $value->need;
        $values_sql .= '('.(int)$id.','.(int)$need.'),';
      }
      $values_sql = rtrim($values_sql,",");
      $sql = "INSERT INTO my_prognoz (id,need) VALUES $values_sql
              ON DUPLICATE KEY UPDATE id=VALUES(id),need=VALUES(need);";
      mysqlQuery($sql);
      //error_log(print_r($sql, true));
    }

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
        $('._id').each(function (el, e) {
          var name = $(e).attr('name');
          var id = $(e).attr('value');
          _g_data[name].id = id;
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
           var name = $(e).attr('name');
          if (_g_data[name].need == '') {
            _g_data[name].need = $(e).val();
          }
        });
        $('._date').each(function (el, e) {
          var date = $(e).attr('value');
          var name = $(e).attr('name');
          _g_data[name].date = date;
        });
        $('._to_date').each(function (el, e) {
          var name = $(e).attr('name');
          if (_g_data[name].date == '') {
            date = e.value;
            $.datepicker.parseDate( "yy-mm-dd", date );
            _g_data[name].date = date;
          }
            
        });

        $.each(_g_data, function(key, value) {
          if (value.need != '' && value.date != '') {
            var name = key.split('_')[0];
            var type = key.split('_')[1];

            var list = getList(name);
            list.need = Number(list.need);
            list.stock = Number(list.stock);

            var detki = getDetki(name);
            detki.need = Number(detki.need);
            detki.stock = Number(detki.stock);

            var cvet = getCvet(name);
            cvet.need = Number(cvet.need);
            cvet.stock = Number(cvet.stock);

            var nowtime = new Date ();
            var date = new Date (value.date);

            function fix(arg) { if (String(arg).length == 1) return '0'+arg; else return String(arg); }

            if (type == 'цветущая')
            {
              if (value.need > value.stock) { //начинаем проверять детки и листы
                  console.log(key);
                  console.log('Цветущих требуется: ' + cvet.need);
                  console.log('Цветущих на складе: ' + cvet.stock);
                  console.log('Деток на складе: ' + detki.stock);
                  console.log('Листков на складе: ' + list.stock);

                  var diff = value.need - value.stock; // здесь сколько не хватает
                  var divTime = (date.getTime()-nowtime.getTime())/1000/60/60/24;

                  //высадить до
                  var ddt = date.getTime()-(180*1000*60*60*24);
                  var ddtDate2 = new Date(ddt);
                  console.log(ddtDate2.toISOString());

                  if ((divTime>=90)) { //если деткам хватит времени вырасти
                    diff = diff - detki.stock;
                    console.log(diff); 
                    if (diff <= 0) {  //если деток хватило, то садить не надо
                      console.log('Успех!');
                      console.log('Листья садить не нужно, ' + (cvet.need-cvet.stock) + 'шт. деток со склада успеют вырасти. Зарезервируйте их.');
                    }
                    else {
                      if (divTime>=180) {  // проверяем есть и хватит ли листков и времени вырасти
                        diff = diff - list.stock;
                          if (diff <=0) {         //листков на складе хватило
                            console.log('Успех!');
                            console.log('Листков на складе хватило, вам надо отложить ' + (cvet.need-cvet.stock-detki.stock) + 'шт. и посадить до' + ddtDate2); //здесь нужно получить крайнюю дату посадки листков
                          }
                          else { //листков на складе не хватило
                            console.log('Не получится! Листков на складе не хватило!')
                            console.log('Нужно где-то найти и посадить' + (cvet.need-cvet.stock-detki.stock-list.stock) + ' листков до ' + ddtDate2); //здесь вконце надо вставить дату край, когда успеют еще вырасти
                          }
                      }
                      else { //если листков нет (не хватает) на складе, либо они не вырастут по времени
                        console.log('Не получится!');
                        console.log('Цветущих и деток не хватает, а новые не успеют вырасти');
                      }
                    }
                  }
                  else {
                    console.log('Не получится!');
                    console.log('Цветущих фиалок не хватает, а новые не успеют вырасти');
                  }
              }
              else { 
                console.log(key);
                console.log('Успех!');
                console.log('Вам хватает цветущих фиалок на складе');
                console.log('Цветущих требуется: '+(cvet.need));
                console.log('Цветущих на складе: '+(cvet.stock));
              }
            } 
            else if (type == 'детки')
            {
              if (value.need > value.stock) { //начинаем проверять детки и листы
                  console.log(key);
                  console.log('Деток требуется: ' + detki.need);
                  console.log('Деток на складе: ' + detki.stock);
                  console.log('Листков на складе: ' + list.stock);

                  var diff = value.need - value.stock; // здесь сколько не хватает
                  var divTime = (date.getTime()-nowtime.getTime())/1000/60/60/24;

                  //высадить до
                  var ddt = date.getTime()-(90*1000*60*60*24);
                  var ddtDate2 = new Date(ddt);
                  console.log(ddtDate2.toISOString());
                  console.log(fix(ddtDate2.getDate())+'.'+(fix(ddtDate2.getMonth()+1))+'.'+ddtDate2.getFullYear());
                  //console.log(nowtime.getDate()+'.'+nowtime.getMonth()+'.'+nowtime.getFullYear());
                  //$.datepicker.parseDate(ddtDate2.toISOString());

                  if ((divTime>=90)) { //если листьям хватит времени вырасти до деток
                    diff = diff - list.stock;
                    console.log(diff); 
                    if (diff <= 0) {  //если листьев хватило, то искать не надо
                      console.log('Успех!');
                      console.log('Листьев на складе хватает, ' + (detki.need-detki.stock) + 'шт. листьев со склада успеют вырасти. Зарезервируйте и посадите их до ' + ddtDate2);
                    }
                    else {
                            console.log('Не получится! Листков на складе не хватило!')
                            console.log('Нужно где-то найти и посадить' + (detki.need-detki.stock-list.stock) + ' листков до ' + ddtDate2); //здесь вконце надо вставить дату край, когда успеют еще вырасти
                    }
                  }
                  else {
                    console.log('Не получится!');
                    console.log('Деток на складе не хватает, а новые не успеют вырасти');
                  }
              }
              else { 
                console.log(key);
                console.log('Успех!');
                console.log('Вам хватает деток на складе');
                console.log('Деток требуется: '+ (detki.need));
                console.log('Деток на складе: '+ (detki.stock));
              }
            }

            if (type == 'лист')
            {
              if (value.need > value.stock) { //начинаем проверять детки и листы
                  console.log(key);
                  console.log('Листков требуется: ' + list.need);
                  console.log('Листков на складе: ' + list.stock);
                  console.log('Цветущих на складе: ' + cvet.stock);
                  console.log('Деток на складе: ' + detki.stock);

                  var diff = value.need - value.stock; // здесь сколько не хватает
                  var divTime = (date.getTime()-nowtime.getTime())/1000/60/60/24;

                  //высадить до
                  var ddt = date.getTime()-(90*1000*60*60*24);
                  var ddtDate2 = new Date(ddt);
                  //console.log(ddtDate2.toISOString());
                  //console.log(ddtDate2.getDate()+'.'+ddtDate2.getMonth()+'.'+ddtDate2.getFullYear());
                  //$.datepicker.parseDate(ddtDate2);

                  if ((diff-(cvet.stock*20))<=0) { // листьев и цветущих хватит.
                    if (list.stock>0) {
                      console.log('Успех!');
                      console.log('Сейчас есть '+list.stock+' листков'); 
                      console.log('Отломите еще '+diff+' листков от '+ (cvet.stock-Math.floor((cvet.stock*20-diff)/20))+' цветущих'); 
                    }
                    else {
                      console.log('Успех!');
                      console.log('Просто отломите '+diff+' листков от '+ (cvet.stock-Math.floor((cvet.stock*20-diff)/20))+' цветущих'); 
                    }
                  }
                  else { // если листьев и цветущих не хватило проверяем
                    if (divTime>=90) { //хватает ли времени
                      if ((diff-cvet.stock*20-detki.stock*20)<=0) { //если кол-ва на складе хватает, а не успеваем только по дате
                        if (list.stock>0) {
                          console.log('Сейчас есть '+list.stock+' листков');
                          if (cvet.stock>0) {
                            console.log('Сейчас есть '+cvet.stock+' цветущих фиалок, из них получится '+cvet.stock*20+' листков');
                          }
                        }
                        console.log('Отломите еще '+diff+' листков от '+ (cvet.stock-Math.floor((cvet.stock*20-diff)/20))+' цветущих'); 
                      }
                    }
                    else {
                      if ((detki.stock>0) && ((diff-cvet.stock*20-detki.stock*20)<=0)){
                        console.log('Не получится!');
                        console.log('Листков и цветов на складе не хватает, чтобы получить листья к указанной дате, а '+(Math.ceil((diff-cvet.stock*20)/20))+' шт. деток не успеют вырасти. Предлагаем сдвинуть дату на ... дней');
                        console.log('Из них нужно оторвать '+(diff-cvet.stock*20)+' листков');
                      }
                      else {
                        console.log('Не получится!');
                        console.log('Листков, цветов и деток на складе не хватает');
                      }
                      }
                  }
              }
              else { 
                console.log(key);
                console.log('Успех!');
                console.log('Вам хватает листков на складе');
                console.log('Листков требуется: '+ (list.need));
                console.log('Листков на складе: '+ (list.stock));
              }
            }
          }

          var json = JSON.stringify(_g_data);
          $('#send_form input').val(json);
          $('#send_form').submit();

        });

      });
 
  } );
  function getDetki(name) { //возвращает объект деток
    return _g_data[name+"_"+'детки'];
  }
  function getCvet(name) { //возвращает объект деток
    return _g_data[name+"_"+'цветущая'];
  }
  function getList(name) { //возвращает объект деток
    return _g_data[name+"_"+'лист'];
  }
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
                    echo "<div name='{$ska_name}' class='_id' value='{$typevalue['id']}'>{$typevalue['id']}</div>";
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
        <button class="button _calc">Вычислить</button>
        <form id="send_form">
        <input type='hidden' name='data'>
        </form>
        <?php
    }
    init();

?>