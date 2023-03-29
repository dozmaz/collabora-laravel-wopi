<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en">
<head>
    <script>
        parent.parent.copiarToken("{{ csrf_token() }}");
        parent.parent.limpiarArchivo();
    </script>
</head>
<body>
<div style="display: none">
    <form action="" enctype="multipart/form-data" method="post" target="_self" id="collabora-submit-form">
        <input name="access_token" value="test" type="hidden" />
        <input name="ui_defaults" value="UIMode=tabbed;TextRuler=true;TextStatusbar=true;TextSidebar=false" type="hidden"/>
        <input type="submit" value="" />
    </form>
</div>
<div>
    <p> Something went wrong :-( </p>
    <p>
        @if ($errorCode > 200)
        {{ $errorMessage }}
        @endif
    </p>
</div>

<script type="text/ecmascript">
    function loadDocument() {
        var wopiSrc = window.location.origin + '/laravel/wopi/files/{{ $documentoId }}/{{ $usuarioEdicionId }}';

        var wopiClientUrl = "{{ $wopi_src }}";
        if (!wopiClientUrl) {
            console.log('error: wopi client url not found');
            return;
        }

        var wopiUrl = wopiClientUrl + 'WOPISrc=' + wopiSrc+"&lang=es-es&&css_variables=--co-primary-element%3Dgreen;--co-body-bg%3D%23FDFDFD";

        var formElem = document.getElementById("collabora-submit-form");
        if (!formElem) {
            console.log('error: submit form not found');
            return;
        }
        formElem.action = wopiUrl;
        formElem.submit();
    }

    loadDocument();
</script>
</body>
</html>
