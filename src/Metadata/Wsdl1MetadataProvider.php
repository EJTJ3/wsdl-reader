<?php
declare(strict_types=1);

namespace Soap\WsdlReader\Metadata;

use Soap\Engine\Metadata\Metadata;
use Soap\Engine\Metadata\MetadataProvider;
use Soap\WsdlReader\Metadata\Converter\Methods\MethodsConverterContext;
use Soap\WsdlReader\Metadata\Converter\SchemaToTypesConverter;
use Soap\WsdlReader\Metadata\Converter\Types\TypesConverterContext;
use Soap\WsdlReader\Metadata\Converter\Wsdl1ToMethodsConverter;
use Soap\WsdlReader\Model\Definitions\SoapVersion;
use Soap\WsdlReader\Model\Wsdl1;
use function Psl\Fun\lazy;

class Wsdl1MetadataProvider implements MetadataProvider
{
    /**
     * @var \Closure(): Metadata
     */
    private \Closure $metadata;

    public function __construct(
        public readonly Wsdl1 $wsdl,
        public readonly ?SoapVersion $soapVersion = null
    ){
        $this->metadata = lazy(static function () use ($wsdl, $soapVersion): Metadata {
            return new WsdlMetadata(
                $types = (new SchemaToTypesConverter())(
                    $wsdl->schema,
                    TypesConverterContext::default()
                ),
                $methods = (new Wsdl1ToMethodsConverter())(
                    $wsdl,
                    MethodsConverterContext::defaults($types, $soapVersion)
                )
            );
        });
    }

    public function getMetadata(): Metadata
    {
        return ($this->metadata)();
    }
}