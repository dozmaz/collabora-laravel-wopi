<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Collabora</title>
    </head>
    <body>
{{--    <form action="https://correspondencia.endesyc.bo/laravel/wopi/files/1/contents?access_token=test&access_token_ttl=0&permission=edit" enctype="multipart/form-data" method="post" target="collabora-online-viewer" id="collabora-submit-form">--}}
    <form action="https://correspondencia.endesyc.bo/laravel/wopi/files/1/contents" enctype="multipart/form-data" method="post" target="collabora-online-viewer" id="collabora-submit-form">
        <input type="file" name="content">
        <input type="submit" value="grabar" />
    </form>
    <iframe id="collabora-online-viewer" name="collabora-online-viewer" src="{{ url('/collabora') }}" style="width:95%;height:90%;position:absolute;">
    </iframe>
    </body>
</html>
