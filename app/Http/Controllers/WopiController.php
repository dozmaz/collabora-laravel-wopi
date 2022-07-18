<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WopiController extends Controller
{
    private $client_url = '';
    private $no_ssl_validation = false;

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
    public function wopiCheckFileInfo($documentId, Request $request)
    {
        $access_token = $request->query('access_token');
        // test.txt is just a fake text file
        // the Size property is the length of the string
        // returned in wopiGetFile
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

        return response()->json($response, 200);
    }

    public function parseWopiRequest($documentId, Request $request){
        if (Request::isMethod('post'))        {
            return $this->wopiPutFile($documentId,$request);
        }elseif (Request::isMethod('get'))
        {
            return $this->wopiGetFile($documentId,$request);
        }
    }
    /* *
     *  Given a request access token and a document id, sends back the contents of the file.
     *  The GetFile wopi endpoint is triggered by a request with a GET verb at
     *  https://HOSTNAME/example_php/wopi/files/<document_id>/contents
     */
    public function wopiGetFile($documentId, Request $request)
    {
        $access_token = $request->query('access_token');
        // we just return the content of a fake text file
        // in a real case you should use the document id
        // for retrieving the file from the storage and
        // send back the file content as response
        $archivo = "ejemplo.docx";
        if (is_file(Storage::disk('public')->path($archivo))) {
            return response()->file(Storage::disk('public')->path($archivo));
        }
        return response('Error al leer el contenido del archivo');
    }

    /**
     *  Given a request access token and a document id, replaces the files with the POST request body.
     *  The PutFile wopi endpoint is triggered by a request with a POST verb at
     *  https://HOSTNAME/example_php/wopi/files/<document_id>/contents
     */
    public function wopiPutFile($documentId, Request $request)
    {
//        $access_token = $request->query('access_token');
        // we log to the apache error log file so that is possible
        // to check that saving has triggered this wopi endpoint
//        error_log('INFO: wopiPutFile invoked: document id: ' . $documentId);
//
//        $fileContent = file_get_contents("php://input");
//        file_put_contents("newfile.docx",$fileContent);
//        file_put_contents("newfile2.docx",getallheaders());
//
//        error_log('INFO: ' . $fileContent);
        $path = "ejemplo.docx";
        file_put_contents(Storage::disk('public')->path($path), $request->getContent());
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
//            if (!isset($_GET['submit'])) {
//                return 101;
//            }
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
        }
        $discovery_parsed = simplexml_load_string($discovery);
        libxml_disable_entity_loader($loadEntities);
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
