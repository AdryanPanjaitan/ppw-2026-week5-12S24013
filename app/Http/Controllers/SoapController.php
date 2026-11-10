<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SimpleXMLElement;
use Throwable;

class SoapController extends Controller
{
    public function wsdl(): Response
    {
        return response()->view('soap.sipus-wsdl')->header('Content-Type', 'text/xml; charset=utf-8');
    }

    
public function handle(Request $request): Response
{
    try {
        libxml_use_internal_errors(true);
        libxml_clear_errors();

         $xml = $request->getContent();

        $envelope = simplexml_load_string(
            $xml,
            SimpleXMLElement::class,
            LIBXML_NONET
        );

        if ($envelope === false) {
            $errors = libxml_get_errors();
            $messages = [];

            foreach ($errors as $error) {
                $messages[] = trim($error->message);
            }

            throw new \InvalidArgumentException(
                'XML SOAP tidak valid: ' . implode('; ', $messages)
            );
        }

        $nodes = $envelope->xpath(
            '//*[local-name()="nim_peminjam"]'
        );

        $nim = trim((string) ($nodes[0] ?? ''));

        if ($nim === '') {
            throw new \InvalidArgumentException(
                'nim_peminjam wajib diisi.'
            );
        }

        $activeLoans = Peminjaman::where(
            'nim_peminjam',
            $nim
        )->aktif()->count();

        return $this->xmlResponse(
            $this->envelope(
                'ClearanceResponse',
                '<active_loans>' . $activeLoans . '</active_loans>'
                . '<is_cleared>' . ($activeLoans === 0 ? 'true' : 'false')
                . '</is_cleared>'
            )
        );

    } catch (Throwable $exception) {
        return $this->xmlResponse(
            $this->envelope(
                'Fault',
                '<faultcode>SOAP-ENV:Client</faultcode>'
                . '<faultstring>'
                . htmlspecialchars(
                    $exception->getMessage(),
                    ENT_XML1,
                    'UTF-8'
                )
                . '</faultstring>'
            ),
            500
        );
    }
}


    private function envelope(string $bodyName, string $body): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">'
            .'<SOAP-ENV:Body><'.$bodyName.'>'.$body.'</'.$bodyName.'></SOAP-ENV:Body></SOAP-ENV:Envelope>';
    }

    private function xmlResponse(string $xml, int $status = 200): Response
    {
        return response($xml, $status)->header('Content-Type', 'text/xml; charset=utf-8');
    }
}
