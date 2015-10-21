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
    public function setUp()
    {
        $xmlPath = __DIR__ . '/resources/sample-cfdi.xml';
        $this->cfdi = new Cfdi(file_get_contents($xmlPath));
    }

    public function tearDown()
    {
        $filepath = __DIR__ . '/resources/cfdi-to-file.xml';

        if (file_exists($filepath)) {
            unlink($filepath);
        }

        if (file_exists(__DIR__ . '/resources/qr-original.png')) {
            unlink(__DIR__ . '/resources/qr-original.png');
        }

        if (file_exists(__DIR__ . '/resources/qr-generated.png')) {
            unlink(__DIR__ . '/resources/qr-generated.png');
        }
    }

    public function testCfdiGetAttributes()
    {
        $this->assertEquals('3.2', $this->cfdi->version);
        $this->assertEquals('AIN', $this->cfdi->serie);
        $this->assertEquals('AIN2015027', $this->cfdi->folio);
        $this->assertEquals('2015-02-27T13:02:13', $this->cfdi->fecha);
        $this->assertEquals('320.50', $this->cfdi->subtotal);
        $this->assertEquals('371.78', $this->cfdi->total);
        $this->assertStringStartsWith('MIIEmDCCA4CgAwIBAg', $this->cfdi->certificado);
        $this->assertEquals('00001000000301470107', $this->cfdi->noCertificado);
        $this->assertEquals('No aplica', $this->cfdi->condicionesDePago);
        $this->assertEquals('0.0', $this->cfdi->descuento);
        $this->assertEquals('Sin descuento', $this->cfdi->motivoDescuento);
        $this->assertEquals('1', $this->cfdi->tipoCambio);
        $this->assertEquals('MXN', $this->cfdi->moneda);
        $this->assertEquals('No Identificado', $this->cfdi->metodoDePago);
        $this->assertStringStartsWith('Gg6sfBpGbmGPKLkBUfM3ULQQ', $this->cfdi->sello);
        $this->assertEquals('ingreso', $this->cfdi->tipoDeComprobante);
        $this->assertEquals('Pago en una sola exhibición', $this->cfdi->formaDePago);
        $this->assertEquals('México DF', $this->cfdi->lugarExpedicion);
        $this->assertEquals('No aplica', $this->cfdi->numCtaPago);
    }

    /**
     * @expectedException ZzAntares\CfdiWrapper\Exceptions\UndefinedAttributeException
     */
    public function testCfdiThrowsExceptionIfAttributeIsNotDefined()
    {
        $this->cfdi->legal_name;
    }

    public function testCfdiEmisor()
    {
        $this->assertEquals('AIN020729J92', $this->cfdi->emisor->rfc);
        $this->assertEquals('Automatización en Internet SA de CV', $this->cfdi->emisor->nombre);
        $this->assertEquals('ALFONSO NAPOLES GANDARA', $this->cfdi->emisor->domicilioFiscal->calle);
        $this->assertEquals('50', $this->cfdi->emisor->domicilioFiscal->noExterior);
        $this->assertEquals('PEÑA BLANCA SANTA FE', $this->cfdi->emisor->domicilioFiscal->colonia);
        $this->assertEquals('DF', $this->cfdi->emisor->domicilioFiscal->localidad);
        $this->assertEquals('ALVARO OBREGON', $this->cfdi->emisor->domicilioFiscal->municipio);
        $this->assertEquals('DF', $this->cfdi->emisor->domicilioFiscal->estado);
        $this->assertEquals('México', $this->cfdi->emisor->domicilioFiscal->pais);
        $this->assertEquals('01210', $this->cfdi->emisor->domicilioFiscal->codigoPostal);

        $this->assertEquals('México', $this->cfdi->emisor->expedidoEn->pais);
        $this->assertEquals('Persona Moral del Regimen General', $this->cfdi->emisor->regimenFiscal->regimen);

        $this->assertEquals($this->cfdi->emisor->domicilio, $this->cfdi->emisor->domicilioFiscal);
        $this->assertEquals($this->cfdi->emisor->regimen, $this->cfdi->emisor->regimenFiscal);
    }

    public function testCfdiReceptor()
    {
        $this->assertEquals('BEGL7407295B7', $this->cfdi->receptor->rfc);
        $this->assertEquals('LUIS DANIEL BELTRAN GIRON', $this->cfdi->receptor->nombre);
        $this->assertEquals('Alfonso Napoles Gandara', $this->cfdi->receptor->domicilioFiscal->calle);
        $this->assertEquals('50', $this->cfdi->receptor->domicilioFiscal->noExterior);
        $this->assertEquals('4', $this->cfdi->receptor->domicilioFiscal->noInterior);
        $this->assertEquals('Peña Blanca', $this->cfdi->receptor->domicilioFiscal->colonia);
        $this->assertEquals('Santa Fe', $this->cfdi->receptor->domicilioFiscal->localidad);
        $this->assertEquals('Alvaro Obregón', $this->cfdi->receptor->domicilioFiscal->municipio);
        $this->assertEquals('Distrito Federal', $this->cfdi->receptor->domicilioFiscal->estado);
        $this->assertEquals('México', $this->cfdi->receptor->domicilioFiscal->pais);
        $this->assertEquals('01210', $this->cfdi->receptor->domicilioFiscal->codigoPostal);

        $this->assertEquals($this->cfdi->receptor->domicilio, $this->cfdi->receptor->domicilioFiscal);
    }

    public function testConceptos()
    {
        $this->assertCount(2, $this->cfdi->conceptos);

        $this->assertEquals('1', $this->cfdi->conceptos[0]->cantidad);
        $this->assertEquals('Servicio', $this->cfdi->conceptos[0]->unidad);
        $this->assertStringStartsWith('Actualización', $this->cfdi->conceptos[0]->descripcion);
        $this->assertEquals('120.50', $this->cfdi->conceptos[0]->valorUnitario);
        $this->assertEquals('120.50', $this->cfdi->conceptos[0]->importe);

        $this->assertEquals('2', $this->cfdi->conceptos[1]->cantidad);
        $this->assertEquals('Producto', $this->cfdi->conceptos[1]->unidad);
        $this->assertStringStartsWith('Servidor', $this->cfdi->conceptos[1]->descripcion);
        $this->assertEquals('100.00', $this->cfdi->conceptos[1]->valorUnitario);
        $this->assertEquals('200.00', $this->cfdi->conceptos[1]->importe);
    }

    public function testImpuesto()
    {
        $this->assertEquals('51.28', $this->cfdi->impuestos->totalImpuestosTrasladados);
        $this->assertEquals('0.00', $this->cfdi->impuestos->totalImpuestosRetenidos);

        $this->assertCount(2, $this->cfdi->impuestos->retenciones);

        $this->assertEquals('IVA', $this->cfdi->impuestos->retenciones[0]->impuesto);
        $this->assertEquals('0.00', $this->cfdi->impuestos->retenciones[0]->importe);
        $this->assertEquals('ISR', $this->cfdi->impuestos->retenciones[1]->impuesto);
        $this->assertEquals('0.00', $this->cfdi->impuestos->retenciones[1]->importe);

        $this->assertCount(1, $this->cfdi->impuestos->traslados);
        $this->assertEquals('IVA', $this->cfdi->impuestos->traslados[0]->impuesto);
        $this->assertEquals('0.16', $this->cfdi->impuestos->traslados[0]->tasa);
        $this->assertEquals('51.28', $this->cfdi->impuestos->traslados[0]->importe);
    }

    public function testComplementoImpuestosLocales()
    {
        $this->assertEquals('1.0', $this->cfdi->impuestosLocales->version);
        $this->assertEquals('0.00', $this->cfdi->impuestosLocales->retenciones);
        $this->assertEquals('51.28', $this->cfdi->impuestosLocales->traslados);

        $this->assertEquals(
            $this->cfdi->impuestosLocales->retenciones,
            $this->cfdi->impuestosLocales->totalDeRetenciones
        );

        $this->assertEquals(
            $this->cfdi->impuestosLocales->traslados,
            $this->cfdi->impuestosLocales->totalDeTraslados
        );

        $this->assertEquals('ICED', $this->cfdi->impuestosLocales->retencionesLocales->impuesto);
        $this->assertEquals('0.00', $this->cfdi->impuestosLocales->retencionesLocales->importe);
        $this->assertEquals('0.00', $this->cfdi->impuestosLocales->retencionesLocales->tasa);
    }

    public function testComplementoTimbreFiscalDigital()
    {
        $this->assertEquals($this->cfdi->timbre, $this->cfdi->timbreFiscalDigital);

        $this->assertEquals('1.0', $this->cfdi->timbre->version);
        $this->assertEquals('2F613767-0610-4686-9EA1-BE330AFD6C66', $this->cfdi->timbre->uuid);
        $this->assertEquals('2015-02-27T13:40:41', $this->cfdi->timbre->fecha);
        $this->assertStringStartsWith('Gg6sfBpGbm', $this->cfdi->timbre->selloCFD);
        $this->assertEquals('00001000000202639096', $this->cfdi->timbre->noCertificadoSAT);
        $this->assertStringStartsWith('04R+3SnVfe+R5', $this->cfdi->timbre->selloSAT);

        $this->assertEquals($this->cfdi->timbre->fecha, $this->cfdi->timbre->fechaTimbrado);
        $this->assertEquals($this->cfdi->timbre->selloCFD, $this->cfdi->timbre->cfd);
        $this->assertEquals($this->cfdi->timbre->uuid, $this->cfdi->timbre->UUID);
        $this->assertEquals($this->cfdi->timbre->selloSAT, $this->cfdi->timbre->sat);
    }

    /**
     * @expectedException ZzAntares\CfdiWrapper\Exceptions\MalformedCfdiException
     */
    public function testIsValid()
    {
        $xmlPath = __DIR__ . '/resources/sample-cfdi-invalid.xml';
        $cfdi = new Cfdi($xmlPath);
    }

    public function testInstanceFromFile()
    {
        $xmlPath = __DIR__ . '/resources/sample-cfdi.xml';
        $cfdi = new Cfdi($xmlPath);
    }

    public function testLoadFromFile()
    {
        $xmlPath = __DIR__ . '/resources/sample-cfdi.xml';
        $cfdi = new Cfdi($xmlPath);
    }

    public function testLoad()
    {
        $folio = $this->cfdi->folio;
        $this->cfdi->load(file_get_contents(__DIR__ . '/resources/sample-cfdi-2.xml'));

        $this->assertNotEquals($folio, $this->cfdi->folio);
    }

    public function testToString()
    {
        $filepath = __DIR__ . '/resources/cfdi-to-file.xml';
        file_put_contents($filepath, $this->cfdi);

        $this->assertFileExists($filepath);

        $fileContent = file_get_contents($filepath);
        $this->assertEquals($fileContent, $this->cfdi->__toString());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testToFileThrowsExceptionIfFileExists()
    {
        $filepath = __DIR__ . '/resources/cfdi-to-file.xml';
        file_put_contents($filepath, 'file content');

        $this->cfdi->toFile($filepath);
    }

    public function testToFile()
    {
        $filepath = __DIR__ . '/resources/cfdi-to-file.xml';

        $this->cfdi->toFile($filepath);

        $this->assertFileExists($filepath);

        $fileContent = file_get_contents($filepath);
        $this->assertEquals($fileContent, $this->cfdi->__toString());
    }

    public function testToFileEvenIfFileExists()
    {
        $filepath = __DIR__ . '/resources/cfdi-to-file.xml';
        file_put_contents($filepath, 'file content');

        $written = $this->cfdi->toFile($filepath, true);

        $this->assertTrue((bool) $written);

        $fileContent = file_get_contents($filepath);
        $this->assertEquals($fileContent, $this->cfdi->__toString());
    }

    public function testGetQrString()
    {
        $qrString = '?re=AIN020729J92&rr=BEGL7407295B7&tt=371.78'
            . '&id=2F613767-0610-4686-9EA1-BE330AFD6C66';

        $this->assertEquals($qrString, $this->cfdi->getQrString());

        return $qrString;
    }

    /**
     * @depends testGetQrString
     */
    public function testQrCodeGetsGenerated($qrString)
    {
        $renderer = new \BaconQrCode\Renderer\Image\Png();
        $renderer->setWidth(256);
        $renderer->setHeight(256);

        $writer = new \BaconQrCode\Writer($renderer);
        $qrString = $writer->writeString($qrString);

        $this->assertEquals(base64_encode($qrString), $this->cfdi->qr());
    }

    /**
     * @depends testGetQrString
     */
    public function testQrCodeGetsSavedToFile($qrString)
    {
        $renderer = new \BaconQrCode\Renderer\Image\Png();
        $renderer->setWidth(256);
        $renderer->setHeight(256);

        $writer = new \BaconQrCode\Writer($renderer);
        $writer->writeFile($qrString, __DIR__ . '/resources/qr-original.png');

        $this->cfdi->qrCode(__DIR__ . '/resources/qr-generated.png');
        $this->assertFileEquals(
            __DIR__ . '/resources/qr-original.png',
            __DIR__ . '/resources/qr-generated.png'
        );
    }
}
