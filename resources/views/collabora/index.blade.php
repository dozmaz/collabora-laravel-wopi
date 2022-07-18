<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en">
<body>
<div style="display: none">
    <form action="" enctype="multipart/form-data" method="post" target="_self" id="collabora-submit-form">
        <input name="access_token" value="test" type="hidden" />
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
        var wopiSrc = window.location.origin + '/laravel/wopi/files/{{ $documentoId }}';

        var wopiClientUrl = "{{ $wopi_src }}";
        if (!wopiClientUrl) {
            console.log('error: wopi client url not found');
            return;
        }

        var wopiUrl = wopiClientUrl + 'WOPISrc=' + wopiSrc;

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
