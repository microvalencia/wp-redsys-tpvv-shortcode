<?php

include_once('TPVVShortcode_LifeCycle.php');

class TPVVShortcode_Plugin extends TPVVShortcode_LifeCycle {

    /**
     * See: http://plugin.michael-simpson.com/?page_id=31
     * @return array of option meta data.
     */
    public function getOptionMetaData() {
      //  http://plugin.michael-simpson.com/?page_id=31
      return array(
        //'_version' => array('Installed Version'), // Leave this one commented-out. Uncomment to test upgrades.
        'TPVV_url' => array(__('URL TPV Virtual', 'tpvv-shortcode-plugin')),
        'TPVV_clave' => array(__('Clave TPV Virtual', 'tpvv-shortcode-plugin')),
        'TPVV_simbolo_divisa' => array(__('Símbolo de divisa', 'tpvv-shortcode-plugin')),
        'TPVV_Ds_Merchant_Currency' => array(__('Código de divisa', 'tpvv-shortcode-plugin')),
        'TPVV_Ds_Merchant_MerchantName' => array(__('Nombre de comercio', 'tpvv-shortcode-plugin')),
        'TPVV_Ds_Merchant_MerchantCode' => array(__('Código de comercio', 'tpvv-shortcode-plugin')),
        'TPVV_Ds_Merchant_Terminal' => array(__('Número de terminal', 'tpvv-shortcode-plugin')),
        'TPVV_url_condiciones' => array(__('URL condiciones y privacidad', 'tpvv-shortcode-plugin')),
        'TPVV_recaptcha_public' => array(__('reCAPTCHA: Clave del sitio', 'tpvv-shortcode-plugin')),
        'TPVV_recaptcha_secret' => array(__('reCAPTCHA: Clave secreta', 'tpvv-shortcode-plugin')),
        'TPVV_mandrill_apikey' => array(__('Mandrill: API Key', 'tpvv-shortcode-plugin')),
        'TPVV_mandrill_remite' => array(__('Mandrill: Email remitente', 'tpvv-shortcode-plugin')),
      );
    }

//    protected function getOptionValueI18nString($optionValue) {
//        $i18nValue = parent::getOptionValueI18nString($optionValue);
//        return $i18nValue;
//    }

    protected function initOptions() {
      $options = $this->getOptionMetaData();
      if(!empty($options)) {
        foreach($options as $key => $arr){
          if (is_array($arr) && count($arr > 1)){
            $this->addOption($key, $arr[1]);
          }
        }
      }
    }

    public function getPluginDisplayName() {
      return 'TPVV shortcode';
    }

    protected function getMainPluginFileName() {
      return 'tpvv-shortcode.php';
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Called by install() to create any database tables if needed.
     * Best Practice:
     * (1) Prefix all table names with $wpdb->prefix
     * (2) make table names lower case only
     * @return void
     */
    protected function installDatabaseTables() {
      global $wpdb;
      $tabla = $this->prefixTableName('operaciones_redsys');
      $sql = "CREATE TABLE IF NOT EXISTS `$tabla` (";
      $sql .= "`id` bigint(20) NOT NULL AUTO_INCREMENT, ";
      $sql .= "`fecha` datetime NOT NULL, ";
      $sql .= "`nombre` varchar(255) COLLATE utf8_unicode_ci NOT NULL, ";
      $sql .= "`apellidos` varchar(255) COLLATE utf8_unicode_ci NOT NULL, ";
      $sql .= "`telefono` varchar(255) COLLATE utf8_unicode_ci NOT NULL, ";
      $sql .= "`email` varchar(255) COLLATE utf8_unicode_ci NOT NULL, ";
      $sql .= "`concepto` varchar(255) COLLATE utf8_unicode_ci NOT NULL, ";
      $sql .= "`precio` double NOT NULL, ";
      $sql .= "`url_referer` varchar(255) COLLATE utf8_unicode_ci NOT NULL, ";
      $sql .= "`fecha_respuesta` datetime DEFAULT NULL, ";
      $sql .= "`respuesta` varchar(255) COLLATE utf8_unicode_ci NOT NULL, ";
      $sql .= "`post` text COLLATE utf8_unicode_ci NOT NULL, ";
      $sql .= "PRIMARY KEY (`id`)";
      $sql .= ")";
      $wpdb->query($sql);
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Drop plugin-created tables on uninstall.
     * @return void
     */
    protected function unInstallDatabaseTables() {
      global $wpdb;
      $tabla = $this->prefixTableName('operaciones_redsys');
      $wpdb->query("drop table `$tabla`");
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=105
     * @return void
     */
    public function activate() {
      $this->installDatabaseTables();
    }

    /**
     * Perform actions when upgrading from version X to version Y
     * See: http://plugin.michael-simpson.com/?page_id=35
     * @return void
     */
    public function upgrade() {
    }

    public function addActionsAndFilters() {

      // Add options administration page
      // http://plugin.michael-simpson.com/?page_id=47
      add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));

      // Example adding a script & style just for the options administration page
      // http://plugin.michael-simpson.com/?page_id=47
      //        if (strpos($_SERVER['REQUEST_URI'], $this->getSettingsSlug()) !== false) {
      //            wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));
      //            wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
      //        }

      // Add Actions & Filters
      add_action('wp_loaded', array(&$this, 'execPasarela'));
      add_action('wp_loaded', array(&$this, 'ipnPasarela'));

      // TODO: action para mostrar un mensaje en caso de que el pago haya sido correcto o no (Ds_Merchant_UrlOK y Ds_Merchant_UrlKO)

      // Adding scripts & styles to all pages
      wp_enqueue_style('TPVV-style', plugins_url('/css/tpvv.css', __FILE__));
      wp_enqueue_script('recaptcha-script', 'https://www.google.com/recaptcha/api.js?onload=recaptchaCallback&render=explicit');

      // Register short codes
      include_once('TPVVShortcode_TPVV.php');
      $sc = new TPVVShortcode_TPVV();
      $sc->register('TPVV');

    }

    public function execPasarela(){
      if(isset($_POST['tpvv-shortcode'])){

        // verificar captcha
        include_once(plugin_dir_path(__FILE__).'ReCaptcha/ReCaptcha.php');
        include_once(plugin_dir_path(__FILE__).'ReCaptcha/RequestMethod.php');
        include_once(plugin_dir_path(__FILE__).'ReCaptcha/RequestParameters.php');
        include_once(plugin_dir_path(__FILE__).'ReCaptcha/Response.php');
        include_once(plugin_dir_path(__FILE__).'ReCaptcha/RequestMethod/Post.php');
        include_once(plugin_dir_path(__FILE__).'ReCaptcha/RequestMethod/Socket.php');
        include_once(plugin_dir_path(__FILE__).'ReCaptcha/RequestMethod/SocketPost.php');

        $recaptcha = new \ReCaptcha\ReCaptcha($this->getOption('TPVV_recaptcha_secret'));
        $res = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
        if(!$res->isSuccess()){
          echo 'captcha error';
          exit();
        }

        // verificar token
        if($_POST['token'] != md5($_POST['concepto'].$_POST['importe'].$this->getOption('TPVV_clave'))){
          echo 'token error';
          exit(); 
        }
        
        global $wpdb;
        $tabla = $this->prefixTableName('operaciones_redsys');

        $sql = "insert into $tabla (fecha, nombre, apellidos, telefono, email, concepto, precio, url_referer) values (now(), %s, %s, %s, %s, %s, %s, %s)";
        $sql = $wpdb->prepare($sql, trim($_POST['nombre']), trim($_POST['apellidos']), trim($_POST['telefono']), trim($_POST['email']), trim($_POST['concepto']), trim($_POST['importe']), $_SERVER['HTTP_REFERER']);
        $wpdb->query($sql);

        $orden = str_pad($wpdb->insert_id, 12, '0', STR_PAD_LEFT);

        $url_retorno = $_SERVER['HTTP_REFERER'];
        if(strpos($url_retorno, '?') === false) $url_retorno .= '?';
        else $url_retorno .= '&';

        $prefirma  = number_format($_POST['importe'], 2, '', ''); // Ds_Merchant_Amount
        $prefirma .= $orden; // Ds_Merchant_Order
        $prefirma .= $this->getOption('TPVV_Ds_Merchant_MerchantCode'); // Ds_Merchant_MerchantCode
        $prefirma .= $this->getOption('TPVV_Ds_Merchant_Currency'); // Ds_Merchant_Currency
        $prefirma .= '0'; // Ds_TransactionType
        $prefirma .= $url_retorno.'tpvv=ipn'; // Ds_Merchant_MerchantURL
        $prefirma .= $this->getOption('TPVV_clave'); // Clave de firma
        $firma = strtoupper(sha1($prefirma));

        ob_start();
        ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=__('conectando con la pasarela de pago...', 'tpvv-shortcode-plugin')?></title>
<style type="text/css">
<!--
  * { font-family: Arial, Helvetica, sans-serif; color: #333; }
  div { text-align: center; }
  h2 { margin: 50px 0 0 0; font-size: 20px; }
  p { font-size: 12px; }
  a { color: #069; text-decoration: none; cursor: pointer; }
  a:hover { text-decoration: underline; }
-->
</style>
</head>
<body onload="document.pagar.submit()">
<div>

  <form method="post" name="pagar" action="<?=$this->getOption('TPVV_url')?>" >
  <h2><?=__('conectando con la pasarela de pago...', 'tpvv-shortcode-plugin')?></h2>

    <input type="hidden" name="Ds_TransactionType" value="0">
    <input type="hidden" name="Ds_Merchant_Amount" value="<?=number_format($_POST['importe'], 2, '', '')?>">
    <input type="hidden" name="Ds_Merchant_Currency" value="<?=$this->getOption('TPVV_Ds_Merchant_Currency')?>">
    <input type="hidden" name="Ds_Merchant_Order" value="<?=$orden?>">
    <input type="hidden" name="Ds_Merchant_ProductDescription" value="<?=$_POST['concepto']?>">
    <input type="hidden" name="Ds_Merchant_MerchantName" value="<?=$this->getOption('TPVV_Ds_Merchant_MerchantName')?>">
    <input type="hidden" name="Ds_Merchant_MerchantCode" value="<?=$this->getOption('TPVV_Ds_Merchant_MerchantCode')?>">
    <input type="hidden" name="Ds_Merchant_Terminal" value="<?=$this->getOption('TPVV_Ds_Merchant_Terminal')?>">
    <input type="hidden" name="Ds_Merchant_UrlOK" value="<?=$url_retorno?>tpvv=ok">
    <input type="hidden" name="Ds_Merchant_UrlKO" value="<?=$url_retorno?>tpvv=ko">
    <input type="hidden" name="Ds_Merchant_MerchantURL" value="<?=$url_retorno?>tpvv=ipn">
    <input type="hidden" name="Ds_Merchant_MerchantSignature" value="<?=$firma?>">

    <p>
      <a onclick="document.pagar.submit()"><?=__('pulsa aquí si en 10 segundos no has sido redirigido', 'tpvv-shortcode-plugin')?></a><br/>
      <input type="submit" value="<?=__('Pulsa aquí', 'tpvv-shortcode-plugin')?>" style="visibility: hidden;">
    </p>

  </form>
</div>
</body>
</html><?
    
        header('Content-Type: text/html; charset=utf-8');
        echo ob_get_clean();
        exit();

      }
    }

    public function ipnPasarela(){
      if(isset($_POST['Ds_Order'])){

        global $wpdb;
        $tabla = $this->prefixTableName('operaciones_redsys');

        $sql = "select * from $tabla where id=%d";
        $sql = $wpdb->prepare($sql, intval($_POST['Ds_Order']));
        if($r = $wpdb->get_row($sql)){

          $sql = "update $tabla set fecha_respuesta=now(), respuesta=%s, post=%s where id=$d";
          $sql = $wpdb->prepare($sql, $_POST['Ds_Response'], print_r($_POST, true), $r->id);
          $wpdb->query($sql);

          $ok = false;
          $id_operacion = $_POST['Ds_Order'];
          if($_POST['Ds_Signature'] == strtoupper(sha1($_POST['Ds_Amount'].$_POST['Ds_Order'].$_POST['Ds_MerchantCode'].$_POST['Ds_Currency'].$_POST['Ds_Response'].$this->getOption('TPVV_clave')))){ 
            if(intval($_POST['Ds_Response']) == 0){
              $ok = true;
              $log = "id_operacion: $id_operacion -- AUTORIZADA";
              $this->email_confirmacion(intval($id_operacion));
            } else $log = "id_operacion: $id_operacion -- Fallo en la operacion";
          } else {
            $log = "id_operacion: $id_operacion -- DS_SIGNATURE INCORRECTA\n";  
            $log .= "DS_SIGNATURE CALCULADO: ".strtoupper(sha1($_POST['Ds_Amount'].$_POST['Ds_Order'].$_POST['Ds_MerchantCode'].$_POST['Ds_Currency'].$_POST['Ds_Response'].$this->getOption('TPVV_clave')));
          }
          $log .= "\nPOST sermepa: ".print_r($_POST, true)."\n\n";
          
          file_put_contents("ipn_sermepa.log", $log, FILE_APPEND);
          return $ok;

        }

      }
    }

    public function email_confirmacion($orden){

      global $wpdb;
      $tabla = $this->prefixTableName('operaciones_redsys');

      $sql = "select * from $tabla where id=%d";
      $sql = $wpdb->prepare($sql, $orden);
      if($r = $wpdb->get_row($sql)){
        ob_start();
        ?>
        <h1><?=__('Confirmación de pago', 'tpvv-shortcode-plugin')?></h1>
        <p><?=__('Este correo es la confirmación de que el pago mediante la pasarela de pago ha sido completado con éxito, a continuación le mostramos los datos de la transacción.', 'tpvv-shortcode-plugin')?></p>
        <ul>
          <li><?=__('Concepto', 'tpvv-shortcode-plugin')?>: <?=$r->concepto?></li>
          <li><?=__('Importe', 'tpvv-shortcode-plugin')?>: <?=number_format($r->precio, 2, '.', '').$this->getOption('TPVV_simbolo_divisa')?></li>
          <li><?=__('Nombre', 'tpvv-shortcode-plugin')?>: <?=$r->nombre?></li>
          <li><?=__('Apellidos', 'tpvv-shortcode-plugin')?>: <?=$r->apellidos?></li>
          <li><?=__('Teléfono', 'tpvv-shortcode-plugin')?>: <?=$r->telefono?></li>
          <li><?=__('Email', 'tpvv-shortcode-plugin')?>: <?=$r->email?></li>
          <li><?=__('Id de pago', 'tpvv-shortcode-plugin')?>: <?=str_pad($r->id, 12, '0', STR_PAD_LEFT)?></li>
          <li><?=__('Fecha', 'tpvv-shortcode-plugin')?>: <?=date('j/n/Y G:i', strtotime($r->fecha))?></li>
        </ul>
        <?
        $html = ob_get_clean();
        $this->mandrill($r->email, __('Confirmación de pago', 'tpvv-shortcode-plugin'), $html);
        $this->mandrill($this->getOption('TPVV_mandrill_remite'), '['.$this->getOption('TPVV_Ds_Merchant_MerchantName').'] '.__('Confirmación de pago', 'tpvv-shortcode-plugin'), $html);
      }

    }

    private function mandrill($destino, $asunto, $html, $txt = '', $tags = array()){
    
      $post = array();
      $post['key'] = $this->getOption('TPVV_mandrill_apikey');
      $post['message'] = array();
      if($html != '') $post['message']['html'] = $html;
      if($txt != '') $post['message']['txt'] = $txt;
      else $post['message']['auto_text'] = true;
      $post['message']['subject'] = $asunto;
      $post['message']['from_email'] = $this->getOption('TPVV_mandrill_remite');
      $post['message']['from_name'] = $this->getOption('TPVV_Ds_Merchant_MerchantName');
      $post['message']['headers'] = array();
      $post['message']['headers']['Reply-To'] = $this->getOption('TPVV_mandrill_remite');
      $post['message']['to'] = array();
      $post['message']['to'][] = array('email' => $destino);
      $post['message']['track_opens'] = true;
      $post['message']['track_clicks'] = false;
      if(is_array($tags) && count($tags) > 0) $post['message']['tags'] = $tags;
      
      $ch = curl_init(); 
      curl_setopt($ch, CURLOPT_URL, 'https://mandrillapp.com/api/1.0/messages/send.json'); 
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
      curl_setopt($ch, CURLOPT_POST, TRUE); 
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));   
      $data = curl_exec($ch); 
      curl_close($ch);

      return true;
  
  }

}