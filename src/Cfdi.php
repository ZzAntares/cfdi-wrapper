<?php

/**
 * This file is part of the CFDI Wrapper library.
 *
 * @copyright 2015 César Antáres <zzantares@gmail.com>
 * @license http://opensource.org/licenses/MIT The MIT License.
 */

namespace ZzAntares\CfdiWrapper;

require_once 'helpers.php';

use ZzAntares\CfdiWrapper\Exceptions\UndefinedAttributeException;
use ZzAntares\CfdiWrapper\Exceptions\MalformedCfdiException;

class Cfdi
{
    /**
     * Holds the instance used by all the methods to acces to the properties of
     * the CFDI XSD.
     *
     * @var SimpleXMLElement
     */
    private $cfdi;

    /**
     * Contains a map between CFDI XSD paths and an easier to read dotted
     * representation of the paths.
     *
     * @var array
     */
    private $paths = [
        'cfdi' => '//cfdi:Comprobante',
        'cfdi.issuing' => '//cfdi:Comprobante//cfdi:Emisor',
        'cfdi.issuing.address' => '//cfdi:Comprobante//cfdi:Emisor//cfdi:DomicilioFiscal',
        'cfdi.issuing.issued_at' => '//cfdi:Comprobante//cfdi:Emisor//cfdi:ExpedidoEn',
        'cfdi.issuing.regimen' => '//cfdi:Comprobante//cfdi:Emisor//cfdi:RegimenFiscal',
        'cfdi.receiver' => '//cfdi:Comprobante//cfdi:Receptor',
        'cfdi.receiver.address' => '//cfdi:Comprobante//cfdi:Receptor//cfdi:Domicilio',
        'cfdi.items' => '//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto',
        'cfdi.taxes' => '//cfdi:Comprobante//cfdi:Impuestos',
        'cfdi.taxes.holdbacks' => '//cfdi:Comprobante//cfdi:Impuestos//cfdi:Retenciones//cfdi:Retencion',
        'cfdi.taxes.transfers' => '//cfdi:Comprobante//cfdi:Impuestos//cfdi:Traslados//cfdi:Traslado',
        'cfdi.addon.taxes' => '//cfdi:Comprobante//cfdi:Complemento//implocal:ImpuestosLocales',
        'cfdi.addon.taxes.holdbacks' => '//cfdi:Comprobante//cfdi:Complemento//implocal:ImpuestosLocales//implocal:RetencionesLocales',
    ];

    /**
     * Contains the allowed attributes on the CFDI Wrapper instance.
     *
     * @var array
     */
    private $allowedAttributes = [
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
    ];

    /**
     * List the nested objects vailable on the CFDI Wrapper instance.
     *
     * @var array
     */
    private $nestedObjects = [
        'emisor',
        'receptor',
        'conceptos',
        'impuestos',
        'impuestosLocales',
        'timbre',
        'timbreFiscalDigital',
    ];


    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct($pathOrContent)
    {
        if (file_exists($pathOrContent)) {
            $this->loadFromFile($pathOrContent);
        } else {
            $this->load($pathOrContent);
        }
    }

    /**
     * Allows to change the loaded CFDI by specifying a new string whose content
     * is the new CDFI to set.
     *
     * @param string $xmlContent
     *
     * @return bool 'true' if load was successful, 'false' otherwise.
     */
    public function load($xmlContent)
    {
        $cfdi = simplexml_load_string($xmlContent);
        $this->cfdi = $cfdi;

        return $this->isValid(true);
    }

    /**
     * Allows to change the loaded CFDI by specifying the file path.
     *
     * @param string $path
     *
     * @return bool 'true' if load was successful, 'false' otherwise.
     */
    public function loadFromFile($path)
    {
        return $this->load(file_get_contents($path));
    }

    /**
     * Checks if the given CFDI is valid by searching the needed namespaces.
     *
     * @throws ZzAntares\CfdiWrapper\Exceptions\MalformedCfdiException
     *
     * @param SimpleXMLElement $cfdi
     *
     * @return void
     */
    public function isValid($throwException = false)
    {
        $namespaces = array_keys($this->cfdi->getNamespaces(true));

        if (in_array_all(['tfd', 'xsi', 'cfdi', 'implocal'], $namespaces)) {
            return true;
        }

        if ($throwException) {
            throw new MalformedCfdiException();
        }

        return false;
    }

    /**
     * Gets the specified attribute.
     *
     * @param string $attribute Property on the CFDI to retrieve.
     *
     * @return void
     */
    private function getAttribute($attribute)
    {
        $comprobante = $this->cfdi->xpath($this->paths['cfdi']);

        switch ($attribute) {
            case 'subtotal':
                $attribute = 'subTotal';
                break;

            case 'tipoCambio':
                $attribute = 'TipoCambio';
                break;

            case 'moneda':
                $attribute = 'Moneda';
                break;

            case 'lugarExpedicion':
                $attribute = 'LugarExpedicion';
                break;

            case 'numCtaPago':
                $attribute = 'NumCtaPago';
                break;
        }

        return $comprobante[0][$attribute]->__toString();
    }

    /**
     * Retrieves the propper nested object under the CFDI.
     *
     * @return object
     */
    private function getNestedObject($attribute)
    {
        switch ($attribute) {
            case 'emisor':
                return $this->getEmisor();
                break;

            case 'receptor':
                return $this->getReceptor();
                break;

            case 'conceptos':
                return $this->getConceptos();
                break;

            case 'impuestos':
                return $this->getImpuestos();
                break;

            case 'impuestosLocales':
                return $this->getImpuestosLocales();
                break;

            case 'timbre':
            case 'timbreFiscalDigital':
                return $this->getTimbreFiscalDigital();
                break;
        }
    }

    /**
     * Magic method to get attributes on the CFDI.
     *
     * @param string $attribute
     *
     * @return mixed A string or a nested object.
     */
    public function __get($attribute)
    {
        if (in_array($attribute, $this->allowedAttributes)) {
            return $this->getAttribute($attribute);
        }

        if (in_array($attribute, $this->nestedObjects)) {
            return $this->getNestedObject($attribute);
        }

        throw new UndefinedAttributeException();
    }

    /**
     * Retrieves the 'cfdi.addon.digital_stamp' path in the CFDI.
     *
     * @return object
     */
    private function getTimbreFiscalDigital()
    {
        // $stamp = $this->cfdi->xpath($this->paths['cfdi.addon.digital_stamp']);

        $ns = $this->cfdi->getNamespaces(true);
        $this->cfdi->registerXPathNamespace('tfd', $ns['tfd']);
        $stamp = $this->cfdi->xpath('//tfd:TimbreFiscalDigital')[0];

        return (object) [
            'version' => $stamp['version']->__toString(),
            'uuid' => $stamp['UUID']->__toString(),
            'UUID' => $stamp['UUID']->__toString(),
            'fecha' => $stamp['FechaTimbrado']->__toString(),
            'fechaTimbrado' => $stamp['FechaTimbrado']->__toString(),
            'selloCFD' => $stamp['selloCFD']->__toString(),
            'cfd' => $stamp['selloCFD']->__toString(),
            'noCertificadoSAT' => $stamp['noCertificadoSAT']->__toString(),
            'selloSAT' => $stamp['selloSAT']->__toString(),
            'sat' => $stamp['selloSAT']->__toString(),
        ];
    }

    /**
     * Retrieves the 'cfdi.addon.taxes' path in the CFDI.
     *
     * @return object
     */
    private function getImpuestosLocales()
    {
        $addon = $this->cfdi->xpath($this->paths['cfdi.addon.taxes'])[0];

        return (object) [
            'version' => $addon['version'],
            'totalDeRetenciones' => $addon['TotaldeRetenciones'],
            'totaldeRetenciones' => $addon['TotaldeRetenciones'],
            'retenciones' => $addon['TotaldeRetenciones'],
            'totalDeTraslados' => $addon['TotaldeTraslados'],
            'totaldeTraslados' => $addon['TotaldeTraslados'],
            'traslados' => $addon['TotaldeTraslados'],
            'retencionesLocales' => $this->getRetencionesLocales(),
        ];
    }

    /**
     * Retrieves the 'cfdi.addon.taxes.holdbacks' path in the CFDI.
     *
     * @return object
     */
    private function getRetencionesLocales()
    {
        $holdbacks = $this->cfdi->xpath($this->paths['cfdi.addon.taxes.holdbacks'])[0];

        return (object) [
            'impuesto' => $holdbacks['ImpLocRetenido']->__toString(),
            'importe' => $holdbacks['Importe']->__toString(),
            'tasa' => $holdbacks['TasadeRetencion']->__toString(),
        ];
    }

    /**
     * Retrieves all the 'cfdi.taxes' with it's nested objects like
     * 'retenciones' and 'traslados'.
     *
     * @return object
     */
    private function getImpuestos()
    {
        $impuestos = $this->cfdi->xpath($this->paths['cfdi.taxes'])[0];

        return (object) [
            'totalImpuestosTrasladados' => $impuestos['totalImpuestosTrasladados']->__toString(),
            'totalImpuestosRetenidos' => $impuestos['totalImpuestosRetenidos']->__toString(),
            'retenciones' => $this->getRetenciones(),
            'traslados' => $this->getTraslados(),
        ];
    }

    /**
     * Retrieves all 'cfdi.taxes.transfers' in the CFDI.
     *
     * @return array
     */
    private function getTraslados()
    {
        $transfers = $this->cfdi->xpath($this->paths['cfdi.taxes.transfers']);

        $traslados = [];
        foreach ($transfers as $transfer) {
            $traslado = [
                'impuesto' => $transfer['impuesto']->__toString(),
                'importe' => $transfer['importe']->__toString(),
                'tasa' => $transfer['tasa']->__toString(),
            ];

            $traslados[] = (object) $traslado;
        }

        return $traslados;
    }

    /**
     * Retrieves the path 'cfdi.taxes.holdbacks'.
     *
     * @return array
     */
    private function getRetenciones()
    {
        $holdbacks = $this->cfdi->xpath($this->paths['cfdi.taxes.holdbacks']);

        $retenciones = [];
        foreach ($holdbacks as $holdback) {
            $retencion = [
                'impuesto' => $holdback['impuesto']->__toString(),
                'importe' => $holdback['importe']->__toString(),
            ];

            $retenciones[] = (object) $retencion;
        }

        return $retenciones;
    }

    /**
     * Retrieves all 'cfdi.items' in the CFDI.
     *
     * @return array
     */
    private function getConceptos()
    {
        $items = $this->cfdi->xpath($this->paths['cfdi.items']);
        $conceptos = [];
        foreach ($items as $item) {
            $concepto = [
                'cantidad' => $item['cantidad']->__toString(),
                'unidad' => $item['unidad']->__toString(),
                'descripcion' => $item['descripcion']->__toString(),
                'valorUnitario' => $item['valorUnitario']->__toString(),
                'importe' => $item['importe']->__toString(),
            ];

            $conceptos[] = (object) $concepto;
        }

        return $conceptos;
    }

    /**
     * Retrieves the 'cfdi.receiver' path, including it's nested resources like
     * 'domicilio'.
     *
     * @return object
     */
    private function getReceptor()
    {
        $comprobante = $this->cfdi->xpath($this->paths['cfdi.receiver']);
        $domicilio = $this->getDomicilioFiscal('cfdi.receiver.address');

        $receptor = [
            'rfc' => $comprobante[0]['rfc']->__toString(),
            'nombre' => $comprobante[0]['nombre']->__toString(),
            'domicilioFiscal' => $domicilio,
            'domicilio' => $domicilio,
        ];

        return (object) $receptor;
    }

    /**
     * Retrieves the 'cfdi.issuing' path and all it's nested resources like
     * 'comprobante', 'domicilio', 'regimen', etc.
     *
     * @return object
     */
    private function getEmisor()
    {
        $comprobante = $this->cfdi->xpath($this->paths['cfdi.issuing']);
        $domicilio = $this->getDomicilioFiscal('cfdi.issuing.address');
        $regimenFiscal = $this->getRegimenFiscal();

        $emisor = [
            'rfc' => $comprobante[0]['rfc']->__toString(),
            'nombre' => $comprobante[0]['nombre']->__toString(),
            'domicilioFiscal' => $domicilio,
            'domicilio' => $domicilio,
            'expedidoEn' => $this->getExpedidoEn(),
            'regimenFiscal' => $regimenFiscal,
            'regimen' => $regimenFiscal,
        ];

        return (object) $emisor;
    }

    /**
     * Get the nested object domicilioFiscal for the specified path, this can be
     * either 'cfdi.issuing.address' or 'cfdi.receiver.address'.
     *
     * @param string $path 'cfdi.receiver.address' or 'cfdi.issuing.address'.
     *
     * @return object
     */
    private function getDomicilioFiscal($path)
    {
        $address = $this->cfdi->xpath($this->paths[$path])[0];

        $domicilio = [
            'calle'        => $address['calle']->__toString(),
            'colonia'      => $address['colonia']->__toString(),
            'localidad'    => $address['localidad']->__toString(),
            'municipio'    => $address['municipio']->__toString(),
            'noExterior'   => $address['noExterior']->__toString(),
            'estado'       => $address['estado']->__toString(),
            'pais'         => $address['pais']->__toString(),
            'codigoPostal' => $address['codigoPostal']->__toString(),
        ];

        if (isset($address[0]['noInterior'])) {
            $domicilio['noInterior'] = $address['noInterior']->__toString();
        }

        return (object) $domicilio;
    }

    /**
     * Retrieves the info located at 'cfdi.issuing.issued_at'.
     *
     * @return object
     */
    private function getExpedidoEn()
    {
        $expedidoEn = $this->cfdi->xpath($this->paths['cfdi.issuing.issued_at']);

        return (object) [
            'pais' => $expedidoEn[0]['pais']->__toString(),
        ];
    }

    /**
     * Retrieves the info located at 'cfdi.issuing.regimen'.
     *
     * @return object
     */
    private function getRegimenFiscal()
    {
        $regimenFiscal = $this->cfdi->xpath($this->paths['cfdi.issuing.regimen']);

        return (object) [
            'regimen' => $regimenFiscal[0]['Regimen']->__toString(),
            'Regimen' => $regimenFiscal[0]['Regimen']->__toString(),
        ];
    }
}
