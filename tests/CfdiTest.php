<?php

/**
 * This file is part of the CFDI Wrapper library.
 *
 * @copyright 2015 César Antáres <zzantares@gmail.com>
 * @license http://opensource.org/licenses/MIT The MIT License.
 */

namespace ZzAntares\CfdiWrapper;

class CfdiWrapperTest extends \PHPUnit_Framework_TestCase
{
    public function testCfdiGetAttributes()
    {
        $xmlPath = __DIR__ . '/resources/sample-cfdi.xml';
        $cfdi = new Cfdi(file_get_contents($xmlPath));

        $this->assertEquals('3.2', $cfdi->version);
        $this->assertEquals('AIN', $cfdi->serie);
        $this->assertEquals('AIN2015027', $cfdi->folio);
        $this->assertEquals('2015-02-27T13:02:13', $cfdi->fecha);
        $this->assertEquals('5.00', $cfdi->subtotal);
        $this->assertEquals('5.80', $cfdi->total);
        $this->assertStringStartsWith('MIIEmDCCA4CgAwIBAg', $cfdi->certificado);
        $this->assertEquals('00001000000301470107', $cfdi->noCertificado);
        $this->assertEquals('No aplica', $cfdi->condicionesDePago);
        $this->assertEquals('0.0', $cfdi->descuento);
        $this->assertEquals('Sin descuento', $cfdi->motivoDescuento);
        $this->assertEquals('1', $cfdi->tipoCambio);
        $this->assertEquals('MXN', $cfdi->moneda);
        $this->assertEquals('No Identificado', $cfdi->metodoDePago);
        $this->assertStringStartsWith('Gg6sfBpGbmGPKLkBUfM3ULQQ', $cfdi->sello);
        $this->assertEquals('ingreso', $cfdi->tipoDeComprobante);
        $this->assertEquals('Pago en una sola exhibición', $cfdi->formaDePago);
        $this->assertEquals('México DF', $cfdi->lugarExpedicion);
        $this->assertEquals('No aplica', $cfdi->numCtaPago);
    }

    /**
     * @expectedException ZzAntares\CfdiWrapper\Exceptions\UndefinedAttributeException
     */
    public function testCfdiThrowsExceptionIfAttributeIsNotDefined()
    {
        $xmlPath = __DIR__ . '/resources/sample-cfdi.xml';
        $cfdi = new Cfdi(file_get_contents($xmlPath));

        $cfdi->legal_name;
    }

    public function testCfdiEmisor()
    {
        $xmlPath = __DIR__ . '/resources/sample-cfdi.xml';
        $cfdi = new Cfdi(file_get_contents($xmlPath));

        $this->assertEquals('AIN020729J92', $cfdi->emisor->rfc);
        $this->assertEquals('Automatización en Internet SA de CV', $cfdi->emisor->nombre);
        $this->assertEquals('ALFONSO NAPOLES GANDARA', $cfdi->emisor->domicilioFiscal->calle);
        $this->assertEquals('50', $cfdi->emisor->domicilioFiscal->noExterior);
        $this->assertEquals('PEÑA BLANCA SANTA FE', $cfdi->emisor->domicilioFiscal->colonia);
        $this->assertEquals('DF', $cfdi->emisor->domicilioFiscal->localidad);
        $this->assertEquals('ALVARO OBREGON', $cfdi->emisor->domicilioFiscal->municipio);
        $this->assertEquals('DF', $cfdi->emisor->domicilioFiscal->estado);
        $this->assertEquals('México', $cfdi->emisor->domicilioFiscal->pais);
        $this->assertEquals('01210', $cfdi->emisor->domicilioFiscal->codigoPostal);

        $this->assertEquals('México', $cfdi->emisor->expedidoEn->pais);
        $this->assertEquals('Persona Moral del Regimen General', $cfdi->emisor->regimenFiscal->regimen);

        $this->assertEquals($cfdi->emisor->domicilio, $cfdi->emisor->domicilioFiscal);
        $this->assertEquals($cfdi->emisor->regimen, $cfdi->emisor->regimenFiscal);
    }

    public function testCfdiReceptor()
    {
        $xmlPath = __DIR__ . '/resources/sample-cfdi.xml';
        $cfdi = new Cfdi(file_get_contents($xmlPath));

        $this->assertEquals('BEGL7407295B7', $cfdi->receptor->rfc);
        $this->assertEquals('LUIS DANIEL BELTRAN GIRON', $cfdi->receptor->nombre);
        $this->assertEquals('Alfonso Napoles Gandara', $cfdi->receptor->domicilioFiscal->calle);
        $this->assertEquals('50', $cfdi->receptor->domicilioFiscal->noExterior);
        $this->assertEquals('4', $cfdi->receptor->domicilioFiscal->noInterior);
        $this->assertEquals('Peña Blanca', $cfdi->receptor->domicilioFiscal->colonia);
        $this->assertEquals('Santa Fe', $cfdi->receptor->domicilioFiscal->localidad);
        $this->assertEquals('Alvaro Obregón', $cfdi->receptor->domicilioFiscal->municipio);
        $this->assertEquals('Distrito Federal', $cfdi->receptor->domicilioFiscal->estado);
        $this->assertEquals('México', $cfdi->receptor->domicilioFiscal->pais);
        $this->assertEquals('01210', $cfdi->receptor->domicilioFiscal->codigoPostal);

        $this->assertEquals($cfdi->receptor->domicilio, $cfdi->receptor->domicilioFiscal);
    }

    public function testConceptos()
    {
        $xmlPath = __DIR__ . '/resources/sample-cfdi.xml';
        $cfdi = new Cfdi(file_get_contents($xmlPath));

        $this->assertCount(2, $cfdi->conceptos);

        $this->assertEquals('1', $cfdi->conceptos[0]->cantidad);
        $this->assertEquals('Servicio', $cfdi->conceptos[0]->unidad);
        $this->assertStringStartsWith('Actualización', $cfdi->conceptos[0]->descripcion);
        $this->assertEquals('5.00', $cfdi->conceptos[0]->valorUnitario);
        $this->assertEquals('5.00', $cfdi->conceptos[0]->importe);

        $this->assertEquals('2', $cfdi->conceptos[1]->cantidad);
        $this->assertEquals('Producto', $cfdi->conceptos[1]->unidad);
        $this->assertStringStartsWith('Servidor', $cfdi->conceptos[1]->descripcion);
        $this->assertEquals('100.00', $cfdi->conceptos[1]->valorUnitario);
        $this->assertEquals('200.00', $cfdi->conceptos[1]->importe);
    }

    public function testImpuesto()
    {
        $xmlPath = __DIR__ . '/resources/sample-cfdi.xml';
        $cfdi = new Cfdi(file_get_contents($xmlPath));

        $this->assertEquals('0.8', $cfdi->impuestos->totalImpuestosTrasladados);
        $this->assertEquals('0.00', $cfdi->impuestos->totalImpuestosRetenidos);

        $this->assertCount(2, $cfdi->impuestos->retenciones);

        $this->assertEquals('IVA', $cfdi->impuestos->retenciones[0]->impuesto);
        $this->assertEquals('0.00', $cfdi->impuestos->retenciones[0]->importe);
        $this->assertEquals('ISR', $cfdi->impuestos->retenciones[1]->impuesto);
        $this->assertEquals('0.00', $cfdi->impuestos->retenciones[1]->importe);

        $this->assertCount(1, $cfdi->impuestos->traslados);
        $this->assertEquals('IVA', $cfdi->impuestos->traslados[0]->impuesto);
        $this->assertEquals('0.16', $cfdi->impuestos->traslados[0]->tasa);
        $this->assertEquals('0.8', $cfdi->impuestos->traslados[0]->importe);
    }

    public function testComplementoImpuestosLocales()
    {
        $xmlPath = __DIR__ . '/resources/sample-cfdi.xml';
        $cfdi = new Cfdi(file_get_contents($xmlPath));

        $this->assertEquals('1.0', $cfdi->impuestosLocales->version);
        $this->assertEquals('0.00', $cfdi->impuestosLocales->retenciones);
        $this->assertEquals('0.8', $cfdi->impuestosLocales->traslados);

        $this->assertEquals(
            $cfdi->impuestosLocales->retenciones,
            $cfdi->impuestosLocales->totalDeRetenciones
        );

        $this->assertEquals(
            $cfdi->impuestosLocales->traslados,
            $cfdi->impuestosLocales->totalDeTraslados
        );

        $this->assertEquals('ICED', $cfdi->impuestosLocales->retencionesLocales->impuesto);
        $this->assertEquals('0.00', $cfdi->impuestosLocales->retencionesLocales->importe);
        $this->assertEquals('0.00', $cfdi->impuestosLocales->retencionesLocales->tasa);
    }

    public function testComplementoTimbreFiscalDigital()
    {
        $xmlPath = __DIR__ . '/resources/sample-cfdi.xml';
        $cfdi = new Cfdi(file_get_contents($xmlPath));

        $this->assertEquals($cfdi->timbre, $cfdi->timbreFiscalDigital);

        $this->assertEquals('1.0', $cfdi->timbre->version);
        $this->assertEquals('2F613767-0610-4686-9EA1-BE330AFD6C66', $cfdi->timbre->uuid);
        $this->assertEquals('2015-02-27T13:40:41', $cfdi->timbre->fecha);
        $this->assertStringStartsWith('Gg6sfBpGbm', $cfdi->timbre->selloCFD);
        $this->assertEquals('00001000000202639096', $cfdi->timbre->noCertificadoSAT);
        $this->assertStringStartsWith('04R+3SnVfe+R5', $cfdi->timbre->selloSAT);

        $this->assertEquals($cfdi->timbre->fecha, $cfdi->timbre->fechaTimbrado);
        $this->assertEquals($cfdi->timbre->selloCFD, $cfdi->timbre->cfd);
        $this->assertEquals($cfdi->timbre->uuid, $cfdi->timbre->UUID);
        $this->assertEquals($cfdi->timbre->selloSAT, $cfdi->timbre->sat);
    }
}
