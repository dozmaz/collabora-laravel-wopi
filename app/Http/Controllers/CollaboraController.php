<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;

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
    public function index(){
        $wopi_src='';
        $errorCode=0;
        $documentoId=22991;
        $wopi = new WopiController();
        $errorCode = $wopi->validar($wopi_src);
        $errorMessage = $errorCode>0?$wopi->errorMsg[$errorCode]:'';

        $archivo = "ejemplo.docx";
        if (is_file(Storage::disk('public')->path($archivo))) {
            $phpWord2 = IOFactory::load(Storage::disk('public')->path($archivo));
            foreach ($phpWord2->getSections() as $section) {
                print_r($section->getElements());
            }
        }

        return view('collabora.index',compact('wopi_src','errorCode','errorMessage','documentoId'));
    }
}
