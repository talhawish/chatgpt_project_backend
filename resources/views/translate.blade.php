<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">

        <meta name="application-name" content="{{ config('app.name') }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        <style>[x-cloak] { display: none !important; }</style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        @livewireScripts
        @stack('scripts')
    </head>

    <body class="antialiased">
       

        <div class="p-4 border-2 border-slate-500 border-solid">
        <div class="grid  grid-cols-2 ">
            <div class="border-l-4 border-indigo-500 p-4">
                <h1>Germany Text</h1>
                <p>
                    {!! $germanText !!}
                </p>
            </div>
            
            <div class="border-l-4 border-indigo-500 p-4">
                <h1>English Text</h1>
                <p>
                    {!! $englishText !!}
                </p>
            </div>
        </div>
        </div>

        @livewire('notifications')
    </body>
</html>



