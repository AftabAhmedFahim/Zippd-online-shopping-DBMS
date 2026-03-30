@php($mssqlConsoleDebugEntries = session()->pull('mssql_console_debug', [])) 

@if (app()->isLocal() && config('app.debug') && !empty($mssqlConsoleDebugEntries))
    <script>
        (() => {
            const entries = @json($mssqlConsoleDebugEntries);

            console.groupCollapsed(`[MSSQL] ${entries.length} query log(s)`);

            entries.forEach((entry, index) => {
                console.log(`Query #${index + 1}:`);
                console.log(entry.sql);
                console.log('Bindings:', entry.bindings);
                console.log('Output:', entry.output);
            });

            console.groupEnd();
        })();
    </script>
@endif
