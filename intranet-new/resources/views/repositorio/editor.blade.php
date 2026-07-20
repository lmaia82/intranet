<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>{{ $arquivo->nome_original }}</title>
    <style>
        html, body { margin: 0; padding: 0; height: 100%; font-family: sans-serif; }
        #topbar { background: #1f2937; color: white; padding: 8px 16px; display: flex; align-items: center; gap: 16px; }
        #topbar a { color: white; text-decoration: none; }
        #onlyoffice-editor { width: 100%; height: calc(100vh - 40px); }
    </style>
</head>
<body>
    <div id="topbar">
        <span>{{ $arquivo->nome_original }}</span>
    </div>
    <div id="onlyoffice-editor"></div>

    <script src="{{ config('services.onlyoffice.url') }}/web-apps/apps/api/documents/api.js"></script>
    <script>
        new DocsAPI.DocEditor("onlyoffice-editor", @json($config));
    </script>
</body>
</html>
