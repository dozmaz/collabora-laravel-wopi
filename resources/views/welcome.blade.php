<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Collabora</title>
    {{-- <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}

    @vite(['resources/js/app.js'])

    <script>
        parent.copiarToken("{{ csrf_token() }}");
        parent.limpiarArchivo();


        function post(msg) {
            window.frames[0].postMessage(JSON.stringify(msg), '*');
        }

        function insertText(a, acargo, via, viacargo, de, decargo, referencia, fecha, tipo, cite, hojaruta) {
            post({
                'MessageId': 'CallPythonScript',
                'SendTime': Date.now(),
                'ScriptFile': 'InsertTableCorrespondencia.py',
                'Function': 'InsertText',
                'Values': {
                    'a': {'type': 'string', 'value': a},
                    'acargo': {'type': 'string', 'value': acargo},
                    'via': {'type': 'string', 'value': via},
                    'viacargo': {'type': 'string', 'value': viacargo},
                    'de': {'type': 'string', 'value': de},
                    'decargo': {'type': 'string', 'value': decargo},
                    'referencia': {'type': 'string', 'value': referencia},
                    'fecha': {'type': 'string', 'value': fecha},
                    'tipo': {'type': 'string', 'value': tipo},
                    'cite': {'type': 'string', 'value': cite},
                    'hojaruta': {'type': 'string', 'value': hojaruta},
                }
            });
        }

        function createTable() {
            post({
                'MessageId': 'CallPythonScript',
                'SendTime': Date.now(),
                'ScriptFile': 'TableSample.py',
                'Function': 'createTable',
                'Values': null
            });
        }

        function exportar(formato) {
            console.log(formato);
            post({
                'MessageId': 'Action_Print',
                'SendTime': Date.now(),
                'Values': null
            });
            post({
                'MessageId': 'Action_Export',
                'SendTime': Date.now(),
                'Values': {
                    'Format': formato,
                }
            });
        }

        function capitalize() {
            post({
                'MessageId': 'CallPythonScript',
                'SendTime': Date.now(),
                'ScriptFile': 'Capitalise.py',
                'Function': 'capitalisePython',
                'Values': null
            });
        }

        function save() {
            post({
                'MessageId': 'Action_Save',
                'Values': {'Notify': true, 'ExtendedData': 'CustomFlag=Custom Value;AnotherFlag=AnotherValue'}
            });
        }

        function closeDocument() {
            post({
                'MessageId': 'Action_Close',
                'Values': null
            });
        }

        function hide_commands(id) {
            post({
                'MessageId': 'Hide_Menu_Item',
                'Values': {'id': id,}
            });
            post({
                'MessageId': 'Hide_Button',
                'Values': {'id': id,}
            });
        }

        function show_commands(id) {
            post({
                'MessageId': 'Show_Menu_Item',
                'Values': {'id': id,}
            });
            post({
                'MessageId': 'Show_Button',
                'Values': {'id': id,}
            });
        }

        function disable_default_uiaction(action, disable) {
            post({
                'MessageId': 'Disable_Default_UIAction',
                'Values': {'action': action, 'disable': disable}
            });
        }

        function ShowMenubar(visible) {
            var messageId = visible ? 'Show_Menubar' : 'Hide_Menubar';
            post({'MessageId': 'Host_PostmessageReady'});
            post({'MessageId': messageId});
        }

        function ShowInsertButton() {
            post({
                'MessageId': 'Insert_Button',
                'Values': {
                    'id': 'Save',
                    'imgurl': 'images/lc_save.svg',
                    'hint': '',
                    'mobile': false,
                    'label': 'Show additional btns via Insert_Button',
                    'insertBefore': 'Save',
                    'unoCommand': '.uno:Save'
                }
            });
        }

        function ShowNotebookbar(notebookbar) {
            var value = notebookbar ? 'notebookbar' : 'classic';
            post({'MessageId': 'Host_PostmessageReady'});
            post({
                'MessageId': 'Action_ChangeUIMode',
                'Values': {'Mode': value}
            });
        }

        function reset_access_token(accesstoken) {
            post({
                'MessageId': 'Reset_Access_Token',
                'Values': {'token': accesstoken,}
            });
        }

        // This function is invoked when the iframe posts a message back.

        function receiveMessage(event) {
            console.log('==== framed.doc.html receiveMessage: ' + event.data);
            var msg = JSON.parse(event.data);
            if (!msg) {
                return;
            }
            if (msg.MessageId == 'App_LoadingStatus') {
                if (msg.Values) {
                    if (msg.Values.Status == 'Document_Loaded') {
                        post({'MessageId': 'Host_PostmessageReady'});
                    }
                }
            } else if (msg.MessageId == 'Doc_ModifiedStatus') {
                if (msg.Values) {
                    if (msg.Values.Modified == true) {
                        if (document.getElementById("ModifiedStatus"))
                            document.getElementById("ModifiedStatus").innerHTML = "Modified";
                    } else {
                        if (document.getElementById("ModifiedStatus"))
                            document.getElementById("ModifiedStatus").innerHTML = "Saved";
                    }
                }
            } else if (msg.MessageId == 'Action_Save_Resp') {
                if (msg.Values) {
                    if (msg.Values.success == true) {
                        if (document.getElementById("ModifiedStatus"))
                            document.getElementById("ModifiedStatus").innerHTML = "Saved";
                    } else {
                        if (document.getElementById("ModifiedStatus"))
                            document.getElementById("ModifiedStatus").innerHTML = "Error during save";
                    }
                }
            } else if (msg.MessageId == 'Download_As') {
                if (msg.Values) {
                    if (msg.Values.URL) {
                        downloadURI(msg.Values.URL, "documento.pdf");
                    }
                }
            }
        }

        function downloadURI(uri, name) {
            var link = document.createElement("a");
            link.download = name;
            link.href = uri;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            delete link;
        }
        function convertir(format,token_correspondencia='') {
            let formulario = document.getElementById("reportePdf");
            document.getElementById('format').value=format;
            if(token_correspondencia!='') {
                document.getElementById('token_correspondencia').value = token_correspondencia;
            }
            formulario.submit();
        }

        // 'main' code of this <script> block, run when page is being
        // rendered. Install the message listener.
        window.addEventListener("message", receiveMessage, false);
    </script>
</head>
<body>
<div class="container-fluid">
    <form action="/laravel/collabora/exportarpdf" method="post" id="reportePdf"
          style="display: none;" target="_blank">
        @csrf
        <input type="hidden" name="token_correspondencia" id="token_correspondencia"
               value="{{ $token_correspondencia??'' }}">
        <input type="hidden" name="format" id="format">
        <button type="submit">Vista previa</button>
    </form>
    @if ($token_correspondencia!="0")
    <div class="row">
        <iframe id="collabora-online-viewer" name="collabora-online-viewer"
                src="{{ url('/collabora') }}/{{$token_correspondencia}}"
                style="width:100%;height:99%;position:absolute;" class="p-0">
        </iframe>
    </div>
    @endif
    {{--    <div id="usage" class="alert" style="display:none">--}}
    {{--        <h2>Usage</h2>--}}
    {{--        <p>--}}
    {{--            Load this page via https or http, depending on whether SSL is enabled or not, from the Online server directly.</br>--}}
    {{--            The document to load must be given as a query parameter called file_path. The document can be any accessible URL, including on disk, via file://.--}}
    {{--        </p>--}}

    {{--        <h4>Example</h4>--}}
    {{--        <p>--}}
    {{--            http://localhost:9980/browser/dist/framed.doc.html?file_path=file:///path/to/document/hello-world.odt--}}
    {{--        </p>--}}
    {{--    </div>--}}

    {{--    <h3>PostMessage test harness</h3>--}}

    {{--    <h4>Run python scripts</h4>--}}
    {{--    <form id="insert-text-form">--}}
    {{--        <label for="fname"><b>InsertText.py</b>: inserts text into the document</label><br>--}}
    {{--        <textarea name="source" value="" rows="5" cols="50">Type your text and press Insert text</textarea><br>--}}
    {{--        <button onclick="insertText(document.forms['insert-text-form'].elements['source'].value); return false;">Insert text</button>--}}
    {{--        <button onclick="createTable(); return false;">Create Table</button>--}}
    {{--    </form>--}}
    {{--    <br>--}}
    {{--    <form id="insert-text-form">--}}
    {{--        <label for="fname"><b>Capitalize.py</b>: capitalize selected text in the document</label><br>--}}
    {{--        <button onclick="capitalize(); return false;">Capitalize selected text</button></br></br>--}}
    {{--    </form>--}}

    {{--    <h4>Various other messages to post</h4>--}}
    {{--    <form>--}}
    {{--        <button onclick="save(); return false;">Save</button>--}}
    {{--        <button onclick="closeDocument(); return false;">Close</button></br></br>--}}
    {{--        <button onclick="hide_commands('save'); return false;">Hide Save Commands</button>--}}
    {{--        <button onclick="show_commands('save'); return false;">Show Save Commands</button></br>--}}
    {{--        <button onclick="hide_commands('print'); return false;">Hide Print Commands</button>--}}
    {{--        <button onclick="show_commands('print'); return false;">Show Print Commands</button></br></br>--}}
    {{--        <button onclick="disable_default_uiaction('UI_Save', true); return false;">Disable default save action</button></br>--}}
    {{--        <button onclick="disable_default_uiaction('UI_Save', false); return false;">Enable default save action</button></br></br>--}}
    {{--        <label for="new-access-token"><b>New Access-Token</b>: </label><br>--}}
    {{--        <textarea name="new-access-token" id="new-access-token" value="" rows="1" cols="30">123456789AA</textarea><br>--}}
    {{--        <button onclick="reset_access_token(document.getElementById('new-access-token').value); return false;">Reset Access-Token</button>--}}
    {{--    </form>--}}

    {{--    <h3>Modified Status: <span id="ModifiedStatus">Saved</span></h3>--}}

    {{--    <h3>UI modification</h3>--}}
    {{--    <form id="menubar-toggle">--}}
    {{--        <button onclick="ShowMenubar(false); return false;">Hide Menubar</button>--}}
    {{--        <button onclick="ShowMenubar(true); return false;">Show Menubar</button>--}}
    {{--        <button onclick="ShowInsertButton(); return false;" title="via Insert_Button">Insert custom button</button>--}}

    {{--            <button onclick="ShowNotebookbar(false); return false;">Compact Toolbar</button>--}}
    {{--            <button onclick="ShowNotebookbar(true); return false;">Tabbed Toolbar</button>--}}
    {{--    </form>--}}
</div>
</body>
</html>
