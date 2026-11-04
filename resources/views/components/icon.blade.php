@props(['name'])

<svg {{ $attributes }}
     xmlns="http://www.w3.org/2000/svg"
     viewBox="0 0 24 24"
     fill="none"
     stroke="currentColor"
     stroke-width="1.8"
     stroke-linecap="round"
     stroke-linejoin="round"
     aria-hidden="true">

    @switch($name)
        @case('home')
            <path d="M3 10.5 12 3l9 7.5"/>
            <path d="M5 9.5V21h14V9.5"/>
            <path d="M9.5 21v-6h5v6"/>
            @break

        @case('car')
            <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/>
            <circle cx="7" cy="17" r="2"/>
            <path d="M9 17h6"/>
            <circle cx="17" cy="17" r="2"/>
            @break

        @case('history')
            <path d="M3 12a9 9 0 1 0 3-6.7L3 8"/>
            <path d="M3 3v5h5"/>
            <path d="M12 7v5l3 2"/>
            @break

        @case('check')
            <circle cx="12" cy="12" r="9"/>
            <path d="m8.5 12.5 2.5 2.5 4.5-5"/>
            @break

        @case('chevron')
            <path d="m6 9 6 6 6-6"/>
            @break

        @case('logout')
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <path d="m16 17 5-5-5-5"/>
            <path d="M21 12H9"/>
            @break

        @case('alert')
            <path d="M12 3 2 20h20L12 3Z"/>
            <path d="M12 10v4"/>
            <path d="M12 17h.01"/>
            @break

        @case('link')
            <path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/>
            <path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>
            @break
    @endswitch
</svg>