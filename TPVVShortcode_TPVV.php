<?php

	include_once('TPVVShortcode_ShortCodeLoader.php');
	include_once('TPVVShortcode_Plugin.php');
	 
	class TPVVShortcode_TPVV extends TPVVShortcode_ShortCodeLoader {
    
    /**
     * @param  $atts shortcode inputs
     * @return string shortcode content
     */
    public function handleShortcode($atts) {

    	if(!isset($atts['concepto']) || trim($atts['concepto']) == '') return __('TPVV: falta el concepto', 'tpvv-shortcode-plugin');
    	if(!isset($atts['importe']) || trim($atts['importe'] == '')) return __('TPVV: falta el importe', 'tpvv-shortcode-plugin');
    	if(!is_numeric($atts['importe']) || doubleval($atts['importe']) <= 0) return __('TPVV: el importe es incorrecta', 'tpvv-shortcode-plugin');

    	$concepto = trim($atts['concepto']);
    	$importe = doubleval($atts['importe']);

    	$id = md5(time().rand(0, 9999));

    	$opciones = new TPVVShortcode_Plugin();

    	ob_start();
    	?>

    	<div>

    		<h3><?=__('Pagar ahora con tarjeta de crédito o débito', 'tpvv-shortcode-plugin')?></h3>
    		<p>
    			<?=__('Concepto', 'tpvv-shortcode-plugin')?>: <?=$concepto?><br/>
    			<?=__('Importe', 'tpvv-shortcode-plugin')?>: <strong><?=number_format($importe, 2, '.', '')?><?=$opciones->getOption('TPVV_simbolo_divisa')?> <?=__('I.V.A. incluido', 'tpvv-shortcode-plugin')?></strong>
    		</p>

	    	<form class="tpvv" name="TPVV_<?=$id?>" method="POST" action="http://<?=$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]?>" onsubmit="return validar_<?=$id?>(this)">
	    		<input type="hidden" name="tpvv-shortcode" value="<?=$id?>">
	    		<label for="nombre"><?=__('Nombre', 'tpvv-shortcode-plugin')?>: <input type="text" name="nombre" id="nombre"></label>
	    		<label for="apellidos"><?=__('Apellidos', 'tpvv-shortcode-plugin')?>: <input type="text" name="apellidos" id="apellidos"></label>
	    		<label for="telefono"><?=__('Teléfono', 'tpvv-shortcode-plugin')?>: <input type="text" name="telefono" id="telefono"></label>
	    		<label for="email"><?=__('Email', 'tpvv-shortcode-plugin')?>: <input type="text" name="email" id="email"></label>
	    		<div class="g-recaptcha" id="recaptcha_<?=$id?>"></div>
	    		<label for="condiciones" class="condiciones"><input type="checkbox" name="condiciones" id="condiciones"> <?=__('He leido, comprendido y acepto las condiciones de compra y política de privacidad de este sítio web', 'tpvv-shortcode-plugin')?> - <a href="#"><?=__('Ver condiciones de venta y política de privacidad', 'tpvv-shortcode-plugin')?></a></label>
	    		<div><input type="submit" name="pagar" value="<?=__('Pagar ahora', 'tpvv-shortcode-plugin')?>"> <img class="tarjetas" src="<?=plugins_url('img/visa_mastercard.jpg', __FILE__)?>"></div>
	    		<input type="hidden" name="concepto" value="<?=$concepto?>">
	    		<input type="hidden" name="importe" value="<?=$importe?>">
	    		<input type="hidden" name="token" value="<?=md5($concepto.$importe.$opciones->getOption('TPVV_clave'))?>">
	    	</form>

	    	<script type="text/javascript">

	    		// TODO: replantear el captcha para poder tener más de un shorcode en la misma página

	    		var recaptchaCallback = function(){
		        grecaptcha.render('recaptcha_<?=$id?>', {
		          'sitekey' : '<?=$opciones->getOption('TPVV_recaptcha_public')?>',
		          'callback': verifyRecaptchaCallback
		        });
		      };

		      var recaptcha_<?=$id?> = false;
		      var verifyRecaptchaCallback = function(response, r2){
		      	recaptcha_<?=$id?> = true;
		      };
	    		
	    		if(!String.prototype.trim){
					  (function() {
					    var rtrim = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;
					    String.prototype.trim = function() {
					      return this.replace(rtrim, '');
					    };
					  })();
					}

					if(typeof window.validar_email === 'undefined'){
				    window.validar_email = function(email) {
				      return (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/.test(email));
				    }
					};

	    		function validar_<?=$id?>(f){
	    			if(f.nombre.value.trim() == ''){ alert('<?=__('Tu nombre no puede estar en blanco', 'tpvv-shortcode-plugin')?>'); return false; }
	    			if(f.apellidos.value.trim() == ''){ alert('<?=__('Tus apellidos no pueden estar en blanco', 'tpvv-shortcode-plugin')?>'); return false; }
	    			if(f.telefono.value.trim() == ''){ alert('<?=__('Tu teléfono no puede estar en blanco', 'tpvv-shortcode-plugin')?>'); return false; }
	    			if(f.email.value.trim() == ''){ alert('<?=__('Tu email no puede estar en blanco', 'tpvv-shortcode-plugin')?>'); return false; }
	    			if(!validar_email(f.email.value.trim())){ alert('<?=__('Tu email no es válido', 'tpvv-shortcode-plugin')?>'); return false; }
	    			if(!recaptcha_<?=$id?>){ alert('<?=__('Debes verificar que no eres un robot', 'tpvv-shortcode-plugin')?>'); return false; }
	    			if(!f.condiciones.checked){ alert('<?=__('Debes leer, comprender y capetar las condiciones de compra y política de privacidad de este sítio web', 'tpvv-shortcode-plugin')?>'); return false; }
	    			return true;
	    		}

	    	</script>
	    	
	    <div>
    	<?php

      return ob_get_clean();

    }

	}