<?php
    header('Content-Type: text/html; charset=utf-8');
    require_once("mysql.php");

    if (isset($_GET['data'])) {
      $data = json_decode($_GET['data']);
      error_log(print_r($data, true));
      foreach ($data as $key => $value) {
        $sql = "SELECT `id` FROM `my_prognoz` WHERE `id` = '{$value->id}'";
        $res = mysqlQuery($sql);
        if ($res->num_rows > 0) //В базе уже есть значение
        {
          $sql = "UPDATE `my_prognoz` SET `need` = '{$value->need}', `date` = '{$value->date}', `diff` = '{$value->diff}'
                  WHERE `id` = '{$value->id}'";
          mysqlQuery($sql);
        }
        else //В базе нет значения
        {
          $sql = "INSERT INTO `my_prognoz` (`id`, `need`, `date`, `diff`) VALUES ('{$value->id}', '{$value->need}', '{$value->date}', '{$value->diff}')";
          mysqlQuery($sql);
        }
      }
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
          var _g_string = '';
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
        $('._diff').each(function (el, e) {
          var diff = $(e).attr('value');
          var name = $(e).attr('name');
          if (diff == '') 
            diff = 0;
          else
            _g_data[name].diff = Number(diff);
        });
        $('._need').each(function (el, e) {
          var need = $(e).attr('value');
          var name = $(e).attr('name');
          _g_data[name].need = need;
        });
        $('._to_need').each(function (el, e) {
          if ($(e).val() != '') {
            var name = $(e).attr('name');
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
          if (e.value != '') {
            date = e.value;
            $.datepicker.parseDate( "yy-mm-dd", date );
            _g_data[name].date = date;
          }
            
        });

        $.each(_g_data, function(key, value) {
          if (value.need != '' && value.date != '') {
            //get diff
            value.diff = Number(value.stock) - Number(value.need);
            //get diff

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

            function my_log_date(date) {
              return my_log(fix(date.getDate())+'.'+(fix(date.getMonth()+1))+'.'+date.getFullYear());
            }

            function vivoddate(date) {
              var a = fix(date.getDate())+'.'+(fix(date.getMonth()+1))+'.'+date.getFullYear();
              return a;
            }
            //---------------------------------------------------------------цветущая-----------------------------------------
            if (type == 'цветущая')
            {
              my_log(key);
                  my_log('Цветущих требуется: ' + cvet.need);
                  my_log('Цветущих на складе: ' + cvet.stock);
                  my_log('Деток на складе: ' + detki.stock);
                  my_log('Листьев на складе: ' + list.stock);
                  my_log('');
              var divTime = (date.getTime()-nowtime.getTime())/1000/60/60/24; //разница между к какому числу и сегодня в днях
              //высадить до
              var ddt1 = date.getTime()-(90*1000*60*60*24); //нужное время-1 месяц1, в мс
              var ddt2 = date.getTime()-(180*1000*60*60*24); //нужное время-2 месяца, в мс
              var ddtDate1 = new Date(ddt1); //дата "-1 месяц1 от нужной"
              var ddtDate2 = new Date(ddt2); //дата "-2 месяца от нужной"
              var detkivirosli = new Date(nowtime.getTime()+(90*1000*60*60*24)); //к какому числу вырастут детки, от сегодняшнего
              var fialkivirosli = new Date(nowtime.getTime()+(180*1000*60*60*24)); //к какому числу вырастут листья, от сегодняшнего
              
              var detkiperenesti = (detkivirosli.getTime()-date.getTime())/1000/60/60/24; //на сколько дней перенести дату чтоб выросли детки
              var listperenesti = (fialkivirosli.getTime()-date.getTime())/1000/60/60/24; //на сколько дней перенести дату чтоб выросли детки
              //my_log(ddtDate2.toISOString());

              if (value.need > value.stock) { //начинаем проверять детки и листы
                  
                  var diff = value.need - value.stock; // здесь сколько не хватает
                  

                  if ((divTime>=90)) { //если деткам хватит времени вырасти
                    diff = diff - detki.stock;
                    if (diff <= 0) {  //если деток хватило, то садить не надо
                      my_log('Планирование успешно!');
                      my_log('У вас уже есть '+cvet.stock+' цветущих. Дополнительно листочки садить не нужно.');
                      my_log((cvet.need-cvet.stock)+' шт. деток со склада вырастут к '+vivoddate(detkivirosli)+' Зарезервируйте их.');
                      my_log('После того как детки вырастут, до указанной вами даты останется '+(Math.floor(divTime-90))+' дней(дня).');
                      if ((cvet.need-detki.stock<=0)&&(cvet.stock>0)) {
                        my_log('Вы также можете не откладывать цветущие фиалки сейчас на складе, а просто высадить все '+detki.stock+' шт. деток.')
                      }
                      if ((divTime>=180) && (cvet.need-list.stock<=0)) {
                        my_log('');
                        my_log('Однако у вас еще очень много времени в запасе! На складе хватает листочков, чтобы вырастить все '+(cvet.need)+' фиалки(фиалок) и они зацвели.');
                      }
                      
                    }
                    else {
                      if (divTime>=180) {  // проверяем есть и хватит ли листков и времени вырасти
                        diff = diff - list.stock;
                          if (diff <=0) {         //листков на складе хватило
                            my_log('Планирование успешно!');
                            if (cvet.stock>0) {
                              my_log('У вас уже есть '+cvet.stock+' шт. цветущих.');
                            }
                            if (detki.stock>0) {
                              my_log((detki.stock)+' шт. деток вырастут к '+vivoddate(detkivirosli));
                              my_log('Вырастите и оставьте их. + посадите '+(cvet.need-cvet.stock-detki.stock)+' шт. листков  до ' + vivoddate(ddtDate2)+' чтоб они успели вырасти.');
                              if ((cvet.need-list.stock>0)&&(cvet.need-list.stock-cvet.stock<=0)) {
                                my_log('');
                                my_log('Либо посадите только листья, с учетом '+cvet.stock+' цветущих. Тогда вам нужно посадить ' + (cvet.need-cvet.stock) + ' шт. листьев до ' + vivoddate(ddtDate2));
                              }
                              if (cvet.need-list.stock<=0) {
                                my_log('');
                                my_log('Либо посадите только листья, их хватает на складе: вам нужно посадить ' + (cvet.need) + ' шт. листьев до ' + vivoddate(ddtDate2));
                              }
                            }
                            else {
                              my_log('Вам надо отложить ' + (cvet.need-cvet.stock) + ' шт. листков и посадить до ' + vivoddate(ddtDate2));
                            }
                             
                          }
                          else { //листков на складе не хватило
                            my_log('Не получится :/ Листков на складе не хватило!')
                            my_log('Нужно где-то найти и посадить ' + (cvet.need-cvet.stock-detki.stock-list.stock) + ' еще листков до ' + vivoddate(ddtDate2));
                          }
                      }
                      else { //если листков нет (не хватает) на складе, либо они не вырастут по времени
                        my_log('Не получится :/');
                        my_log('Цветущих и деток не хватает, а новые не успеют вырасти.');
                      }
                    }
                  } //деткам не хватит времени, и хз хватает ли их чтоб выполнить план
                  else {
                    my_log('Не получится :/');
                    if (cvet.need-cvet.stock-detki.stock<=0){
                      my_log('Но если перенести дату на '+Math.ceil(detkiperenesti)+' дня/дней, детки вырастут к '+vivoddate(detkivirosli)+' Их достаточно на складе.');
                    }
                    else {
                      if (cvet.need-cvet.stock-list.stock<=0){
                        my_log('Но если перенести дату на '+Math.ceil(listperenesti)+' дня/дней, можно сегодня посадить листья и они вырастут к '+vivoddate(detkivirosli)+' Их достаточно на складе.');
                      }
                      else {
                        //my_log('Цветущих фиалок не хватает, а новые не успеют вырасти.'+detkivirosli);
                        my_log('На складе не хватает фиалок, деток или листьев и времени для их выращивания.');
                      }
                    }
                  }
              }
              else { 
                my_log('Планирование успешно!');
                my_log('Вам хватает цветущих фиалок на складе');
                if ((cvet.need-list.stock<=0)&&(divTime>=180)) {
                  my_log('');
                  my_log('Количество листьев и указанная дата позволяют посадить и вырастить столько фиалок, сколько требуется.');
                  my_log('Рациональней будет именно посадить листья, а цветущие фиалки, имеющиеся на складе сейчас - продать, т.к. в конечном итоге будет получено больше прибыли:)');
                  my_log('Фиалки из листочков вырастут и зацветут: '+vivoddate(fialkivirosli));
                }
                else {
                  if ((cvet.need-detki.stock<=0)&&(divTime>=90)) {
                    my_log('');
                    my_log('Но также к указанной вами дате вырастет и необходимое количество деток на складе.');
                    my_log('Рациональней будет дождаться пока вырастут детки, а цветущие фиалки, имеющиеся на складе сейчас - продать.');
                    my_log('Детки вырастут и зацветут: '+vivoddate(detkivirosli));
                  }
                }
              }
              my_log('---------------------------------------------------------------------------------------------');
            }
            //---------------------------------------------------------------детки----------------------------------------- 
            else if (type == 'детки')
            {
              if (value.need > value.stock) { //начинаем проверять детки и листы
                  my_log(key);
                  my_log('Деток требуется: ' + detki.need);
                  my_log('Деток на складе: ' + detki.stock);
                  my_log('Листьев на складе: ' + list.stock);

                  var diff = value.need - value.stock; // здесь сколько не хватает
                  var divTime = (date.getTime()-nowtime.getTime())/1000/60/60/24;

                  //высадить до
                  var ddt = date.getTime()-(90*1000*60*60*24);
                  var ddtDate2 = new Date(ddt);
                  vivoddate(ddtDate2);

                  if ((divTime>=90)) { //если листьям хватит времени вырасти до деток
                    diff = diff - list.stock;
                    my_log(diff); 
                    if (diff <= 0) {  //если листьев хватило, то искать не надо
                      my_log('Планирование успешно!');
                      my_log('Листьев на складе хватает, ' + (detki.need-detki.stock) + 'шт. листьев со склада успеют вырасти. Зарезервируйте и посадите их до ' + ddtDate2);
                    }
                    else {
                            my_log('Не получится :/ Листков на складе не хватило!')
                            my_log('Нужно где-то найти и посадить' + (detki.need-detki.stock-list.stock) + ' листков до ' + ddtDate2);
                    }
                  }
                  else {
                    my_log('Не получится :/');
                    my_log('Деток на складе не хватает, а новые не успеют вырасти');
                  }
              }
              else { 
                my_log(key);
                my_log('Планирование успешно!');
                my_log('Вам хватает деток на складе');
                my_log('Деток требуется: '+ (detki.need));
                my_log('Деток на складе: '+ (detki.stock));
              }
            }
            //---------------------------------------------------------------лист-----------------------------------------
            if (type == 'лист')
            {
              if (value.need > value.stock) { //начинаем проверять детки и листы
                  my_log(key);
                  my_log('Листков требуется: ' + list.need);
                  my_log('Листков на складе: ' + list.stock);
                  my_log('Цветущих на складе: ' + cvet.stock);
                  my_log('Деток на складе: ' + detki.stock);

                  var diff = value.need - value.stock; // здесь сколько не хватает
                  var divTime = (date.getTime()-nowtime.getTime())/1000/60/60/24;

                  //высадить до
                  var ddt = date.getTime()-(90*1000*60*60*24);
                  var ddtDate2 = new Date(ddt);

                  if ((diff-(cvet.stock*20))<=0) { // листьев и цветущих хватит.
                    if (list.stock>0) {
                      my_log('Планирование успешно!');
                      my_log('Сейчас есть '+list.stock+' листков'); 
                      my_log('Отломите еще '+diff+' листков от '+ (cvet.stock-Math.floor((cvet.stock*20-diff)/20))+' цветущих'); 
                    }
                    else {
                      my_log('Планирование успешно!');
                      my_log('Просто отломите '+diff+' листков от '+ (cvet.stock-Math.floor((cvet.stock*20-diff)/20))+' цветущих'); 
                    }
                  }
                  else { // если листьев и цветущих не хватило проверяем
                    if (divTime>=90) { //хватает ли времени
                      if ((diff-cvet.stock*20-detki.stock*20)<=0) { //если кол-ва на складе хватает, а не успеваем только по дате
                        if (list.stock>0) {
                          my_log('Сейчас есть '+list.stock+' листков');
                          if (cvet.stock>0) {
                            my_log('Сейчас есть '+cvet.stock+' цветущих фиалок, из них получится '+cvet.stock*20+' листков');
                          }
                        }
                        my_log('Отломите еще '+diff+' листков от '+ (cvet.stock-Math.floor((cvet.stock*20-diff)/20))+' цветущих'); 
                      }
                    }
                    else {
                      if ((detki.stock>0) && ((diff-cvet.stock*20-detki.stock*20)<=0)){
                        my_log('Не получится :/');
                        my_log('Листков и цветов на складе не хватает, чтобы получить листья к указанной дате, а '+(Math.ceil((diff-cvet.stock*20)/20))+' шт. деток не успеют вырасти. Предлагаем сдвинуть дату на ... дней');
                        my_log('Из них нужно оторвать '+(diff-cvet.stock*20)+' листков');
                      }
                      else {
                        my_log('Не получится :/');
                        my_log('Листков, цветов и деток на складе не хватает');
                      }
                      }
                  }
              }
              else { 
                my_log(key);
                my_log('Планирование успешно!');
                my_log('Вам хватает листков на складе');
                my_log('Листков требуется: '+ (list.need));
                my_log('Листков на складе: '+ (list.stock));
              }
            }
          }

        });
        alert(_g_string);
        var json = JSON.stringify(_g_data);
        $('#send_form input').val(json);
        $('#send_form').submit();
      });

      
 
  } );

  function my_log(arg) {
    _g_string += arg + '\n';
  }
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
                    echo "<div name='{$ska_name}' class='_diff' value='{$typevalue['diff']}'>Diff: {$typevalue['diff']}</div>";
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