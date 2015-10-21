# CFDI Wrapper

This project is a neat wrapper for CFDI XSD files, this library allows you to
manipulate CFDI attributes in a very simple and intuitive way without dealing
with XML, XSD or XPATH at all!

## Instalation

Add the dependency to your `composer.json`:

```
{
    "repositories": [
        {
            "type": "vcs",
            "url": "http://git.microbit.com/julio/cfdi-wrapper.git"
        },
    ],
    "require": {
        "zzantares/cfdi-wrapper": "dev-master",
    }
}
```

Now just run `composer install` or `composer update` depending on the stage of
your project

## Usage

In order to use the CFDI Wrapper advantages create an instance of the `Cfdi`
class by passing the XML content of the CFDI to inspect:

```php
<?php

require 'vendor/autoload.php';

use ZzAntares\CfdiWrapper\Cfdi;

$xmlContent = file_get_contents('/path/to/cfdi.xml');
$cfdi = new Cfdi($xmlContent);

echo $cfdi->emisor->rfc;  // 'some123456rfc'
```

It is also possible to create an instance from a path file, like this:

```php
<?php

require 'vendor/autoload.php';

use ZzAntares\CfdiWrapper\Cfdi;

$cfdi = new Cfdi('/path/to/cfdi.xml');
```


## Available attributes

```php
'version',
'serie',
'folio',
'fecha',
'subTotal',
'subtotal',  // Alias of subTotal
'total',
'certificado' ,
'noCertificado',
'condicionesDePago',
'descuento',
'motivoDescuento',
'TipoCambio',
'tipoCambio',  // Alias of TipoCambio
'Moneda',
'moneda',  // Alias of Moneda
'metodoDePago',
'sello' ,
'tipoDeComprobante',
'formaDePago',
'LugarExpedicion',
'lugarExpedicion',  // Alias of LugarExpedcion
'NumCtaPago',
'numCtaPago',  // Alias of numCtaPago
```

Also you can access to the nested objects:

```php
'emisor',
'receptor',
'conceptos',
'impuestos',
'impuestosLocales',
'timbreFiscalDigital',
'timbre',  // Alias of timbreFiscalDigital
```

Every one of this nested objects has it's own attributes, you can get them very
intuitively by reading the CFDI XSD file, here are some common examples:

```php
<?php

$cfdi->folio;
$cfdi->fecha;
$cfdi->emisor->rfc;
$cfdi->emisor->nombre;
$cfdi->emisor->domicilio->calle;
$cfdi->receptor->rfc;
$cfdi->receptor->nombre;
$cfdi->receptor->domicilio->calle;
$cfdi->timbre->uuid;
$cfdi->conceptos;  // Is an array
$cfdi->conceptos[0]->descripcion;
```

# TODO

- Integrate helpers functions to get common values easier like IVA.
