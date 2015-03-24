# wp-redsys-tpvv-shortcode

Plugin para WordPress para insertar un formulario de pago [RedSys](http://www.redsys.es) sobre una entrada o página mediente un shortcode.
Basado en el código de ¿Michael Simpson? [http://plugin.michael-simpson.com/](http://plugin.michael-simpson.com/)

## Uso

Shortcode tpvv con dos parámetros, ambos obligatorios

- concepto: Descripción de la operación que aparecerá en el formulario de compra y en la pantalla de confirmación de la misma.
- importe: Importe total de la operación a cobrar, debe ser un número de hasta dos decimales sin separador de miles y utilizando el punto (.) como separador de decimales

	[tpvv concepto="" importe=""]

### Ejemplo

	[tpvv concepto="Inscripción a la carrera popular" importe="10.00"]

## Configuración

Una vez instalado, hay que realizar la configuración del mismo mediante el panel de administración de WordPress del plugin. En él introduciremos los siguientes valores:

- *URL TPV Virtual*: URL del sistema de pagos RedSys, proporcionada por la entidad bancaria
- *Clave TPV Virtual*: Clave proporcionada por la entidad bancaria
- *Símbolo de divisa*: Símbolo para adjuntar al importe de la operación en las vistas de formulario y justificante de pago
- *Código de divisa*: Número de hasta 4 dígitos que identifica la divisa en la que se realizará la operación, obtenida de la documentación del sistema RedSys
- *Nombre de comercio*: Nombre que aparecerá en el formulario de pago de la entidad bancaria
- *Código de comercio*: Identificador del comercio proporcionado por la entidad bancaria
- *Número de terminal*: Proporcionado por la entidad bancaria
- *URL condiciones y privacidad*: URL de las condiciones de venta y políticas de privacidad de nuestro sitio web
- *reCAPTCHA: Clave del sitio*: Clave pública de [reCAPTCHA](https://www.google.com/recaptcha) para evitar robots
- *reCAPTCHA: Clave secreta*: Clave privada de [reCAPTCHA](https://www.google.com/recaptcha) para evitar robots
- *Mandrill: API Key*: Clave API de [Mandrill](https://mandrillapp.com/) para el envío de correos de confirmación de pago
- *Mandrill: Email del remitente*: Remitente de los correos enviados desde [Mandrill](https://mandrillapp.com/) y el cual recibirá una copia de los correos de confirmación de pago