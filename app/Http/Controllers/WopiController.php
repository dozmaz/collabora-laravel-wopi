<?php


namespace App\Http\Controllers;

use App\Models\DocumentoEdicion;
use App\Models\Documentos;
use App\Models\Tipos;
use App\Models\UsuarioCorrespondencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\TemplateProcessor;

class WopiController extends Controller
{
    private $client_url = '';
    private $no_ssl_validation = true;

    public $errorMsg = [
        101 => 'GET Request not found',
        201 => 'Collabora Online server address is not valid',
        202 => 'Collabora Online server address scheme does not match the current page url scheme',
        203 => 'No able to retrieve the discovery.xml file from the Collabora Online server with the submitted address.',
        102 => 'The retrieved discovery.xml file is not a valid XML file',
        103 => 'The requested mime type is not handled',
        204 => 'Warning! You have to specify the scheme protocol too (http|https) for the server address.'
    ];

    public function __construct()
    {
        $this->client_url = env('WOPI_CLIENT_URL', '');
        $this->no_ssl_validation = env('WOPI_NO_SSL_VALIDATION', false);
    }

    /**
     * @return false|string
     */
    public function getDiscovery()
    {
        $discoveryUrl = $this->client_url . '/hosting/discovery';
        if ($this->no_ssl_validation) {
            $arrContextOptions = array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ),
            );
            $res = file_get_contents($discoveryUrl, false, stream_context_create($arrContextOptions));
        } else {
            $res = file_get_contents($discoveryUrl);
        }
        return $res;
    }

    /**
     * @param $discovery_parsed
     * @param $mimetype
     * @return null
     */
    public function getWopiSrcUrl($discovery_parsed, $mimetype)
    {
        if ($discovery_parsed === null || $discovery_parsed == false) {
            return null;
        }
        $result = $discovery_parsed->xpath(sprintf('/wopi-discovery/net-zone/app[@name=\'%s\']/action', $mimetype));
        if ($result && count($result) > 0) {
            return $result[0]['urlsrc'];
        }
        return null;
    }

    /**
     *  Returns info about the file with the given document id.
     *  The response has to be in JSON format and at a minimum it needs to include
     *  the file name and the file size.
     *  The CheckFileInfo wopi endpoint is triggered by a GET request at
     *  https://HOSTNAME/example_php/wopi/files/<document_id>
     */
    public function wopiCheckFileInfo($documentoId,$usuarioEdicionId, Request $request)
    {
        try{
            $access_token = $request->query('access_token');

            $documento = Documentos::find($documentoId);
            if ($documento) {
                $usuarioCorrespondencia = UsuarioCorrespondencia::find($documento->id_user);

                $filename =CollaboraController::generaNombreArchivoGestion($documento);
                if (!is_file($filename)) {
                    $filename =CollaboraController::generaNombreArchivoGestion($documento,2022);
                }


                $docEdicion = DocumentoEdicion::where('documento_id', '=', $documentoId)
                    ->where('estado', '=', DocumentoEdicion::$enProceso)->first();
                $puedeEditarDocumentoDerivado = false;
                if ($docEdicion) {
                    if($docEdicion->usuario_edita_id == $usuarioEdicionId && $docEdicion->puedeEditarPorFecha()){
                        $puedeEditarDocumentoDerivado = true;
                    }
                }

                $info = pathinfo($filename);
                if($documento->estado ==0 || $puedeEditarDocumentoDerivado) {//documento sin derivar
                    $response = [
                        'BaseFileName' => $info['basename'],
                        'UserFriendlyName' => $usuarioCorrespondencia->nombre,
                        'Size' => filesize($filename),
                        'UserId' => 1,
                        'UserCanWrite' => true,
                        'DisablePrint' => true,
                        'HidePrintOption' => true,
                        'HideExportOption' => true,
                        //'WatermarkText'=>$documento->estado==0?'Borrador':'',
    //                'DisableExport' => true,
                        //'DownloadAsPostMessage' => true,
                        'UserExtraInfo' => ['avatar' => '', 'mail' => $usuarioCorrespondencia->email]
                    ];
                }else{//documento derivado
                    $response = [
                        'BaseFileName' => $info['basename'],
                        'UserFriendlyName' => $usuarioCorrespondencia->nombre,
                        'Size' => filesize($filename),
                        'UserId' => 1,
                        'UserCanWrite' => false,
                        'DisableExport' => false,
                        //'DownloadAsPostMessage' => true,
                        //'DisablePrint' => true,
                        //'HidePrintOption' => true,
                        'HideExportOption' => true,
                        'UserExtraInfo' => ['avatar' => '', 'mail' => $usuarioCorrespondencia->email]
                    ];
                }
            }else {
                $response = [
                    'BaseFileName' => 'test.docx',
                    'UserFriendlyName' => 'test',
                    'Size' => 11,
                    'UserId' => 1,
                    'UserCanWrite' => true,
                    'DisablePrint' => true,
                    'HidePrintOption' => true,
                    'HideExportOption' => true,
                    'DisableExport' => true,
                    'DownloadAsPostMessage' => true,
                    'UserExtraInfo' => ['avatar' => '', 'mail' => 'user@endesyc.bo']
                ];
            }
        }catch(Exception $e){
            Log::info($e);
        }
        return response()->json($response, 200);
    }

    public function parseWopiRequest($documentId, Request $request)
    {
        try{
            if ($request->isMethod('POST') || $request->isMethod('post')) {
                return $this->wopiPutFile($documentId, $request);
            } elseif ($request->isMethod('GET') || $request->isMethod('get')) {
                return $this->wopiGetFile($documentId, $request);
            }
        }catch(Exception $e){
            Log::info($e);
        }
    }

    /* *
     *  Given a request access token and a document id, sends back the contents of the file.
     *  The GetFile wopi endpoint is triggered by a request with a GET verb at
     *  https://HOSTNAME/example_php/wopi/files/<document_id>/contents
     */
    public function wopiGetFile($documentoId, Request $request)
    {
        try{
            $access_token = $request->query('access_token');

            $documento = Documentos::find($documentoId);
            if ($documento) {
                $filename =CollaboraController::generaNombreArchivoGestion($documento);
                if (is_file($filename)) {Log::info("filename:".$filename);
                    return response()->file($filename,['"Content-Type: application/octet-stream"']);
                }else{
                    $filename =CollaboraController::generaNombreArchivoGestion($documento,2022);
                    if (is_file($filename)) {Log::info("filename:".$filename);
                        return response()->file($filename,['"Content-Type: application/octet-stream"']);
                    }
                }
            }
        }catch(Exception $e){
            Log::info($e);
        }
        return response('Error al leer el contenido del archivo');
    }

    /**
     *  Given a request access token and a document id, replaces the files with the POST request body.
     *  The PutFile wopi endpoint is triggered by a request with a POST verb at
     *  https://HOSTNAME/example_php/wopi/files/<document_id>/contents
     */
    public function wopiPutFile($documentoId, Request $request)
    {
        try{
            $access_token = $request->query('access_token');
            $documento = Documentos::find($documentoId);
            if ($documento) {
                $filename =CollaboraController::generaNombreArchivoGestion($documento);
                if (is_file($filename)) {
                    file_put_contents($filename, $request->getContent());
                }else{
                    $filename =CollaboraController::generaNombreArchivoGestion($documento,2022);
                    if (is_file($filename)) {
                        file_put_contents($filename, $request->getContent());
                    }
                }
                $documento->contenido = CollaboraController::convertFormat('txt',$filename);
                $documento->save();
            }
        }catch(Exception $e){
            Log::info($e);
        }
        return response(true, 200);
    }

    public function strStartsWith($s, $ss)
    {
        $res = strrpos($s, $ss);
        return !is_bool($res) && $res == 0;
    }

    public function validar(&$wopi_src)
    {
        $_HOST_SCHEME = request()->secure() ? 'https' : 'http';

        $wopiClientServer = $this->client_url;
        if (!$wopiClientServer) {
            return 201;
        }
        $wopiClientServer = trim($wopiClientServer);

        // validar si la solicitud es por http
        if (!$this->strStartsWith($wopiClientServer, 'http')) {
            return 204;
        }

        // validar si la solicitud es por https
        if (!$this->strStartsWith($wopiClientServer, $_HOST_SCHEME . '://')) {
            return 202;
        }

        $discovery = $this->getDiscovery($wopiClientServer);
        if (!$discovery) {
            return 203;
        }

        if (\PHP_VERSION_ID < 80000) {
            $loadEntities = libxml_disable_entity_loader(true);
            libxml_disable_entity_loader($loadEntities);
        }
        $discovery_parsed = simplexml_load_string($discovery);
        if (!$discovery_parsed) {
            return 102;
        }

        $wopi_src = $this->getWopiSrcUrl($discovery_parsed, 'text/plain');
        if (!$wopi_src) {
            return 103;
        }
        return 0;
    }
}
