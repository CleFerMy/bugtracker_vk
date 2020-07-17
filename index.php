<?
header("Content-type:application/json");
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

if(!empty($_GET['id'])){
  $get = (int) preg_replace("/[^0-9]/", '', $_GET['id']);
  require_once 'simple_html_dom.php';

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, auth_url);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_USERAGENT, browser);
  curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.json');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $login_page = curl_exec($ch);
  curl_close($ch);

  //авторизация
  $html = str_get_html($login_page);
  $login_url = $html->find("form",0)->action;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $login_url);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_USERAGENT, browser);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, ["email"=>email, "pass"=>pass]);
  curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.json');
  curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.json');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $page = curl_exec($ch);
  curl_close($ch);

  //главная страница пользователя в трекере
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "https://vk.com/bugtracker");
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_USERAGENT,browser);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, ["act"=>'reporter', "id"=>$get]);
  curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.json');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $error = array('code' => curl_errno($ch), 'description' => curl_error($ch));
  if($error['code'] > 0){
    $return = json_encode(array('error' => array('code' => $error['code'], 'description' => $error['description'])), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
  }else{
    //страница с продуктами пользователя в трекере
    $ch2 = curl_init();
    curl_setopt($ch2, CURLOPT_URL, "https://vk.com/bugtracker");
    curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch2, CURLOPT_USERAGENT,browser);
    curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch2, CURLOPT_POSTFIELDS, ["act"=>'reporter_products', "id"=>$get]);
    curl_setopt($ch2, CURLOPT_COOKIEFILE, 'cookie.json');
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);

    $page = curl_exec($ch);   //получаем 1 часть
    $page2 = curl_exec($ch2); //получаем 2 часть

    curl_close($ch);
    curl_close($ch2);

    $page = str_get_html(iconv('windows-1251','utf-8',$page))->find('div#page_wrap div',0)->find('div.scroll_fix div#page_layout',0)->find('div#page_body div#wrap3',0)->find('div#wrap2 div#wrap1',0)->find('div#content',0);
    $page2 = str_get_html(iconv('windows-1251','utf-8',$page2))->find('div#page_wrap div',0)->find('div.scroll_fix div#page_layout',0)->find('div#page_body div#wrap3',0)->find('div#wrap2 div#wrap1',0)->find('div#content',0);

    $pagea = trim(preg_replace('|\s+|', ' ', trim($page->find('div.message_page',0)->plaintext)));
    if(mb_strlen($pagea,'UTF-8') > 5){
      $return = json_encode(array('error' => array('code' => 1001, 'description' => 'Пользователь не найден')), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }else{
      $pagea = trim(preg_replace('|\s+|', ' ', trim($page->find('div.wide_column_right',0)->find('div.wide_column_wrap div#wide_column',0)->find('div.page_block div.bt_reporter_block',0)->find('div.BugtrackerReporterProfile div.BugtrackerReporterProfile__in',0)->find('div.BugtrackerReporterProfile__content',0)->plaintext)));
      if(mb_strlen($pagea,'UTF-8') > 5){
        $status = mb_strtolower($pagea);
        switch ($status) {
          case 'не участвует в программе vk testers':
            $name = explode(' ',trim(preg_replace('|\s+|', ' ', trim($page->find('div.wide_column_right',0)->find('div.wide_column_wrap div#wide_column',0)->find('div.page_block .page_block_h2',0)->find('div.page_block_header div.page_block_header_inner',0)->find('div.ui_crumb',0)->plaintext))));
            $return = json_encode(array('response' => array('id' => $get, 'first_name' => $name[0], 'last_name' => $name[1], 'status' => 0, 'description' => 'Не участвует в программе VK  Testers')), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;
          case 'подал заявку на вступление в программу': case 'подала заявку на вступление в программу':
            $name = explode(' ',trim(preg_replace('|\s+|', ' ', trim($page->find('div.wide_column_right',0)->find('div.wide_column_wrap div#wide_column',0)->find('div.page_block .page_block_h2',0)->find('div.page_block_header div.page_block_header_inner',0)->find('div.ui_crumb',0)->plaintext))));
            $descloc = ($status=='подала заявку на вступление в программу')? 'Подала заявку на вступление в программу' : 'Подал заявку на вступление в программу';
            $return = json_encode(array('response' => array('id' => $get, 'first_name' => $name[0], 'last_name' => $name[1],  'status' => 1, 'description' => $descloc)), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;
          case 'исключён из программы vk testers': case 'исключена из программы vk testers':
            $name = explode(' ',trim(preg_replace('|\s+|', ' ', trim($page->find('div.wide_column_right',0)->find('div.wide_column_wrap div#wide_column',0)->find('div.page_block .page_block_h2',0)->find('div.page_block_header div.page_block_header_inner',0)->find('div.ui_crumb',0)->plaintext))));
            $descloc = ($status=='исключёна из программы vk testers')? 'Исключена из программы VK Testers' : 'Исключён из программы VK Testers';
            $return = json_encode(array('response' => array('id' => $get, 'first_name' => $name[0], 'last_name' => $name[1],  'status' => 2, 'description' => $descloc)), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;
          case 'во вступлении в программу отказано':
            $name = explode(' ',trim(preg_replace('|\s+|', ' ', trim($page->find('div.wide_column_right',0)->find('div.wide_column_wrap div#wide_column',0)->find('div.page_block .page_block_h2',0)->find('div.page_block_header div.page_block_header_inner',0)->find('div.ui_crumb',0)->plaintext))));
            $return = json_encode(array('response' => array('id' => $get, 'first_name' => $name[0], 'last_name' => $name[1],  'status' => 3, 'description' => 'Во вступлении в программу отказано')), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;
          case 'вышел из программы vk testers': case 'вышла из программы vk testers':
            $name = explode(' ',trim(preg_replace('|\s+|', ' ', trim($page->find('div.wide_column_right',0)->find('div.wide_column_wrap div#wide_column',0)->find('div.page_block .page_block_h2',0)->find('div.page_block_header div.page_block_header_inner',0)->find('div.ui_crumb',0)->plaintext))));
            $descloc = ($status=='вышла из программы vk testers')? 'Вышла из программы VK Testers' : 'Вышел из программы VK Testers';
            $return = json_encode(array('response' => array('id' => $get, 'first_name' => $name[0], 'last_name' => $name[1],  'status' => 4, 'description' => $descloc)), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;
            case 'приглашён в программу vk testers': case 'приглашена в программу vk testers':
            $name = explode(' ',trim(preg_replace('|\s+|', ' ', trim($page->find('div.wide_column_right',0)->find('div.wide_column_wrap div#wide_column',0)->find('div.page_block .page_block_h2',0)->find('div.page_block_header div.page_block_header_inner',0)->find('div.ui_crumb',0)->plaintext))));
            $descloc = ($status=='приглашена в программу vk testers')? 'Приглашена в программу VK Testers' : 'Приглашён в программу VK Testers';
            $return = json_encode(array('response' => array('id' => $get, 'first_name' => $name[0], 'last_name' => $name[1],  'status' => 6, 'description' => $descloc)), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;
          default:
            $name = explode(' ',trim(preg_replace('|\s+|', ' ', trim($page->find('div.wide_column_right',0)->find('div.wide_column_wrap div#wide_column',0)->find('div.page_block .page_block_h2',0)->find('div.page_block_header div.page_block_header_inner',0)->find('div.ui_crumb',0)->plaintext))));
            if($status == '0 отчётов' || strpos($status, 'рейтинге')){
              $tickets = (int) preg_replace("/[^0-9]/", '', explode(' ',$status)[count(explode(' ',$status))-2]);
              $productcount = (int) preg_replace("/[^0-9]/", '', trim(preg_replace('|\s+|', ' ', trim($page->find('div.wide_column_right',0)->find('div.wide_column_wrap div#wide_column',0)->find('div.page_block .page_block_h2',1)->plaintext))));
              if($productcount > 0){
                $pagea2 = $page2->find('div.wide_column_right',0)->find('div.wide_column_wrap div#wide_column',0)->find('div.page_block div.bt_products_list_compact',0)->find('div.bt_reporter_product');
                $product = array();
                for($x=0; $x<count($pagea2); $x++){
                  $id = $page2->find('div.wide_column_right',0)->find('div.wide_column_wrap div#wide_column',0)->find('div.page_block div.bt_products_list_compact',0)->find('div.bt_reporter_product',$x)->attr['id'];
                  $pname = trim(preg_replace('|\s+|', ' ', trim($page2->find('div.wide_column_right',0)->find('div.wide_column_wrap div#wide_column',0)->find('div.page_block div.bt_products_list_compact',0)->find('div.bt_reporter_product',$x)->find('div.bt_reporter_product_title',0)->plaintext)));
                  $report = (int) preg_replace("/[^0-9]/", '', trim(preg_replace('|\s+|', ' ', trim($page2->find('div.wide_column_right',0)->find('div.wide_column_wrap div#wide_column',0)->find('div.page_block div.bt_products_list_compact',0)->find('div.bt_reporter_product',$x)->find('.bt_reporter_product_nreports',0)->plaintext))));
                  $id = (int) preg_replace("/[^0-9]/", '', explode('_',$id)[2]);
                  if($id > 0) $product[$x]['id'] = $id;
                    $product[$x]['name'] = $pname;
                    $product[$x]['reports'] = $report;
                }
                $products = array('count' => $productcount, 'items' => $product);
              }else{
                $products = array('count' => 0);
              }
              $return = json_encode(array('response' => array('id' => $get, 'first_name' => $name[0], 'last_name' => $name[1],  'status' => 5, 'description' => 'Участвует в программе VK Testers', 'reports' => $tickets, 'products' => $products)), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }else{
              $return = json_encode(array('error' => array('code' => 1002, 'description' => 'Не удалось определить статус пользователя')), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
            break;
        }
      }else{
        $return = json_encode(array('error' => array('code' => 1003, 'description' => 'Не удалось загрузить информацию')), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
      }
    }
  }
}else{
  $return = json_encode(array('error' => array('code' => 1004, 'description' => 'Не удалось получить ID пользователя')), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
echo $return;
?>
