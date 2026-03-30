@php($mssqlConsoleDebugEntries = session()->pull('mssql_console_debug', [])) 

@if (app()->isLocal() && config('app.debug') && !empty($mssqlConsoleDebugEntries))
    <script>
        (() => {
            const entries = @json($mssqlConsoleDebugEntries);

            console.groupCollapsed(`[MSSQL] ${entries.length} query(s)`);

            entries.forEach((entry, index) => {
                console.log(`Query #${index + 1}: ${entry.sql}`);
            });

            console.groupEnd();
        })();
    </script>
@endif
