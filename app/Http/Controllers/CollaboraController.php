<?php

namespace App\Http\Controllers;

use App\Models\Documentos;
use App\Models\Tipos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

class CollaboraController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('auth');
    }

    public function index($token_correspondencia, Request $request)
    {
        $wopi_src = '';
        $documentoId = 0;
        $usuarioId = 0;
        $usuarioEdicionId = 0;
        self::decodeToken($token_correspondencia, $documentoId,$usuarioId,$usuarioEdicionId);
        $wopi = new WopiController();
        $errorCode = $wopi->validar($wopi_src);
        $errorMessage = $errorCode > 0 ? $wopi->errorMsg[$errorCode] : '';
        $this->mostrarArchivoBD($request, $documentoId);
        return view('collabora.index', compact('wopi_src', 'errorCode', 'errorMessage', 'documentoId','usuarioEdicionId', 'token_correspondencia'));
    }

    /**
     * Punto de ingreso a la edición
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function welcome($token_correspondencia, Request $request)
    {
        $documentoId = 0;
        $usuarioId = 0;
        $usuarioEdicionId = 0;
        self::decodeToken($token_correspondencia, $documentoId,$usuarioId,$usuarioEdicionId);
        $documento = Documentos::find($documentoId);
        return view('welcome', compact('token_correspondencia'));
    }

    /**
     * Genera el archivo word con el contenido obtenido de la base de datos
     * agregar a la clase PhpOffice\PhpWord\PhpWord los métodos:
     *
     *    public function addExistingSection(Section $section)
     *    {
     *    $this->sections[] = $section;
     *    return $section;
     *    }
     *
     *    public function section_unshift(Section $section){
     *    array_unshift($this->sections, $section);
     *    }
     *
     * @param $documentoId
     * @param false $fusionarArchivo
     * @param null $nuevoArchivo
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     */
    public function mostrarArchivoBD(Request $request, $documentoId, $fusionarArchivo = false, $nuevoArchivo = null, $conContenido = true)
    {
        $documento = Documentos::find($documentoId);
        if ($documento) {
            $tipos = Tipos::find($documento->id_tipo);
            
            $filename = self::generaNombreArchivoGestion($documento, 2022);
            if (!is_file($filename)) {
                $filename = self::generaNombreArchivoGestion($documento);
                if (!is_file($filename)) {                
                    $doc = $this->encabezadoPlantilla($documento, $tipos);
                    try{
                    $doc->setValue('htmlblock', '');
                    $doc->setValue('/htmlblock', '');
                    $doc->setValue('html', '');
                    }catch(\Exception $e){}
                    if ($conContenido) {
                        $contenido = htmlspecialchars($documento->contenido);
                        if (is_null($contenido) || $contenido == "" || $contenido != strip_tags($contenido)) {
                            $contenido = str_replace("</p>", "\n", $contenido);
                            $contenido = str_replace("<br/>", "\n", $contenido);
                            $contenido = strip_tags($contenido);
                        }
                        // $contenido = str_replace("\n\n\n\n", "<br/>", $contenido);
                        // $contenido = str_replace("\n\n\n", "<br/>", $contenido);
                        // $contenido = str_replace("\n\n", "<br/>", $contenido);
                        // $contenido = str_replace("\n", "<br/>", $contenido);
                        $contenido = str_replace("<w:br/><w:br/><w:br/><w:br/>", "<br/>", $contenido);
                        $contenido = str_replace("<w:br/><w:br/><w:br/>", "<br/>", $contenido);
                        $contenido = str_replace("<w:br/><w:br/>", "<br/>", $contenido);
                        $contenido = str_replace("<w:br/>", "<br/>", $contenido);

                        // $doc->replaceBlock('htmlblock', htmlspecialchars($contenido));
                        // Log::info(htmlspecialchars($contenido));
                        // $doc->replaceBlock('htmlblock', '');

                        $doc->setHtmlBlockValue('htmlblock', '');
                        $doc->setValue('html', '');

                        // $section = (new \PhpOffice\PhpWord\PhpWord())->addSection();
                        // \PhpOffice\PhpWord\Shared\Html::addHtml($section, $contenido, false, false);
                        // $containers = $section->getElements();
                        // $doc->cloneBlock('htmlblock', count($containers), true, true);
                        // for ($i = 0; $i < count($containers); $i++) {
                        //     $doc->setComplexBlock('html#' . ($i + 1), $containers[$i]);
                        // }
                    }
                    $doc->saveAs($filename);
                }
            }
        }
    }

    /**
     * crea el encabezado de un documento
     *
     * @param $documento
     * @param $tipos
     * @return TemplateProcessor
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     */
    public function encabezadoPlantilla($documento, $tipos)
    {
        $plantilla = $tipos->template ?? 'templates/doc_sin_via.docx';
        if ($documento->nombre_via != '') {
            $plantilla = $tipos->template_via ?? 'templates/doc_con_via.docx';
        }
        if (strpos($plantilla, 'templates') === false) {
            $plantilla = 'templates/' . $plantilla;
        }

        $template = Storage::disk('public')->path($plantilla);
        $doc = new TemplateProcessor($template);

        $doc->setValue('titulo', utf8_decode($documento->titulo));
        $doc->setValue('institucion', utf8_decode($documento->institucion));

        $doc->setValue('tipo', utf8_decode(mb_strtoupper($tipos->tipo)));
        $doc->setValue('cite', utf8_decode($documento->codigo));
        $doc->setValue('destinatario', utf8_decode($documento->nombre_destinatario));
        $doc->setValue('destinatariocargo', utf8_decode(mb_strtoupper($documento->cargo_destinatario)));
        $doc->setValue('via', utf8_decode($documento->nombre_via));
        $doc->setValue('viacargo', utf8_decode(mb_strtoupper($documento->cargo_via)));
        $doc->setValue('remitente', utf8_decode($documento->nombre_remitente));
        $doc->setValue('remitentecargo', utf8_decode(mb_strtoupper($documento->cargo_remitente)));
        $doc->setValue('referencia', htmlspecialchars(mb_strtoupper($documento->referencia)));
        setlocale(LC_ALL, 'es_ES');
        setlocale(LC_TIME, "ES");
        $fecha = strftime('%d de %B de %Y', strtotime($documento->fecha_creacion));

        $doc->setValue('fecha', utf8_decode($fecha));
        $doc->setValue('hoja_ruta', $documento->nur);
        $doc->setValue('copia', ($documento->copias != '' && $documento->copias > 0 ? 'C.c.:' . $documento->copias : ''));
        $doc->setValue('adjunto', ($documento->adjuntos != '' && $documento->adjuntos > 0 ? 'Adj.:' . $documento->adjuntos : ''));
        $doc->setValue('mosca', $documento->mosca_remitente);
        return $doc;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function cargararchivo(Request $request)
    {
        $wopi_src = '';
        $documentoId = 0;
        $usuarioId = 0;
        $usuarioEdicionId = 0;
        self::decodeToken($request->input('token_correspondencia'), $documentoId,$usuarioId,$usuarioEdicionId);

        $uploadedFile = $request->file('file');
        $filename = time() . $uploadedFile->getClientOriginalName();

        $documento = Documentos::find($documentoId);
        
        $filename =self::generaNombreArchivoGestion($documento);
        if (is_file($filename)) {
            unlink($filename);
        }else{
            $filename =self::generaNombreArchivoGestion($documento,2022);
            if (is_file($filename)) {
                unlink($filename);
            }
            $filename =self::generaNombreArchivoGestion($documento);
        }

        $filename=str_replace(Storage::disk('public')->path(''),'',$filename);
        Storage::disk('public')->putFileAs(
            "",
            $uploadedFile,
            $filename
        );
        $partes_ruta = pathinfo(self::generaNombreArchivoGestion($documento));

        if (strtolower($partes_ruta['extension']) != 'docx') {
            $fileContent = self::convertFormat('docx', self::generaNombreArchivoGestion($documento));
            file_put_contents(self::generaNombreArchivoGestion($documento), $fileContent);
        }
        $wopi = new WopiController();
        $errorCode = $wopi->validar($wopi_src);
        $errorMessage = $errorCode > 0 ? $wopi->errorMsg[$errorCode] : '';
        return view('collabora.index', compact('wopi_src', 'errorCode', 'errorMessage', 'documentoId','usuarioEdicionId'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     */
    public function borrarcontenido(Request $request)
    {
        $documentoId = 0;
        $usuarioId = 0;
        $usuarioEdicionId = 0;
        $token_correspondencia=$request->input('token_correspondencia');
        self::decodeToken($token_correspondencia, $documentoId,$usuarioId,$usuarioEdicionId);

        $documento = Documentos::find($documentoId);
        if ($documento) {
            $filename =self::generaNombreArchivoGestion($documento);
            if (is_file($filename)) {
                unlink($filename);
            }else{
                $filename =self::generaNombreArchivoGestion($documento,2022);
                if (is_file($filename)) {
                    unlink($filename);
                }
            }
        }
        $this->mostrarArchivoBD($request, $documentoId, false, null, false);
        return $this->index($token_correspondencia, $request);
    }

    /**
     * @param Request $request
     */
    public function exportarpdf(Request $request)
    {
        $documentoId = 0;
        $usuarioId = 0;
        $usuarioEdicionId = 0;
        self::decodeToken($request->input('token_correspondencia'), $documentoId,$usuarioId,$usuarioEdicionId);
        $documento = Documentos::find($documentoId);
        //header("Content-type:application/pdf");      
        $filename =self::generaNombreArchivoGestion($documento);
        if (is_file($filename)) {
            echo self::convertFormat('pdf', $filename);
        }else{
            $filename =self::generaNombreArchivoGestion($documento,2022);
            if (is_file($filename)) {
                echo self::convertFormat('pdf', $filename);
            }
        }
    }

    /**
     * @param $format
     * @param $filename
     * @return bool|string
     */
    public static function convertFormat($format, $filename)
    {
        try{
            //$format.=$format=='pdf'?'&lang=es-ES':'';
            $ch = curl_init(env('WOPI_CLIENT_URL', '') . '/cool/convert-to/' . $format);
            curl_setopt_array($ch, array(
                CURLOPT_POST => 1,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_POSTFIELDS => array(
                    'file' => new \CURLFile($filename),
                    'WatermarkText' => 'TiledWatermark',
                    'Watermark' => 'TiledWatermark'
                )
            ));
            $output = curl_exec($ch);
            curl_close($ch);
            return $output;
        }catch(Exception $e){
            Log::info($e);
            return $e;
        }
    }

    /**
     * 
     * @param [type] $token_correspondencia
     * @param [type] $documentoId
     * @param [type] $usuarioId
     * @param [type] $usuarioEdicionId
     * @return void
     */
    public static function decodeToken($token_correspondencia,&$documentoId,&$usuarioId,&$usuarioEdicionId)
    {
        $base64Decode = base64_decode($token_correspondencia);
        $datos = explode('|', $base64Decode);
        if(count($datos)>1) {
            $documentoId = base64_decode($datos[0]);
            $usuarioId = base64_decode($datos[1]);
            $usuarioEdicionId = base64_decode($datos[2]);
        }else{
            $documentoId = 0;
            $usuarioId = 0;
            $usuarioEdicionId = 0;
        }
    }

    /**
     * 
     *
     * @param Documentos $documento
     * @param integer $gestion
     * @return string
     */
    public static function generaNombreArchivoGestion(Documentos $documento, int $gestion=0): string{
        $gestion = $gestion==0? date('Y',strtotime($documento->fecha_creacion)):$gestion;
        $filenameDocx = str_replace("/", "-", $documento->codigo) . '.docx';
        if(!is_dir(Storage::disk('public')->path('temp/'.$gestion.'/'))){
            mkdir(Storage::disk('public')->path('temp/'.$gestion.'/'),0775,true);
        }
        return Storage::disk('public')->path('temp/'.$gestion.'/' . $filenameDocx);
    }
}
